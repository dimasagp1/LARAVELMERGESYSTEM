<?php

namespace App\Core\Services;

use App\Core\Models\SystemSetting;
use Illuminate\Support\Facades\Cache;

class SystemSettingService
{
    protected const CACHE_KEY = 'erp_system_settings_all';

    protected const CACHE_TTL = 3600;

    /**
     * Get a setting by key, with default fallback.
     */
    public function get(string $key, mixed $default = null): mixed
    {
        $settings = $this->allCached();

        if (array_key_exists($key, $settings)) {
            return $settings[$key];
        }

        return $default;
    }

    /**
     * Set/update a setting value.
     */
    public function set(string $key, mixed $value, ?string $group = null, string $type = 'string', bool $isPublic = false, ?string $description = null): SystemSetting
    {
        $stringVal = is_array($value) ? json_encode($value) : (string) $value;

        $setting = SystemSetting::updateOrCreate(
            ['key' => $key],
            [
                'group' => $group ?? 'general',
                'value' => $stringVal,
                'type' => $type,
                'is_public' => $isPublic,
                'description' => $description,
            ]
        );

        $this->clearCache();

        return $setting;
    }

    /**
     * Get all settings grouped by category.
     *
     * @return array<string, array<string, mixed>>
     */
    public function getGrouped(): array
    {
        $this->ensureDefaultsSeeded();

        $all = SystemSetting::all();
        $grouped = [];

        foreach ($all as $item) {
            $grouped[$item->group][$item->key] = $item->typed_value;
        }

        return $grouped;
    }

    /**
     * Get all public settings for sharing via Inertia or API.
     *
     * @return array<string, mixed>
     */
    public function getPublicSettings(): array
    {
        $this->ensureDefaultsSeeded();

        $public = SystemSetting::where('is_public', true)->get();
        $result = [];

        foreach ($public as $item) {
            $result[$item->key] = $item->typed_value;
        }

        return $result;
    }

    /**
     * Update bulk settings.
     *
     * @param  array<string, mixed>  $data
     */
    public function updateBulk(array $data, ?string $group = null): void
    {
        foreach ($data as $key => $value) {
            $existing = SystemSetting::where('key', $key)->first();
            $type = $existing?->type ?? (is_bool($value) ? 'boolean' : (is_int($value) ? 'integer' : (is_array($value) ? 'json' : 'string')));
            $isPublic = $existing?->is_public ?? in_array($key, ['app_name', 'app_subtitle', 'company_name', 'app_logo_type', 'app_logo_icon', 'theme_color', 'theme_mode', 'theme_radius', 'theme_density']);

            $this->set($key, $value, $group ?? $existing?->group ?? 'general', $type, $isPublic);
        }

        $this->clearCache();
    }

    /**
     * Clear the cache.
     */
    public function clearCache(): void
    {
        Cache::forget(self::CACHE_KEY);
        Cache::forget('erp_system_settings_public');
    }

    /**
     * All settings cached as flat key => value.
     *
     * @return array<string, mixed>
     */
    protected function allCached(): array
    {
        return Cache::remember(self::CACHE_KEY, self::CACHE_TTL, function () {
            $this->ensureDefaultsSeeded();

            $all = SystemSetting::all();
            $result = [];

            foreach ($all as $item) {
                $result[$item->key] = $item->typed_value;
            }

            return $result;
        });
    }

    /**
     * Generate document number based on configured numbering scheme.
     */
    public function generateDocumentNumber(string $docType, ?int $customNumber = null): string
    {
        $schemes = $this->get('numbering_schemes', $this->getDefaultNumberingSchemes());
        $scheme = $schemes[$docType] ?? [
            'prefix' => strtoupper($docType),
            'pattern' => '{PREFIX}/{YEAR}{MONTH}/{NUMBER:4}',
            'reset_period' => 'monthly',
        ];

        $prefix = $scheme['prefix'] ?? strtoupper($docType);
        $pattern = $scheme['pattern'] ?? '{PREFIX}/{YEAR}{MONTH}/{NUMBER:4}';

        $now = now();
        $year = $now->format('Y');
        $shortYear = $now->format('y');
        $month = $now->format('m');
        $romanMonths = [
            1 => 'I', 2 => 'II', 3 => 'III', 4 => 'IV', 5 => 'V', 6 => 'VI',
            7 => 'VII', 8 => 'VIII', 9 => 'IX', 10 => 'X', 11 => 'XI', 12 => 'XII',
        ];
        $romanMonth = $romanMonths[(int) $now->format('n')];

        // Next number
        $number = $customNumber ?? $this->getNextNumberForDocType($docType, $scheme['reset_period'] ?? 'monthly');

        // Replace tokens
        $formatted = str_replace(
            ['{PREFIX}', '{YEAR}', '{YEAR2}', '{MONTH}', '{ROMAN_MONTH}'],
            [$prefix, $year, $shortYear, $month, $romanMonth],
            $pattern
        );

        // Replace {NUMBER:digits} e.g. {NUMBER:4} -> 0001
        $formatted = preg_replace_callback('/\{NUMBER(?::(\d+))?\}/', function ($matches) use ($number) {
            $digits = isset($matches[1]) ? (int) $matches[1] : 4;

            return str_pad((string) $number, $digits, '0', STR_PAD_LEFT);
        }, $formatted);

        return $formatted;
    }

    /**
     * Get next incremental sequence number.
     */
    protected function getNextNumberForDocType(string $docType, string $resetPeriod): int
    {
        $key = 'doc_counter_'.strtolower($docType);
        if ($resetPeriod === 'monthly') {
            $key .= '_'.now()->format('Ym');
        } elseif ($resetPeriod === 'yearly') {
            $key .= '_'.now()->format('Y');
        }

        $current = (int) Cache::get($key, 0);
        $next = $current + 1;
        Cache::put($key, $next, 86400 * 35); // 35 days cache

        return $next;
    }

    /**
     * Default document numbering schemes.
     *
     * @return array<string, array<string, mixed>>
     */
    public function getDefaultNumberingSchemes(): array
    {
        return [
            'PR' => [
                'name' => 'Purchase Request (Pengadaan)',
                'prefix' => 'PR',
                'pattern' => '{PREFIX}/{YEAR}{MONTH}/{NUMBER:4}',
                'reset_period' => 'monthly',
                'description' => 'Format nomor pengajuan pengadaan barang/jasa.',
            ],
            'PO' => [
                'name' => 'Purchase Order (Pemesanan)',
                'prefix' => 'PO',
                'pattern' => '{PREFIX}/{YEAR}{MONTH}/{NUMBER:4}',
                'reset_period' => 'monthly',
                'description' => 'Format nomor pesanan resmi ke vendor.',
            ],
            'GRN' => [
                'name' => 'Goods Receipt Note (Penerimaan)',
                'prefix' => 'GRN',
                'pattern' => '{PREFIX}/{YEAR}/{ROMAN_MONTH}/{NUMBER:4}',
                'reset_period' => 'yearly',
                'description' => 'Format nomor bukti penerimaan barang masuk ke gudang karantina.',
            ],
            'AJE' => [
                'name' => 'Adjusting Journal Entry (Jurnal Penyesuaian)',
                'prefix' => 'AJE',
                'pattern' => '{PREFIX}/{YEAR}/{NUMBER:5}',
                'reset_period' => 'yearly',
                'description' => 'Format nomor jurnal penyesuaian keuangan Maker-Checker.',
            ],
            'WPS' => [
                'name' => 'Weekly Production Schedule (Jadwal Produksi)',
                'prefix' => 'WPS',
                'pattern' => '{PREFIX}/{YEAR}{MONTH}/{NUMBER:4}',
                'reset_period' => 'monthly',
                'description' => 'Format jadwal rencana produksi mingguan PPIC.',
            ],
            'LHP' => [
                'name' => 'Laporan Harian Produksi (LHP)',
                'prefix' => 'LHP',
                'pattern' => '{PREFIX}/{YEAR}{MONTH}/{NUMBER:4}',
                'reset_period' => 'monthly',
                'description' => 'Format nomor pencatatan batch shop floor execution.',
            ],
            'DEV' => [
                'name' => 'Deviation Report (Laporan Deviasi Mutu)',
                'prefix' => 'DR',
                'pattern' => '{PREFIX}/{NUMBER:3}/{YEAR}',
                'reset_period' => 'yearly',
                'description' => 'Format nomor penyimpangan mutu CPOB (Penyelidikan Ketidaksesuaian).',
            ],
            'CRA' => [
                'name' => 'Change Request Mayor (CRA)',
                'prefix' => 'CRA',
                'pattern' => '{PREFIX}/{YEAR}/{NUMBER:4}',
                'reset_period' => 'yearly',
                'description' => 'Format usulan perubahan terkendali mayor QMS.',
            ],
            'CRB' => [
                'name' => 'Change Request Minor (CRB)',
                'prefix' => 'CRB',
                'pattern' => '{PREFIX}/{YEAR}/{NUMBER:4}',
                'reset_period' => 'yearly',
                'description' => 'Format usulan perubahan terkendali minor QMS.',
            ],
            'WO' => [
                'name' => 'Work Order Pemeliharaan (FPU)',
                'prefix' => 'WO',
                'pattern' => '{PREFIX}/{YEAR}{MONTH}/{NUMBER:4}',
                'reset_period' => 'monthly',
                'description' => 'Format nomor surat perintah kerja teknisi pemeliharaan fasilitas.',
            ],
            'LEAVE' => [
                'name' => 'Pengajuan Cuti Karyawan',
                'prefix' => 'CUTI',
                'pattern' => '{PREFIX}/{YEAR}/{NUMBER:4}',
                'reset_period' => 'yearly',
                'description' => 'Format nomor formulir cuti tahunan/khusus.',
            ],
            'OVERTIME' => [
                'name' => 'Surat Perintah Lembur (SPL)',
                'prefix' => 'SPL',
                'pattern' => '{PREFIX}/{YEAR}{MONTH}/{NUMBER:4}',
                'reset_period' => 'monthly',
                'description' => 'Format nomor surat penugasan jam kerja lembur.',
            ],
        ];
    }

    /**
     * Ensure default settings exist in DB.
     */
    public function ensureDefaultsSeeded(): void
    {
        if (SystemSetting::count() > 0) {
            return;
        }

        $defaults = [
            // General & Branding
            [
                'group' => 'general',
                'key' => 'app_name',
                'value' => 'HerbaTech ERP',
                'type' => 'string',
                'description' => 'Nama judul utama aplikasi ERP',
                'is_public' => true,
            ],
            [
                'group' => 'general',
                'key' => 'app_subtitle',
                'value' => 'Integrated Herbal Pharmaceutical ERP System',
                'type' => 'string',
                'description' => 'Subjudul / deskripsi singkat sistem ERP',
                'is_public' => true,
            ],
            [
                'group' => 'general',
                'key' => 'company_name',
                'value' => 'PT Herba Medika Industri',
                'type' => 'string',
                'description' => 'Nama resmi perusahaan pemegang lisensi ERP',
                'is_public' => true,
            ],
            [
                'group' => 'general',
                'key' => 'company_tagline',
                'value' => 'Excellence in Herbal Science & Phytopharmaceuticals',
                'type' => 'string',
                'description' => 'Slogan atau motto perusahaan',
                'is_public' => true,
            ],
            [
                'group' => 'general',
                'key' => 'company_address',
                'value' => 'Kawasan Industri Sukoharjo Blok B-12, Solo Baru, Jawa Tengah 57552',
                'type' => 'text',
                'description' => 'Alamat kantor operasional dan pabrik utama',
                'is_public' => true,
            ],
            [
                'group' => 'general',
                'key' => 'company_phone',
                'value' => '+62 271 7890123',
                'type' => 'string',
                'description' => 'Nomor telepon resmi perusahaan',
                'is_public' => true,
            ],
            [
                'group' => 'general',
                'key' => 'company_email',
                'value' => 'info@herbatech.co.id',
                'type' => 'string',
                'description' => 'Email kontak utama sistem ERP',
                'is_public' => true,
            ],
            [
                'group' => 'general',
                'key' => 'company_npwp',
                'value' => '01.234.567.8-528.000',
                'type' => 'string',
                'description' => 'Nomor Pokok Wajib Pajak (NPWP) perusahaan',
                'is_public' => true,
            ],
            [
                'group' => 'general',
                'key' => 'company_bpom_permit',
                'value' => 'CPOTB-2026/PROD/0921-HMI',
                'type' => 'string',
                'description' => 'Nomor Sertifikat Izin Industri Farmasi / CPOTB',
                'is_public' => true,
            ],
            [
                'group' => 'general',
                'key' => 'app_logo_type',
                'value' => 'icon',
                'type' => 'string',
                'description' => 'Tipe logo di navbar: icon (Lucide) atau image (URL gambar)',
                'is_public' => true,
            ],
            [
                'group' => 'general',
                'key' => 'app_logo_icon',
                'value' => 'Sparkles',
                'type' => 'string',
                'description' => 'Nama ikon Lucide untuk logo aplikasi jika tipe icon',
                'is_public' => true,
            ],

            // Theme Engine
            [
                'group' => 'theme',
                'key' => 'theme_color',
                'value' => 'emerald',
                'type' => 'string',
                'description' => 'Warna aksen utama sistem ERP (emerald, amber, blue, violet, rose, teal, cyan, indigo, slate)',
                'is_public' => true,
            ],
            [
                'group' => 'theme',
                'key' => 'theme_mode',
                'value' => 'dark',
                'type' => 'string',
                'description' => 'Mode tema antarmuka default (dark, light, system)',
                'is_public' => true,
            ],
            [
                'group' => 'theme',
                'key' => 'theme_radius',
                'value' => 'rounded-xl',
                'type' => 'string',
                'description' => 'Tingkat kelengkungan border radius elemen UI (rounded-none, rounded-md, rounded-lg, rounded-xl, rounded-2xl)',
                'is_public' => true,
            ],
            [
                'group' => 'theme',
                'key' => 'theme_density',
                'value' => 'comfortable',
                'type' => 'string',
                'description' => 'Kerapatan tata letak antarmuka (compact atau comfortable)',
                'is_public' => true,
            ],
            [
                'group' => 'theme',
                'key' => 'theme_navbar_style',
                'value' => 'glass',
                'type' => 'string',
                'description' => 'Gaya visual bilah navigasi (glass, solid, minimal)',
                'is_public' => true,
            ],

            // Document Numbering
            [
                'group' => 'numbering',
                'key' => 'numbering_schemes',
                'value' => json_encode($this->getDefaultNumberingSchemes()),
                'type' => 'json',
                'description' => 'Skema format penomoran otomatis seluruh dokumen transaksi ERP',
                'is_public' => false,
            ],

            // API & Integrations
            [
                'group' => 'api',
                'key' => 'api_enabled',
                'value' => 'true',
                'type' => 'boolean',
                'description' => 'Aktifkan endpoint REST API v1 untuk integrasi eksternal',
                'is_public' => true,
            ],
            [
                'group' => 'api',
                'key' => 'api_rate_limit',
                'value' => '120',
                'type' => 'integer',
                'description' => 'Batas permintaan API per menit per token autentikasi',
                'is_public' => false,
            ],
            [
                'group' => 'api',
                'key' => 'api_webhook_url',
                'value' => '',
                'type' => 'string',
                'description' => 'URL webhook tujuan untuk pengiriman event transaksi real-time',
                'is_public' => false,
            ],

            // Integrations Mode (Odoo Standalone / Optional)
            [
                'group' => 'integrations',
                'key' => 'odoo_mode',
                'value' => 'standalone',
                'type' => 'string',
                'description' => 'Mode integrasi Odoo: standalone (Mandiri Manual ERP tanpa Odoo) atau live_sync (Sinkronisasi XML-RPC Odoo)',
                'is_public' => true,
            ],
            [
                'group' => 'integrations',
                'key' => 'odoo_enabled',
                'value' => 'false',
                'type' => 'boolean',
                'description' => 'Status aktif konektor Odoo (opsional)',
                'is_public' => true,
            ],
        ];

        foreach ($defaults as $row) {
            SystemSetting::create($row);
        }
    }
}

<?php

namespace App\Modules\Quality\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Quality\Services\QualityDashboardService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class QualityApiController extends Controller
{
    public function __construct(
        protected QualityDashboardService $dashboardService
    ) {}

    /**
     * Get live Quality metrics.
     */
    public function metrics(): JsonResponse
    {
        return response()->json([
            'success' => true,
            'data' => $this->dashboardService->getDashboardMetrics(),
        ]);
    }

    /**
     * Update digital signature data for current user.
     */
    public function saveSignature(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'signature_data' => ['required', 'string'], // base64 or path
        ]);

        $user = $request->user();
        if ($user) {
            $user->signature_path = $validated['signature_data'];
            $user->save();
        }

        return response()->json([
            'success' => true,
            'message' => 'Tanda tangan digital berhasil disimpan.',
            'signature_path' => $user?->signature_path,
        ]);
    }

    /**
     * Predefined 6M Fishbone root cause suggestions for GMP Herbal plant.
     */
    public function fishboneSuggestions(): JsonResponse
    {
        return response()->json([
            'success' => true,
            'suggestions' => [
                'machine' => [
                    'Suhu ekstraktor vakum berfluktuasi > 5°C',
                    'Tekanan sealing mesin filling tidak merata',
                    'Sensor thermocouple perlu re-kalibrasi',
                    'Gasket tangki mixing bocor halus',
                    'Kecepatan putaran agitator tidak stabil',
                ],
                'man' => [
                    'Operator belum tersertifikasi refresh CPOB',
                    'Kelelahan operator shift malam',
                    'Kurang teliti verifikasi label bahan awal',
                    'Salah membaca skala buret volumetrik',
                    'Tidak menggunakan APD lengkap di area steril',
                ],
                'method' => [
                    'Instruksi kerja pembersihan (CIP) kurang spesifik',
                    'Lama waktu ekstraksi melebihi batas toleransi SOP',
                    'Kecepatan pengadukan sirup terlalu tinggi menyebabkan buih',
                    'Metode sampling tidak homogen di 3 titik tangki',
                    'Ketiadaan checklist verifikasi setting suhu awal',
                ],
                'materials' => [
                    'Kadar air simplisia temulawak di atas batas spesifikasi (> 10%)',
                    'Variasi ketebalan botol PET dari vendor sekunder',
                    'Bahan pengemas tutup botol cap seal rapuh',
                    'Pelarut etanol teknis terkontaminasi residu',
                    'Bahan aktif herbal menggumpal karena kelembaban tinggi',
                ],
                'milieu' => [
                    'RH Cleanroom kelas E melebihi batas 70%',
                    'Tekanan udara ruang mixing berbalik (negative pressure)',
                    'Suhu ruang staging timbang melebihi 25°C',
                    'Penerangan area inspeksi visual botol redup',
                    'Filter HEPA mengalami penurunan efisiensi aliran',
                ],
                'measurement' => [
                    'Alat uji HPLC drift baseline pada detektor UV',
                    'pH meter portabel belum dikalibrasi harian buffer 4 & 7',
                    'Refraktometer brix tidak dikompensasi temperatur',
                    'Timbangan analitik terkena hembusan angin laminar',
                    'Metode penetapan kadar belum re-validasi',
                ],
            ],
        ]);
    }
}

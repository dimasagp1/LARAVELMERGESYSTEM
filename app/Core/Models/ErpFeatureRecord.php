<?php

namespace App\Core\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class ErpFeatureRecord extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'erp_feature_records';

    protected $fillable = [
        'module_slug',
        'feature_route',
        'record_code',
        'title',
        'category',
        'pic_name',
        'entry_date',
        'status',
        'amount',
        'description',
        'custom_attributes',
        'created_by',
        'updated_by',
    ];

    protected function casts(): array
    {
        return [
            'entry_date' => 'date',
            'amount' => 'decimal:2',
            'custom_attributes' => 'array',
        ];
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function updater(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    /**
     * Generate an appropriate sequence record code for a feature route.
     */
    public static function generateNextCode(string $moduleSlug, string $featureRoute): string
    {
        $segments = explode('/', trim($featureRoute, '/'));
        $lastSegment = end($segments) ?: $moduleSlug;
        $cleanWord = preg_replace('/[^a-zA-Z0-9]/', '', $lastSegment) ?: 'REC';
        $prefix = strtoupper(substr($cleanWord, 0, 3));
        if (strlen($prefix) < 3) {
            $prefix = str_pad($prefix, 3, 'X');
        }

        $year = date('Y');
        $prefixPattern = "{$prefix}-{$year}-";

        $lastRecord = static::withTrashed()
            ->where('feature_route', $featureRoute)
            ->where('record_code', 'like', "{$prefixPattern}%")
            ->orderByDesc('id')
            ->first();

        $nextNumber = 1;
        if ($lastRecord && preg_match('/-(\d+)$/', $lastRecord->record_code, $matches)) {
            $nextNumber = ((int) $matches[1]) + 1;
        }

        return sprintf('%s-%s-%04d', $prefix, $year, $nextNumber);
    }
}

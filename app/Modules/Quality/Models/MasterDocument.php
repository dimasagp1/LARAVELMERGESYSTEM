<?php

namespace App\Modules\Quality\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class MasterDocument extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'quality_master_documents';

    protected $fillable = [
        'folder_id',
        'level',
        'document_code',
        'document_number',
        'title',
        'scope',
        'department',
        'document_links',
        'tempat_penyimpanan',
        'no_perubahan_cc',
        'status',
        'effective_date',
        'revision',
        'custom_attributes',
    ];

    protected function casts(): array
    {
        return [
            'level' => 'integer',
            'effective_date' => 'date',
            'document_links' => 'array',
            'custom_attributes' => 'array',
        ];
    }

    /**
     * @return BelongsTo<DocumentFolder, $this>
     */
    public function folder(): BelongsTo
    {
        return $this->belongsTo(DocumentFolder::class, 'folder_id');
    }

    /**
     * @return HasMany<DocumentRevision, $this>
     */
    public function revisions(): HasMany
    {
        return $this->hasMany(DocumentRevision::class, 'master_document_id')->orderByDesc('effective_date');
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('status', 'Active');
    }

    public function scopeObsolete(Builder $query): Builder
    {
        return $query->where('status', 'Obsolete');
    }

    public function scopeByLevel(Builder $query, int $level): Builder
    {
        return $query->where('level', $level);
    }

    public function scopeByDepartment(Builder $query, string $dept): Builder
    {
        return $query->where('department', $dept);
    }

    public function scopeByScope(Builder $query, string $scope): Builder
    {
        return $query->where('scope', $scope);
    }

    public function getLevelLabelAttribute(): string
    {
        return match ($this->level) {
            1 => 'Level 1: Manual Mutu (MM)',
            2 => 'Level 2: Prosedur Tetap (SOP)',
            3 => 'Level 3: Instruksi Kerja (IK)',
            4 => 'Level 4: Formulir & Rekaman (FR)',
            default => "Level {$this->level}",
        };
    }
}

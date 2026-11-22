<?php

namespace App\Modules\Quality\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class DocumentFolder extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'quality_document_folders';

    protected $fillable = [
        'parent_id',
        'name',
        'color',
        'icon',
        'description',
        'sort_order',
        'custom_attributes',
    ];

    protected function casts(): array
    {
        return [
            'custom_attributes' => 'array',
        ];
    }

    /**
     * @return BelongsTo<DocumentFolder, $this>
     */
    public function parent(): BelongsTo
    {
        return $this->belongsTo(self::class, 'parent_id');
    }

    /**
     * @return HasMany<DocumentFolder, $this>
     */
    public function children(): HasMany
    {
        return $this->hasMany(self::class, 'parent_id')->orderBy('sort_order');
    }

    /**
     * @return HasMany<MasterDocument, $this>
     */
    public function documents(): HasMany
    {
        return $this->hasMany(MasterDocument::class, 'folder_id');
    }

    /**
     * Get all recursive child folder IDs including self.
     *
     * @return list<int>
     */
    public function getAllDescendantIds(): array
    {
        $ids = [$this->id];
        foreach ($this->children as $child) {
            $ids = array_merge($ids, $child->getAllDescendantIds());
        }

        return $ids;
    }

    /**
     * Total documents contained in this folder and its descendants.
     */
    public function getTotalDocumentsCountAttribute(): int
    {
        $ids = $this->getAllDescendantIds();

        return MasterDocument::whereIn('folder_id', $ids)->count();
    }
}

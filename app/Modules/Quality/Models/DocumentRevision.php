<?php

namespace App\Modules\Quality\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DocumentRevision extends Model
{
    use HasFactory;

    protected $table = 'quality_document_revisions';

    protected $fillable = [
        'master_document_id',
        'created_by_user_id',
        'revision',
        'title',
        'effective_date',
        'change_notes',
    ];

    protected function casts(): array
    {
        return [
            'effective_date' => 'date',
        ];
    }

    /**
     * @return BelongsTo<MasterDocument, $this>
     */
    public function masterDocument(): BelongsTo
    {
        return $this->belongsTo(MasterDocument::class, 'master_document_id');
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function createdByUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by_user_id');
    }
}

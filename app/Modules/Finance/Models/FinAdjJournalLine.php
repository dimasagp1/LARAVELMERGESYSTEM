<?php

namespace App\Modules\Finance\Models;

use App\Core\Models\Department;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class FinAdjJournalLine extends Model
{
    use HasFactory;

    protected $table = 'fin_adj_journal_lines';

    protected $guarded = ['id'];

    protected $casts = [
        'debit' => 'decimal:2',
        'credit' => 'decimal:2',
    ];

    public function journal(): BelongsTo
    {
        return $this->belongsTo(FinAdjJournal::class, 'adj_journal_id');
    }

    public function adjJournal(): BelongsTo
    {
        return $this->belongsTo(FinAdjJournal::class, 'adj_journal_id');
    }

    public function coaAccount(): BelongsTo
    {
        return $this->belongsTo(FinCoaAccount::class, 'coa_account_id');
    }

    public function fsli(): BelongsTo
    {
        return $this->belongsTo(FinFsli::class, 'fsli_id');
    }

    public function department(): BelongsTo
    {
        return $this->belongsTo(Department::class, 'department_id');
    }
}

<?php

namespace App\Modules\SupplyChain\Models;

use App\Core\Models\Bom;
use App\Core\Models\Product;
use App\Core\Models\Vendor;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ScmMrpItem extends Model
{
    use HasFactory;

    protected $table = 'scm_mrp_items';

    protected $guarded = ['id'];

    protected $casts = [
        'gross_requirement' => 'decimal:2',
        'on_hand_stock' => 'decimal:2',
        'allocated_stock' => 'decimal:2',
        'outstanding_po_qty' => 'decimal:2',
        'safety_stock' => 'decimal:2',
        'net_requirement' => 'decimal:2',
        'suggested_order_date' => 'date',
        'suggested_due_date' => 'date',
    ];

    public function mrpRun(): BelongsTo
    {
        return $this->belongsTo(ScmMrpRun::class, 'mrp_run_id');
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class, 'product_id');
    }

    public function bom(): BelongsTo
    {
        return $this->belongsTo(Bom::class, 'bom_id');
    }

    public function vendor(): BelongsTo
    {
        return $this->belongsTo(Vendor::class, 'vendor_id');
    }
}

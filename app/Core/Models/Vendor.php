<?php

namespace App\Core\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Carbon;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

/**
 * Class Vendor
 *
 * @property int $id
 * @property string $code
 * @property string $name
 * @property string|null $category
 * @property string|null $contact_person
 * @property string|null $email
 * @property string|null $phone
 * @property string|null $address
 * @property string|null $tax_id
 * @property int $payment_term_days
 * @property bool $is_avl
 * @property float|null $avl_rating
 * @property string|null $notes
 * @property bool $is_active
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property Carbon|null $deleted_at
 */
class Vendor extends Model
{
    use HasFactory;
    use LogsActivity;
    use SoftDeletes;

    protected $table = 'vendors';

    protected $fillable = [
        'code',
        'name',
        'category',
        'contact_person',
        'email',
        'phone',
        'address',
        'tax_id',
        'payment_term_days',
        'is_avl',
        'avl_rating',
        'notes',
        'is_active',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'payment_term_days' => 'integer',
            'is_avl' => 'boolean',
            'avl_rating' => 'decimal:2',
            'is_active' => 'boolean',
        ];
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly([
                'code',
                'name',
                'category',
                'payment_term_days',
                'is_avl',
                'avl_rating',
                'is_active',
            ])
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs();
    }
}

<?php

namespace Vanguard\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

/**
 * Vanguard\Models\EmailSettings
 *
 * @property int $id
 * @property string $code
 * @property float $credit
 * @property string $format
 * @property Carbon $start_at
 * @property Carbon $end_at
 * @property boolean $is_active
 * @property string|null $notes
 * @mixin \Eloquent
 * @property string|null $row_hash
 * @property int|null $row_order
 */
class PromoCode extends Model
{
    /**
     * @var string
     */
    protected $table = 'promo_codes';

    /**
     * @var string[]
     */
    protected $fillable = [
        'code',
        'credit',
        'start_at',
        'end_at',
        'is_active',
        'notes',
        'row_hash',
        'row_order',
    ];

    protected $casts = [
        'start_at' => 'datetime',
        'end_at' => 'datetime',
    ];
}

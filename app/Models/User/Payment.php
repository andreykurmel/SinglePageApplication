<?php

namespace Vanguard\Models\User;

use Illuminate\Database\Eloquent\Model;
use Vanguard\User;

/**
 * Vanguard\Models\User\Payment
 *
 * @property int $id
 * @property int $user_id
 * @property string $due_date
 * @property float $amount
 * @property string|null $method
 * @property string|null $notes
 * @property int|null $created_by
 * @property string $created_on
 * @property int|null $modified_by
 * @property string $modified_on
 * @property string|null $type
 * @property string|null $from
 * @property string|null $from_details
 * @property string|null $to
 * @property string|null $row_hash
 * @property string|null $transaction_id
 * @property int $row_order
 * @property-read \Vanguard\User|null $_created_user
 * @property-read \Vanguard\User|null $_modified_user
 * @method static \Illuminate\Database\Eloquent\Builder|\Vanguard\Models\User\Payment newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\Vanguard\Models\User\Payment newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\Vanguard\Models\User\Payment query()
 * @method static \Illuminate\Database\Eloquent\Builder|\Vanguard\Models\User\Payment whereAmount($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Vanguard\Models\User\Payment whereCreatedBy($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Vanguard\Models\User\Payment whereCreatedOn($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Vanguard\Models\User\Payment whereDueDate($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Vanguard\Models\User\Payment whereFrom($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Vanguard\Models\User\Payment whereFromDetails($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Vanguard\Models\User\Payment whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Vanguard\Models\User\Payment whereMethod($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Vanguard\Models\User\Payment whereModifiedBy($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Vanguard\Models\User\Payment whereModifiedOn($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Vanguard\Models\User\Payment whereNotes($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Vanguard\Models\User\Payment whereRowHash($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Vanguard\Models\User\Payment whereRowOrder($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Vanguard\Models\User\Payment whereTo($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Vanguard\Models\User\Payment whereTransactionId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Vanguard\Models\User\Payment whereType($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Vanguard\Models\User\Payment whereUserId($value)
 * @mixin \Eloquent
 */
class Payment extends Model
{
    protected $table = 'payments';

    public $timestamps = false;

    protected $fillable = [
        'user_id',
        'type',
        'from',
        'from_details',
        'to',
        'amount',
        'due_date',
        'transaction_id',
        'notes',
        'created_by',
        'created_on',
        'modified_by',
        'modified_on'
    ];


    public function _created_user() {
        return $this->belongsTo(User::class, 'created_by', 'id');
    }

    public function _modified_user() {
        return $this->belongsTo(User::class, 'modified_by', 'id');
    }
}

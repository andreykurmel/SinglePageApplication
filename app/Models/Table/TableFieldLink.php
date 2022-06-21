<?php

namespace Vanguard\Models\Table;

use Illuminate\Database\Eloquent\Model;
use Vanguard\Models\DataSetPermissions\TableRefCondition;
use Vanguard\Models\User\UserPaymentKey;
use Vanguard\User;

/**
 * Vanguard\Models\Table\TableFieldLink
 *
 * @property int $id
 * @property int $table_field_id
 * @property string $link_type
 * @property string|null $link_display
 * @property string $link_pos
 * @property string $icon
 * @property string|null $tooltip
 * @property int|null $table_ref_condition_id
 * @property int|null $listing_field_id
 * @property int|null $created_by
 * @property string $created_on
 * @property int|null $modified_by
 * @property string $modified_on
 * @property int|null $table_app_id
 * @property string|null $address_field
 * @property int|null $address_field_id
 * @property int|null $link_field_lat
 * @property int|null $link_field_lng
 * @property int|null $link_field_address
 * @property int|null $always_available
 * @property string|null $web_prefix
 * @property int|null $hide_empty_web
 * @property string|null $popup_display
 * @property int $show_sum
 * @property int|null $add_record_limit
 * @property int|null $already_added_records
 * @property string|null $link_preview_fields
 * @property string|null $email_addon_fields
 * @property int $link_preview_show_flds
 * @property int|null $payment_amount_fld_id
 * @property int|null $payment_history_payee_fld_id
 * @property int|null $payment_history_amount_fld_id
 * @property int|null $payment_history_date_fld_id
 * @property int|null $payment_method_fld_id
 * @property int|null $payment_paypal_keys_id
 * @property int|null $payment_stripe_keys_id
 * @property int|null $payment_description_fld_id
 * @property int|null $payment_customer_fld_id
 * @property-read \Vanguard\Models\Table\TableField|null $_address_field
 * @property-read \Vanguard\User|null $_created_user
 * @property-read \Vanguard\Models\Table\TableField $_field
 * @property-read \Vanguard\Models\Table\TableField|null $_listing_field
 * @property-read \Vanguard\Models\Table\TableField|null $_map_field_address
 * @property-read \Vanguard\Models\Table\TableField|null $_map_field_lat
 * @property-read \Vanguard\Models\Table\TableField|null $_map_field_lng
 * @property-read \Vanguard\User|null $_modified_user
 * @property-read \Illuminate\Database\Eloquent\Collection|\Vanguard\Models\Table\TableFieldLinkParam[] $_params
 * @property-read int|null $_params_count
 * @property-read \Vanguard\Models\Table\TableField|null $_payment_amount_fld
 * @property-read \Vanguard\Models\Table\TableField|null $_payment_customer_fld
 * @property-read \Vanguard\Models\Table\TableField|null $_payment_description_fld
 * @property-read \Vanguard\Models\Table\TableField|null $_payment_history_amount_fld
 * @property-read \Vanguard\Models\Table\TableField|null $_payment_history_date_fld
 * @property-read \Vanguard\Models\Table\TableField|null $_payment_history_payee_fld
 * @property-read \Vanguard\Models\Table\TableField|null $_payment_method_fld
 * @property-read \Vanguard\Models\User\UserPaymentKey|null $_paypal_user_key
 * @property-read \Vanguard\Models\DataSetPermissions\TableRefCondition|null $_ref_condition
 * @property-read \Vanguard\Models\User\UserPaymentKey|null $_stripe_user_key
 * @property-read \Illuminate\Database\Eloquent\Collection|\Vanguard\Models\Table\TableFieldLinkToDcr[] $_to_dcrs
 * @property-read int|null $_to_dcrs_count
 * @mixin \Eloquent
 */
class TableFieldLink extends Model
{
    protected $table = 'table_field_links';

    public $timestamps = false;

    protected $fillable = [
        'table_field_id',
        'link_type', // 'Record', 'Web', 'App', 'GMap', 'GEarth'
        'link_display', // 'Popup','Table','RorT'
        'link_pos', // 'before', 'after'
        'icon',
        'tooltip',
        'popup_display', // 'Table', 'Listing', 'Boards'
        'show_sum',
        'table_ref_condition_id',
        'listing_field_id',
        'address_field_id',
        'table_app_id',
        'link_field_lat',
        'link_field_lng',
        'link_field_address',
        'always_available',
        'web_prefix',
        'hide_empty_web',
        'add_record_limit',
        'already_added_records',
        'link_preview_fields',
        'link_preview_show_flds',
        'email_addon_fields',

        'payment_method_fld_id', //'stripe','paypal'
        'payment_paypal_keys_id',
        'payment_stripe_keys_id',
        'payment_description_fld_id',
        'payment_amount_fld_id',
        'payment_customer_fld_id',
        'payment_history_payee_fld_id',
        'payment_history_amount_fld_id',
        'payment_history_date_fld_id',

        'created_by',
        'created_on',
        'modified_by',
        'modified_on'
    ];


    public function _field() {
        return $this->belongsTo(TableField::class, 'table_field_id', 'id');
    }

    public function _ref_condition() {
        return $this->hasOne(TableRefCondition::class, 'id', 'table_ref_condition_id');
    }

    public function _listing_field() {
        return $this->hasOne(TableField::class, 'id', 'listing_field_id');
    }

    public function _address_field() {
        return $this->hasOne(TableField::class, 'id', 'address_field_id');
    }

    public function _map_field_lat() {
        return $this->hasOne(TableField::class, 'id', 'link_field_lat');
    }

    public function _map_field_lng() {
        return $this->hasOne(TableField::class, 'id', 'link_field_lng');
    }

    public function _map_field_address() {
        return $this->hasOne(TableField::class, 'id', 'link_field_address');
    }

    public function _params() {
        return $this->hasMany(TableFieldLinkParam::class, 'table_field_link_id', 'id');
    }

    public function _to_dcrs() {
        return $this->hasMany(TableFieldLinkToDcr::class, 'table_field_link_id', 'id');
    }

    public function _payment_method_fld() {
        return $this->hasOne(TableField::class, 'id', 'payment_method_fld_id');
    }
    public function _paypal_user_key() {
        return $this->hasOne(UserPaymentKey::class, 'id', 'payment_paypal_keys_id');
    }
    public function _stripe_user_key() {
        return $this->hasOne(UserPaymentKey::class, 'id', 'payment_stripe_keys_id');
    }
    public function _payment_customer_fld() {
        return $this->hasOne(TableField::class, 'id', 'payment_customer_fld_id');
    }
    public function _payment_amount_fld() {
        return $this->hasOne(TableField::class, 'id', 'payment_amount_fld_id');
    }
    public function _payment_description_fld() {
        return $this->hasOne(TableField::class, 'id', 'payment_description_fld_id');
    }
    public function _payment_history_payee_fld() {
        return $this->hasOne(TableField::class, 'id', 'payment_history_payee_fld_id');
    }
    public function _payment_history_amount_fld() {
        return $this->hasOne(TableField::class, 'id', 'payment_history_amount_fld_id');
    }
    public function _payment_history_date_fld() {
        return $this->hasOne(TableField::class, 'id', 'payment_history_date_fld_id');
    }


    public function _created_user() {
        return $this->belongsTo(User::class, 'created_by', 'id');
    }

    public function _modified_user() {
        return $this->belongsTo(User::class, 'modified_by', 'id');
    }
}

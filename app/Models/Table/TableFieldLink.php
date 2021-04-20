<?php

namespace Vanguard\Models\Table;

use Illuminate\Database\Eloquent\Model;
use Vanguard\Models\DataSetPermissions\TableRefCondition;
use Vanguard\Models\User\UserPaymentKey;
use Vanguard\User;

class TableFieldLink extends Model
{
    protected $table = 'table_field_links';

    public $timestamps = false;

    protected $fillable = [
        'table_field_id',
        'link_type',
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
        'add_record_limit',
        'already_added_records',
        'link_preview_fields',
        'link_preview_show_flds',

        'payment_method_fld_id', //'stripe','paypal'
        'payment_paypal_keys_id',
        'payment_stripe_keys_id',
        'payment_amount_fld_id',
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
    public function _payment_amount_fld() {
        return $this->hasOne(TableField::class, 'id', 'payment_amount_fld_id');
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

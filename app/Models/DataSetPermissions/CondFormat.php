<?php

namespace Vanguard\Models\DataSetPermissions;

use Illuminate\Database\Eloquent\Model;
use Vanguard\Models\Table\Table;
use Vanguard\User;

class CondFormat extends Model
{
    protected $table = 'cond_formats';

    public $timestamps = false;

    protected $fillable = [
        'table_id',
        'user_id',
        'name',
        'table_column_group_id',
        'table_row_group_id',
        'bkgd_color',
        'color',
        'font',
        'font_size',
        'activity',
        'status',
        'show_form_data',
        'show_table_data',
        'row_order',
        'created_by',
        'created_on',
        'modified_by',
        'modified_on'
    ];


    public function scopeActiveForUser($q, int $user_id = null, int $table_permission_id = null) {
        return $q->leftJoin('cond_format_user_settings', function($header_q) {
                $header_q->whereRaw('cond_formats.id = cond_format_user_settings.cond_format_id');
            })
            ->where(function ($cf) {
                $cf->where('cond_formats.status', 1);
                $cf->orWhere('cond_format_user_settings.status', 1);
            })
            ->where(function ($cf) use ($user_id, $table_permission_id) {
                $cf->where('cond_formats.user_id', $user_id);
                $cf->orWhereHas('_table_permissions', function ($tp) use ($table_permission_id) {
                    $tp->applyIsActiveForUserOrPermission($table_permission_id, true);
                });
            })
            ->select('cond_formats.*');
    }


    public function _table() {
        return $this->belongsTo(Table::class, 'table_id', 'id');
    }

    public function _column_group() {
        return $this->hasOne(TableColumnGroup::class, 'id', 'table_column_group_id');
    }

    public function _row_group() {
        return $this->hasOne(TableRowGroup::class, 'id', 'table_row_group_id');
    }

    public function _table_permissions() {
        return $this->belongsToMany(TablePermission::class, 'table_permissions_2_cond_formats', 'cond_format_id', 'table_permission_id')
            ->as('_link')
            ->withPivot(['always_on','visible_shared']);
    }

    public function _user_settings() {
        return $this->hasMany(CondFormatUserSetting::class, 'cond_format_id', 'id');
    }


    public function _created_user() {
        return $this->belongsTo(User::class, 'created_by', 'id');
    }

    public function _modified_user() {
        return $this->belongsTo(User::class, 'modified_by', 'id');
    }
}

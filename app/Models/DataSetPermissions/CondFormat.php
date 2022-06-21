<?php

namespace Vanguard\Models\DataSetPermissions;

use Illuminate\Database\Eloquent\Model;
use Vanguard\Models\Table\Table;
use Vanguard\User;

/**
 * Vanguard\Models\DataSetPermissions\CondFormat
 *
 * @property int $id
 * @property string $name
 * @property int $table_id
 * @property int $user_id
 * @property int $is_system
 * @property int|null $table_column_group_id
 * @property int|null $table_row_group_id
 * @property string|null $color
 * @property string|null $font
 * @property string|null $activity
 * @property int $status
 * @property int|null $created_by
 * @property string $created_on
 * @property int|null $modified_by
 * @property string $modified_on
 * @property string|null $bkgd_color
 * @property int|null $font_size
 * @property int|null $row_order
 * @property int $show_table_data
 * @property int $show_form_data
 * @property-read \Vanguard\Models\DataSetPermissions\TableColumnGroup|null $_column_group
 * @property-read \Vanguard\User|null $_created_user
 * @property-read \Vanguard\User|null $_modified_user
 * @property-read \Vanguard\Models\DataSetPermissions\TableRowGroup|null $_row_group
 * @property-read \Vanguard\Models\Table\Table $_table
 * @property-read \Illuminate\Database\Eloquent\Collection|\Vanguard\Models\DataSetPermissions\TablePermission[] $_table_permissions
 * @property-read int|null $_table_permissions_count
 * @property-read \Illuminate\Database\Eloquent\Collection|\Vanguard\Models\DataSetPermissions\CondFormatUserSetting[] $_user_settings
 * @property-read int|null $_user_settings_count
 * @method static \Illuminate\Database\Eloquent\Builder|\Vanguard\Models\DataSetPermissions\CondFormat activeForUser($user_id = null, $table_permission_id = null)
 * @method static \Illuminate\Database\Eloquent\Builder|\Vanguard\Models\DataSetPermissions\CondFormat newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\Vanguard\Models\DataSetPermissions\CondFormat newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\Vanguard\Models\DataSetPermissions\CondFormat query()
 * @method static \Illuminate\Database\Eloquent\Builder|\Vanguard\Models\DataSetPermissions\CondFormat whereActivity($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Vanguard\Models\DataSetPermissions\CondFormat whereBkgdColor($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Vanguard\Models\DataSetPermissions\CondFormat whereColor($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Vanguard\Models\DataSetPermissions\CondFormat whereCreatedBy($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Vanguard\Models\DataSetPermissions\CondFormat whereCreatedOn($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Vanguard\Models\DataSetPermissions\CondFormat whereFont($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Vanguard\Models\DataSetPermissions\CondFormat whereFontSize($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Vanguard\Models\DataSetPermissions\CondFormat whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Vanguard\Models\DataSetPermissions\CondFormat whereIsSystem($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Vanguard\Models\DataSetPermissions\CondFormat whereModifiedBy($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Vanguard\Models\DataSetPermissions\CondFormat whereModifiedOn($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Vanguard\Models\DataSetPermissions\CondFormat whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Vanguard\Models\DataSetPermissions\CondFormat whereRowOrder($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Vanguard\Models\DataSetPermissions\CondFormat whereShowFormData($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Vanguard\Models\DataSetPermissions\CondFormat whereShowTableData($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Vanguard\Models\DataSetPermissions\CondFormat whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Vanguard\Models\DataSetPermissions\CondFormat whereTableColumnGroupId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Vanguard\Models\DataSetPermissions\CondFormat whereTableId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Vanguard\Models\DataSetPermissions\CondFormat whereTableRowGroupId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Vanguard\Models\DataSetPermissions\CondFormat whereUserId($value)
 * @mixin \Eloquent
 */
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

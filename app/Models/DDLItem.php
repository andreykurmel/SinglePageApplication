<?php

namespace Vanguard\Models;

use Illuminate\Database\Eloquent\Model;
use Vanguard\Models\DataSetPermissions\TableRowGroup;
use Vanguard\User;

/**
 * Vanguard\Models\DDLItem
 *
 * @property int $id
 * @property int $ddl_id
 * @property string $option
 * @property string|null $show_option
 * @property string|null $notes
 * @property int|null $created_by
 * @property string $created_on
 * @property int|null $modified_by
 * @property string $modified_on
 * @property string|null $description
 * @property string|null $image_path
 * @property int|null $apply_target_row_group_id
 * @property-read \Vanguard\Models\DataSetPermissions\TableRowGroup|null $_apply_row_group
 * @property-read \Vanguard\User|null $_created_user
 * @property-read \Vanguard\Models\DDL $_ddl
 * @property-read \Vanguard\User|null $_modified_user
 * @method static \Illuminate\Database\Eloquent\Builder|\Vanguard\Models\DDLItem isTbRef()
 * @method static \Illuminate\Database\Eloquent\Builder|\Vanguard\Models\DDLItem newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\Vanguard\Models\DDLItem newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\Vanguard\Models\DDLItem query()
 * @method static \Illuminate\Database\Eloquent\Builder|\Vanguard\Models\DDLItem whereApplyTargetRowGroupId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Vanguard\Models\DDLItem whereCreatedBy($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Vanguard\Models\DDLItem whereCreatedOn($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Vanguard\Models\DDLItem whereDdlId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Vanguard\Models\DDLItem whereDescription($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Vanguard\Models\DDLItem whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Vanguard\Models\DDLItem whereImagePath($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Vanguard\Models\DDLItem whereModifiedBy($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Vanguard\Models\DDLItem whereModifiedOn($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Vanguard\Models\DDLItem whereNotes($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Vanguard\Models\DDLItem whereOption($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Vanguard\Models\DDLItem whereShowOption($value)
 * @mixin \Eloquent
 * @property string|null $opt_color
 * @method static \Illuminate\Database\Eloquent\Builder|\Vanguard\Models\DDLItem whereOptColor($value)
 */
class DDLItem extends Model
{
    protected $table = 'ddl_items';

    public $timestamps = false;

    protected $fillable = [
        'ddl_id',
        'apply_target_row_group_id',
        'option',
        'show_option',
        'opt_color',
        'image_path',
        'notes',
        'created_by',
        'created_on',
        'modified_by',
        'modified_on'
    ];


    /**
     * @param $q
     * @return mixed
     */
    public function scopeIsTbRef($q) {
        return $q->whereNotNull('image_path');
    }


    public function _ddl() {
        return $this->belongsTo(DDL::class, 'ddl_id', 'id');
    }

    public function _apply_row_group() {
        return $this->hasOne(TableRowGroup::class, 'id', 'apply_target_row_group_id');
    }


    public function _created_user() {
        return $this->belongsTo(User::class, 'created_by', 'id');
    }

    public function _modified_user() {
        return $this->belongsTo(User::class, 'modified_by', 'id');
    }
}

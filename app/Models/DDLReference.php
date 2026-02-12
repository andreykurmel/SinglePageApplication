<?php

namespace Vanguard\Models;

use Illuminate\Database\Eloquent\Model;
use Vanguard\Models\DataSetPermissions\TableRefCondition;
use Vanguard\Models\DataSetPermissions\TableRowGroup;
use Vanguard\Models\Table\TableField;
use Vanguard\User;

/**
 * Vanguard\Models\DDLReference
 *
 * @property int $id
 * @property int $ddl_id
 * @property int $table_ref_condition_id
 * @property int|null $target_field_id
 * @property string|null $notes
 * @property int|null $created_by
 * @property string $created_on
 * @property int|null $modified_by
 * @property string $modified_on
 * @property int|null $image_field_id
 * @property int|null $color_field_id
 * @property int|null $max_selections_field_id
 * @property int|null $has_individ_images
 * @property int|null $has_individ_colors
 * @property int|null $has_individ_max_selections
 * @property int|null $apply_target_row_group_id
 * @property int|null $show_field_id
 * @property-read \Vanguard\Models\DataSetPermissions\TableRowGroup|null $_apply_row_group
 * @property-read \Vanguard\User|null $_created_user
 * @property-read \Vanguard\Models\DDL $_ddl
 * @property-read \Vanguard\Models\Table\TableField|null $_image_field
 * @property-read \Vanguard\Models\Table\TableField|null $_color_field
 * @property-read \Vanguard\User|null $_modified_user
 * @property-read \Vanguard\Models\DataSetPermissions\TableRefCondition|null $_ref_condition
 * @property-read \Vanguard\Models\Table\TableField|null $_show_field
 * @property-read \Vanguard\Models\Table\TableField|null $_target_field
 * @property-read \Vanguard\Models\Table\TableField|null $_max_selections_field
 * @mixin \Eloquent
 * @property-read \Illuminate\Database\Eloquent\Collection|\Vanguard\Models\DDLReferenceColor[] $_reference_clr_img
 * @property-read int|null $_ref_clr_img_count
 */
class DDLReference extends Model
{
    protected $table = 'ddl_references';

    public $timestamps = false;

    protected $fillable = [
        'ddl_id',
        'table_ref_condition_id',
        'apply_target_row_group_id',
        'target_field_id',
        'show_field_id',
        'image_field_id',
        'color_field_id',
        'max_selections_field_id',
        'has_individ_images',
        'has_individ_colors',
        'has_individ_max_selections',
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
        return $q->where(function ($i) {
            //$i->whereNotNull('target_field_id');
            $i->whereNotNull('show_field_id');
            $i->orWhereNotNull('image_field_id');
            $i->orWhereNotNull('color_field_id');
        });
    }


    /**
     * @return Table\Table|null
     */
    public function ref_table()
    {
        if ($this->_ref_condition && !$this->_ref_condition->incoming_allow) {
            throw new \Exception("The RS is disallowed in the Source table.", 1);
        }
        return $this->_ref_condition ? $this->_ref_condition->_ref_table : null;
    }

    public function search_in_ref_colors()
    {
        return $this->_reference_clr_img->count() >= 100
            ? $this->_reference_clr_img()
            : $this->_reference_clr_img;
    }

    public function get_ref_clr_img_count(): int
    {
        return $this->hasMany(DDLReferenceColor::class, 'ddl_reference_id', 'id')->count();
    }


    //RELATIONS
    public function _ddl() {
        return $this->belongsTo(DDL::class, 'ddl_id', 'id');
    }

    public function _ref_condition() {
        return $this->hasOne(TableRefCondition::class, 'id', 'table_ref_condition_id');
    }

    public function _apply_row_group() {
        return $this->hasOne(TableRowGroup::class, 'id', 'apply_target_row_group_id');
    }

    public function _target_field() {
        return $this->hasOne(TableField::class, 'id', 'target_field_id');
    }

    public function _image_field() {
        return $this->hasOne(TableField::class, 'id', 'image_field_id');
    }

    public function _color_field() {
        return $this->hasOne(TableField::class, 'id', 'color_field_id');
    }

    public function _max_selections_field() {
        return $this->hasOne(TableField::class, 'id', 'max_selections_field_id');
    }

    public function _show_field() {
        return $this->hasOne(TableField::class, 'id', 'show_field_id');
    }

    public function _reference_clr_img() {
        return $this->hasMany(DDLReferenceColor::class, 'ddl_reference_id', 'id')->limit(100);
    }

    public function _applied_fields() {
        return $this->hasMany(TableField::class, 'ddl_id', 'ddl_id');
    }


    public function _created_user() {
        return $this->belongsTo(User::class, 'created_by', 'id');
    }

    public function _modified_user() {
        return $this->belongsTo(User::class, 'modified_by', 'id');
    }
}

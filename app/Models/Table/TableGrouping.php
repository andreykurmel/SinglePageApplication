<?php

namespace Vanguard\Models\Table;

use Eloquent;
use Illuminate\Database\Eloquent\Model;
use Vanguard\Models\DataSetPermissions\TableColumnGroup;
use Vanguard\Models\DataSetPermissions\TablePermission;

/**
 * Vanguard\Models\Table\TableTournament
 *
 * @property int $id
 * @property int $table_id
 * @property string $name
 * @property string|null $rg_data_range
 * @property int|null $rg_colgroup_id
 * @property string $rg_alignment
 * @property string|null $description
 * @property int $rg_active
 * @property Table $_table
 * @property TableColumnGroup $_col_group
 * @property TableGroupingField[] $_settings
 * @property TableGroupingStat[] $_stats
 * @mixin Eloquent
 */
class TableGrouping extends Model
{
    public $timestamps = false;

    protected $table = 'table_groupings';

    protected $fillable = [
        'table_id',
        'name',
        'rg_data_range',
        'rg_colgroup_id',
        'rg_alignment',
        'description',
        'rg_active',
    ];


    public function _table()
    {
        return $this->belongsTo(Table::class, 'table_id', 'id');
    }

    public function _col_group()
    {
        return $this->belongsTo(TableColumnGroup::class, 'rg_colgroup_id', 'id');
    }

    public function _table_permissions()
    {
        return $this->belongsToMany(TablePermission::class, 'table_grouping_rights', 'table_grouping_id', 'table_permission_id')
            ->as('_right')
            ->withPivot(['id', 'table_grouping_id', 'table_permission_id', 'can_edit']);
    }

    public function _settings()
    {
        return $this->hasMany(TableGroupingField::class, 'grouping_id', 'id')
            ->orderBy('row_order');
    }

    public function _global_stats()
    {
        return $this->hasMany(TableGroupingStat::class, 'grouping_id', 'id');
    }

    public function _grouping_rights()
    {
        return $this->hasMany(TableGroupingRight::class, 'table_grouping_id', 'id');
    }

    public function _gr_related_fields()
    {
        return $this->belongsToMany(TableField::class, 'table_grouping_related_fields', 'grouping_id', 'field_id')
            ->as('_grs')
            ->withPivot(['id', 'fld_visible', 'fld_order']);
    }
}

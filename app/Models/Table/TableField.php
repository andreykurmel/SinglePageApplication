<?php

namespace Vanguard\Models\Table;

use Illuminate\Database\Eloquent\Model;
use Vanguard\Models\DataSetPermissions\TableColumnGroup;
use Vanguard\Models\DataSetPermissions\TableRefCondition;
use Vanguard\Models\DataSetPermissions\TableRowGroupCondition;
use Vanguard\Models\DDL;
use Vanguard\Models\File;
use Vanguard\Repositories\Tablda\TableRepository;
use Vanguard\User;

class TableField extends Model
{
    public $timestamps = false;
    protected $table = 'table_fields';
    protected $fillable = [
        'table_id',
        'field',
        'name',
        'unit',
        'unit_ddl_id',
        'header_unit_ddl',
        'header_background',
        'input_type',
        'is_uniform_formula',
        'ddl_id',
        'ddl_add_option',
        'ddl_auto_fill',
        'ddl_style', // ['ddl','panel']
        'tooltip',
        'tooltip_show',
        'placeholder_content',
        'placeholder_only_form',
        'show_history',
        'default_stats',
        'f_type',
        'f_size',
        'f_default',
        'f_required',
        'f_format',
        'f_formula',
        'verttb_he_auto',
        'verttb_cell_height',
        'verttb_row_height',
        'formula',
        'formula_symbol',
        'is_lat_field', // Type is 'Radio'
        'is_long_field', // Type is 'Radio'
        'map_find_street_field', // Type is 'Radio'
        'map_find_city_field', // Type is 'Radio'
        'map_find_state_field', // Type is 'Radio'
        'map_find_county_field', // Type is 'Radio'
        'map_find_zip_field', // Type is 'Radio'
        'info_box',
        'is_info_header_field', // Type is 'Radio'
        'active_links',
        'kanban_group',
        'is_show_on_board',
        'is_image_on_board',
        'is_search_autocomplete_display',
        'is_unique_collection',
        'is_default_show_in_popup',
        'is_table_field_in_popup',
        'is_start_table_popup',
        'is_topbot_in_popup',
        'is_dcr_section',

        //gantt settings
        'is_gantt_group', // Type is 'Radio'
        'is_gantt_parent_group', // Type is 'Radio'
        'is_gantt_main_group', // Type is 'Radio'
        'is_gantt_name', // Type is 'Radio'
        'is_gantt_parent', // Type is 'Radio'
        'is_gantt_start', // Type is 'Radio'
        'is_gantt_end', // Type is 'Radio'
        'is_gantt_progress', // Type is 'Radio'
        'is_gantt_color', // Type is 'Radio'
        'is_gantt_tooltip',
        'is_gantt_left_header',
        'is_gantt_label_symbol', // Type is 'Radio'
        'is_gantt_milestone', // Type is 'Radio'

        //Calendar
        'is_calendar_start', // Type is 'Radio'
        'is_calendar_end', // Type is 'Radio'
        'is_calendar_title', // Type is 'Radio'
        'is_calendar_cond_format', // Type is 'Radio'

        //shared with UserHeaders
        'filter',
        'filter_type', // ['value', 'range']
        'popup_header',
        'is_floating',
        'unit_display',
        'min_width',
        'max_width',
        'width',
        'notes',
        'col_align',
        'show_zeros',
        //hidden
        'is_showed',
        'order',
        //shared with UserHeaders ^^^^^

        'created_by',
        'created_on',
        'modified_by',
        'modified_on'
    ];

    public static $radioFields = [
        'is_image_on_board',
        'is_lat_field',
        'is_long_field',
        'is_info_header_field',
        'map_find_street_field',
        'map_find_city_field',
        'map_find_state_field',
        'map_find_county_field',
        'map_find_zip_field',
        'is_gantt_group',
        'is_gantt_parent_group',
        'is_gantt_main_group',
        'is_gantt_name',
        'is_gantt_parent',
        'is_gantt_start',
        'is_gantt_end',
        'is_gantt_progress',
        'is_gantt_color',
        'is_gantt_label_symbol',
        'is_gantt_milestone',
    ];


    public function scopeHasAutoFillDdl($q)
    {
        return $q->whereNotNull('ddl_id')
            ->where('ddl_auto_fill', 1);
    }


    /**
     * @param $q
     * @param int|null $user_id
     * @param Table|null $table
     * @return mixed
     */
    public function scopeJoinHeader($q, int $user_id = null, Table $table = null)
    {
        $user_id = $table && $table->user_id != $user_id
            ? $user_id
            : null;

        if ($user_id && $table)
        { // NOT OWNER
            return $this->joinNotOwnerSettings($q, $table, $user_id);
        }
        else
        { // OWNER
            $select_fields = [
                'table_fields.*',
                'table_fields.id as user_header_id'
            ];
            return $q->orderBy('table_fields.order')
                ->select($select_fields);
        }
    }

    /**
     * @param $q
     * @param Table $table
     * @param int $user_id
     * @return mixed
     */
    private function joinNotOwnerSettings($q, Table $table, int $user_id)
    {
        //load current user's right
        if (!$table->_current_right) {
            (new TableRepository())->loadCurrentRight($table, $user_id);
        }

        //forbidden settings columns
        $forbidden_col_settings = $table->_current_right->forbidden_col_settings ?? collect([]);
        $forbidden_col_settings = $forbidden_col_settings->toArray() ?? [];

        //select fields
        $can_order = $user_id && $table->_current_right->can_drag_columns;
        $select_fields = ['table_fields.*'];

        $uh_fields = (new UserHeaders())->avail_override_fields;
        $uh_fields[] = 'is_showed';
        ($can_order ? $uh_fields[] = 'order' : null);

        foreach ($uh_fields as $fld) {
            if (!in_array($fld, $forbidden_col_settings)) {
                $select_fields[] = 'user_headers.' . $fld . ' as ' . $fld; // 'user_headers.col as col'
            }
        }
        $select_fields[] = 'user_headers.id as user_header_id';

        //chose 'order by'
        $order_by = $can_order ? 'user_headers.order' : 'table_fields.order';

        //result
        return $q->leftJoin('user_headers', function ($header_q) use ($user_id) {
                $header_q->whereRaw('user_headers.table_field_id = table_fields.id');
                $header_q->where('user_headers.user_id', '=', $user_id);
            })
            ->orderBy($order_by)
            ->select($select_fields);
    }

    /**
     * Alias
     *
     * @param $q
     * @return mixed
     */
    public function scopeJoinOwnerHeader($q)
    {
        return $q->joinHeader();
    }


    public function _table()
    {
        return $this->belongsTo(Table::class, 'table_id', 'id');
    }

    public function _user_headers()
    {
        return $this->hasMany(UserHeaders::class, 'table_field_id', 'id');
    }

    public function _links()
    {
        return $this->hasMany(TableFieldLink::class, 'table_field_id', 'id');
    }

    public function _unit_ddl()
    {
        return $this->hasOne(DDL::class, 'id', 'unit_ddl_id');
    }

    public function _ddl()
    {
        return $this->hasOne(DDL::class, 'id', 'ddl_id');
    }

    public function _ref_condition()
    {
        return $this->hasOne(TableRefCondition::class, 'id', 'table_ref_condition_id');
    }

    public function _listing_field()
    {
        return $this->hasOne(TableField::class, 'id', 'listing_field_id');
    }

    public function _map_icons()
    {
        return $this->hasMany(TableMapIcon::class, 'table_field_id', 'id');
    }

    public function _files()
    {
        return $this->hasMany(File::class, 'table_field_id', 'id');
    }

    public function _table_column_groups()
    {
        return $this->belongsToMany(TableColumnGroup::class, 'table_column_groups_2_table_fields', 'table_field_id', 'table_column_group_id');
    }

    public function _table_row_group_conditions()
    {
        return $this->hasMany(TableRowGroupCondition::class, 'table_field_id', 'id');
    }

    public function _kanban_setting()
    {
        return $this->hasOne(TableKanbanSettings::class, 'table_field_id', 'id');
    }


    public function _created_user()
    {
        return $this->belongsTo(User::class, 'created_by', 'id');
    }

    public function _modified_user()
    {
        return $this->belongsTo(User::class, 'modified_by', 'id');
    }
}

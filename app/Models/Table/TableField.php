<?php

namespace Vanguard\Models\Table;

use Illuminate\Database\Eloquent\Model;
use Vanguard\Models\DataSetPermissions\TableColumnGroup;
use Vanguard\Models\DataSetPermissions\TableRefCondition;
use Vanguard\Models\DDL;
use Vanguard\Models\File;
use Vanguard\Repositories\Tablda\TableRepository;
use Vanguard\Services\Tablda\HelperService;
use Vanguard\User;

/**
 * Vanguard\Models\Table\TableField
 *
 * @property int $id
 * @property int $table_id
 * @property string $field
 * @property string $name
 * @property string|null $unit
 * @property int|null $unit_ddl_id
 * @property int $header_unit_ddl
 * @property string $input_type
 * @property int|null $ddl_id
 * @property int $ddl_add_option
 * @property int $ddl_auto_fill
 * @property string|null $tooltip
 * @property int $show_history
 * @property string $f_type
 * @property string $f_size
 * @property string|null $f_default
 * @property int $f_required
 * @property int|null $created_by
 * @property string $created_on
 * @property int|null $modified_by
 * @property string $modified_on
 * @property string|null $row_hash
 * @property string|null $formula
 * @property string|null $formula_symbol
 * @property int|null $is_lat_field
 * @property int|null $is_long_field
 * @property int|null $map_find_street_field
 * @property int|null $info_box
 * @property int|null $is_info_header_field
 * @property int $active_links
 * @property int $tooltip_show
 * @property string|null $header_background
 * @property string $ddl_style
 * @property int|null $mirror_rc_id
 * @property int|null $mirror_field_id
 * @property string $mirror_part
 * @property string|null $default_stats
 * @property int|null $map_find_city_field
 * @property int|null $map_find_state_field
 * @property int|null $map_find_county_field
 * @property int|null $map_find_zip_field
 * @property string|null $unit_display
 * @property string $filter_type
 * @property string $col_align
 * @property string|null $notes
 * @property int $width
 * @property int $min_width
 * @property int $max_width
 * @property int $order
 * @property int $is_showed
 * @property int $filter
 * @property int $popup_header
 * @property int $is_floating
 * @property int $show_zeros
 * @property int $is_show_on_board
 * @property int|null $is_image_on_board
 * @property int $is_search_autocomplete_display
 * @property int $is_unique_collection
 * @property int $is_table_field_in_popup
 * @property int $is_start_table_popup
 * @property int $is_topbot_in_popup
 * @property int $fld_display_name
 * @property int $fld_display_value
 * @property int $fld_display_border
 * @property string $image_display_view
 * @property string $image_display_fit
 * @property int $is_default_show_in_popup
 * @property string|null $placeholder_content
 * @property int $placeholder_only_form
 * @property int $is_uniform_formula
 * @property string|null $f_format
 * @property string|null $f_formula
 * @property int|null $fetch_source_id
 * @property int $kanban_group
 * @property int|null $is_gantt_group
 * @property int|null $is_gantt_parent_group
 * @property int|null $is_gantt_name
 * @property int|null $is_gantt_parent
 * @property int|null $is_gantt_start
 * @property int|null $is_gantt_end
 * @property int|null $is_gantt_progress
 * @property int|null $is_gantt_color
 * @property int|null $is_gantt_tooltip
 * @property int|null $is_gantt_left_header
 * @property int|null $is_gantt_label_symbol
 * @property int|null $is_gantt_milestone
 * @property int|null $is_gantt_main_group
 * @property int $verttb_he_auto
 * @property int $verttb_row_height
 * @property int $verttb_cell_height
 * @property int $is_dcr_section
 * @property int|null $is_calendar_start
 * @property int|null $is_calendar_end
 * @property int|null $is_calendar_title
 * @property int|null $is_calendar_cond_format
 * @property string|null $kanban_field_name
 * @property string|null $rating_icon
 * @property-read \Vanguard\User|null $_created_user
 * @property-read \Vanguard\Models\DDL|null $_ddl
 * @property-read \Illuminate\Database\Eloquent\Collection|\Vanguard\Models\File[] $_files
 * @property-read int|null $_files_count
 * @property-read \Vanguard\Models\Table\TableKanbanSettings|null $_kanban_setting
 * @property-read \Illuminate\Database\Eloquent\Collection|\Vanguard\Models\Table\TableFieldLink[] $_links
 * @property-read int|null $_links_count
 * @property-read \Vanguard\Models\Table\TableField|null $_listing_field
 * @property-read \Illuminate\Database\Eloquent\Collection|\Vanguard\Models\Table\TableMapIcon[] $_map_icons
 * @property-read int|null $_map_icons_count
 * @property-read \Vanguard\User|null $_modified_user
 * @property-read \Vanguard\Models\DataSetPermissions\TableRefCondition|null $_ref_condition
 * @property-read \Vanguard\Models\Table\Table $_table
 * @property-read \Illuminate\Database\Eloquent\Collection|\Vanguard\Models\DataSetPermissions\TableColumnGroup[] $_table_column_groups
 * @property-read int|null $_table_column_groups_count
 * @property-read \Vanguard\Models\DDL|null $_unit_ddl
 * @property-read \Illuminate\Database\Eloquent\Collection|\Vanguard\Models\Table\UserHeaders[] $_user_headers
 * @property-read int|null $_user_headers_count
 * @mixin \Eloquent
 * @property-read \Vanguard\Models\Table\TableField|null $_mirror_field
 * @property-read \Vanguard\Models\DataSetPermissions\TableRefCondition|null $_mirror_rc
 * @method static \Illuminate\Database\Eloquent\Builder|\Vanguard\Models\Table\TableField hasAutoFillDdl()
 * @method static \Illuminate\Database\Eloquent\Builder|\Vanguard\Models\Table\TableField joinHeader($user_id = null, \Vanguard\Models\Table\Table $table = null)
 * @method static \Illuminate\Database\Eloquent\Builder|\Vanguard\Models\Table\TableField joinOwnerHeader()
 */
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
        'mirror_rc_id',
        'mirror_field_id',
        'mirror_part', // ['id','value','show']
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
        'fetch_source_id',
        'rating_icon',
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
        'kanban_field_name',
        'is_show_on_board',
        'is_image_on_board',
        'is_search_autocomplete_display',
        'is_unique_collection',
        'is_default_show_in_popup',
        'is_table_field_in_popup',
        'is_start_table_popup',
        'is_topbot_in_popup',
        'is_dcr_section',
        'fld_display_name',
        'fld_display_value',
        'fld_display_border',
        'image_display_view',
        'image_display_fit',

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
        'modified_on',
        'row_hash',
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
        'is_calendar_start',
        'is_calendar_end',
        'is_calendar_title',
        'is_calendar_cond_format',
    ];


    public function scopeHasAutoFillDdl($q)
    {
        return $q->whereNotNull('ddl_id')
            ->where('ddl_auto_fill', 1);
    }


    public function scopeNotSystemFields($q)
    {
        return $q->whereNotIn('field', (new HelperService())->system_fields);
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
     * @return mixed
     */
    public function alphaName()
    {
        return preg_replace('/[^\w\d]/i', '_', $this->name);
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

    public function _listing_field()
    {
        return $this->hasOne(TableField::class, 'id', 'listing_field_id');
    }

    public function _fetch_field()
    {
        return $this->hasOne(TableField::class, 'id', 'fetch_source_id');
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

    public function _kanban_setting()
    {
        return $this->hasOne(TableKanbanSettings::class, 'table_field_id', 'id');
    }

    public function _mirror_rc()
    {
        return $this->hasOne(TableRefCondition::class, 'id', 'mirror_rc_id');
    }

    public function _mirror_field()
    {
        return $this->hasOne(TableField::class, 'id', 'mirror_field_id');
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

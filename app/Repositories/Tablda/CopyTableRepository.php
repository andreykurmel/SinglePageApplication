<?php

namespace Vanguard\Repositories\Tablda;


use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use Ramsey\Uuid\Uuid;
use Vanguard\Models\DataSetPermissions\CondFormat;
use Vanguard\Models\DataSetPermissions\TableColumnGroup;
use Vanguard\Models\DataSetPermissions\TablePermission;
use Vanguard\Models\DataSetPermissions\TablePermissionColumn;
use Vanguard\Models\DataSetPermissions\TablePermissionRow;
use Vanguard\Models\DataSetPermissions\TableRefCondition;
use Vanguard\Models\DataSetPermissions\TableRefConditionItem;
use Vanguard\Models\DataSetPermissions\TableRowGroup;
use Vanguard\Models\DataSetPermissions\TableRowGroupRegular;
use Vanguard\Models\Dcr\DcrLinkedTable;
use Vanguard\Models\Dcr\TableDataRequest;
use Vanguard\Models\Dcr\TableDataRequest2Fields;
use Vanguard\Models\Dcr\TableDataRequestColumn;
use Vanguard\Models\Dcr\TableDataRequestDefaultField;
use Vanguard\Models\DDL;
use Vanguard\Models\DDLItem;
use Vanguard\Models\DDLReference;
use Vanguard\Models\DDLReferenceColor;
use Vanguard\Models\Table\AlertAnrTable;
use Vanguard\Models\Table\AlertAnrTableField;
use Vanguard\Models\Table\AlertUfvTable;
use Vanguard\Models\Table\AlertUfvTableField;
use Vanguard\Models\Table\Table;
use Vanguard\Models\Table\TableAi;
use Vanguard\Models\Table\TableAlert;
use Vanguard\Models\Table\TableAlertCondition;
use Vanguard\Models\Table\TableAlertSnapshotField;
use Vanguard\Models\Table\TableCalendar;
use Vanguard\Models\Table\TableChart;
use Vanguard\Models\Table\TableChartTab;
use Vanguard\Models\Table\TableData;
use Vanguard\Models\Table\TableEmailAddonSetting;
use Vanguard\Models\Table\TableField;
use Vanguard\Models\Table\TableFieldLink;
use Vanguard\Models\Table\TableGantt;
use Vanguard\Models\Table\TableGanttSetting;
use Vanguard\Models\Table\TableGrouping;
use Vanguard\Models\Table\TableKanbanGroupParam;
use Vanguard\Models\Table\TableKanbanSettings;
use Vanguard\Models\Table\TableKanbanSettings2Fields;
use Vanguard\Models\Table\TableMap;
use Vanguard\Models\Table\TableMapFieldSetting;
use Vanguard\Models\Table\TableMapIcon;
use Vanguard\Models\Table\TableReport;
use Vanguard\Models\Table\TableReportSource;
use Vanguard\Models\Table\TableReportSourceVariable;
use Vanguard\Models\Table\TableReportTemplate;
use Vanguard\Models\Table\TableSimplemap;
use Vanguard\Models\Table\TableTournament;
use Vanguard\Models\Table\TableTwilioAddonSetting;
use Vanguard\Models\Table\TableView;
use Vanguard\Models\Table\UserHeaders;
use Vanguard\Modules\QRGenerator;
use Vanguard\Repositories\Tablda\Permissions\TableRefConditionRepository;
use Vanguard\Services\Tablda\HelperService;
use Vanguard\User;

class CopyTableRepository
{
    protected $service;
    protected $fieldRepository;

    public $copied_table_compares = [
        /* 'old_table_id' => 'new_table_id' */
    ];

    /**
     * CopyTableRepository constructor.
     */
    public function __construct()
    {
        $this->service = new HelperService();
        $this->fieldRepository = new TableFieldRepository();
    }

    /**
     * Copy TableField Settings.
     *
     * @param Table $old_table
     * @param Table $new_table
     * @param array $limitedIds
     * @return void
     */
    public function copyBasics(Table $old_table, Table $new_table, array $limitedIds = []) {
        $old_table->load([
            '_views',
            '_fields' => function($q) {
                $q->with('_unit_ddl','_ddl');
            },
            '_table_permissions' => function($q) {
                $q->with('_permission_columns','_permission_rows');
            },
            '_column_groups',
            '_row_groups',
        ]);

        $new_table->load(['_views', '_fields', '_table_permissions', '_column_groups', '_row_groups', '_ddls']);

        //copy TableField Settings
        foreach ($old_table->_fields as $field) {

            if ($limitedIds && !in_array($field->id, $limitedIds)) {
                continue;
            }

            $new_field = $new_table->_fields->where('field', '=', $field->field)->first();

            $has_unit_ddl = ($field->_unit_ddl
                ? $new_table->_ddls->where('name', '=', $field->_unit_ddl->name)->first()
                : false);
            $has_ddl = ($field->_ddl
                ? $new_table->_ddls->where('name', '=', $field->_ddl->name)->first()
                : false);

            $fl = array_merge($field->toArray(), [
                'table_id' => $new_table->id,
                'unit_ddl_id' => $has_unit_ddl ? $has_unit_ddl->id : null,
                'ddl_id' => $has_ddl ? $has_ddl->id : null,
            ]);
            TableField::updateOrCreate(
                ['id' => $new_field ? $new_field->id : null],
                $this->service->delSystemFields($fl)
            );
        }

        //Copy Permissions
        foreach ($old_table->_table_permissions as $permission) {
            if (! $permission->is_system) {
                $data = $permission->getAttributes();
                $data['table_id'] = $new_table->id;
                $data = $this->service->delSystemFields($data);
                $new_id = TablePermission::insertGetId($data);

                foreach ($permission->_permission_rows as $permission_row) {
                    $data = $permission_row->getAttributes();
                    $data['table_permission_id'] = $permission->id;
                    $data['table_row_group_id'] = $this->mapIdRelation($old_table->_row_groups, $new_table->_row_groups, $data['table_row_group_id']);
                    $data = $this->service->delSystemFields($data);
                    TablePermissionRow::insertGetId($data);
                }

                foreach ($permission->_permission_columns as $permission_column) {
                    $data = $permission_column->getAttributes();
                    $data['table_permission_id'] = $permission->id;
                    $data['table_column_group_id'] = $this->mapIdRelation($old_table->_column_groups, $new_table->_column_groups, $data['table_column_group_id']);
                    $data = $this->service->delSystemFields($data);
                    TablePermissionColumn::insertGetId($data);
                }
            }
        }

        $new_table->load(['_table_permissions']);

        //Copy Table Views
        foreach ($old_table->_views as $view) {
            if (! $view->is_system) {
                $data = $view->getAttributes();
                $data['table_id'] = $new_table->id;
                $data['user_id'] = $new_table->user_id;
                $data['access_permission_id'] = $this->mapIdRelation($old_table->_table_permissions, $new_table->_table_permissions, $data['access_permission_id']);
                $data['col_group_id'] = $this->mapIdRelation($old_table->_column_groups, $new_table->_column_groups, $data['col_group_id']);
                $data['row_group_id'] = $this->mapIdRelation($old_table->_row_groups, $new_table->_row_groups, $data['row_group_id']);
                $data = $this->service->delSystemFields($data);
                $new_id = TableView::insertGetId($data);
            }
        }

        $new_table->load(['_views']);

        //Update Table Params
        $params = [];
        $params['initial_view_id'] = $this->mapIdRelation($old_table->_views, $new_table->_views, $old_table->initial_view_id) ?: $old_table->initial_view_id;
        $params['board_image_fld_id'] = $this->mapIdRelation($old_table->_fields, $new_table->_fields, $old_table->board_image_fld_id);
        $params['listing_fld_id'] = $this->mapIdRelation($old_table->_fields, $new_table->_fields, $old_table->listing_fld_id);
        $params['multi_link_app_fld_id'] = $this->mapIdRelation($old_table->_fields, $new_table->_fields, $old_table->multi_link_app_fld_id);
        $params['single_view_status_id'] = $this->mapIdRelation($old_table->_fields, $new_table->_fields, $old_table->single_view_status_id);
        $params['single_view_url_id'] = $this->mapIdRelation($old_table->_fields, $new_table->_fields, $old_table->single_view_url_id);
        $params['single_view_password_id'] = $this->mapIdRelation($old_table->_fields, $new_table->_fields, $old_table->single_view_password_id);
        $params['single_view_permission_id'] = $this->mapIdRelation($old_table->_table_permissions, $new_table->_table_permissions, $old_table->single_view_permission_id);

        $new_table->update($this->service->delSystemFields($params));
    }

    /**
     * Copy Row Groups.
     *
     * @param Table $old_table
     * @param Table $new_table
     * @param array $selected_ids
     */
    public function copyRowGroups(Table $old_table, Table $new_table, array $selected_ids = []) {
        $old_table->load([
            '_row_groups' => function ($q) {
                $q->with('_regulars', '_ref_condition', '_preview_col');
            },
        ]);
        $new_table->load(['_ref_conditions', '_column_groups']);

        //copy row groups
        foreach ($old_table->_row_groups as $row_group) {
            if ($row_group->is_system) {
                continue;
            }
            if (count($selected_ids) && !in_array($row_group->id, $selected_ids)) {
                continue;
            }
            $has_ref_cond = ($row_group->_ref_condition
                ? $new_table->_ref_conditions->where('name', '=', $row_group->_ref_condition->name)->first()
                : false);

            $has_preview_col = $row_group->_preview_col
                ? $new_table->_column_groups->where('name', '=', $row_group->_preview_col->name)->first()
                : false;

            $fl = array_merge($row_group->toArray(), [
                'table_id' => $new_table->id,
                'row_ref_condition_id' => $has_ref_cond ? $has_ref_cond->id : null,
                'preview_col_id' => $has_preview_col ? $has_preview_col->id : null,
            ]);
            $new_elem = TableRowGroup::create(
                array_merge($this->service->delSystemFields($fl), $this->service->getModified(), $this->service->getCreated())
            );

            $data = [];
            //copy row group regulars
            foreach ($row_group->_regulars as $item) {
                $fl = array_merge($item->toArray(), ['table_row_group_id' => $new_elem->id]);
                $data[] = array_merge($this->service->delSystemFields($fl), $this->service->getModified(), $this->service->getCreated());
            }
            (count($data) ? TableRowGroupRegular::insert($data) : '');
        }
    }

    /**
     * Copy Column Groups.
     *
     * @param Table $old_table
     * @param Table $new_table
     */
    public function copyColumnGroups(Table $old_table, Table $new_table) {
        $old_table->load([
            '_column_groups' => function ($q) {
                $q->with('_fields');
            }
        ]);
        $new_table->load('_fields', '_column_groups');

        $data = [];
        //copy Column groups
        foreach ($old_table->_column_groups as $column_group) {
            if ($column_group->is_system) {
                continue;
            }

            $new_elem = $new_table->_column_groups->where('name', '=', $column_group->name)->first();

            if (!$new_elem) {
                $fl = array_merge($column_group->toArray(), ['table_id' => $new_table->id, 'is_system' => 0]);
                $new_elem = TableColumnGroup::create(
                    array_merge($this->service->delSystemFields($fl), $this->service->getModified(), $this->service->getCreated())
                );
            }

            $data = [];
            //copy Column group Fields
            foreach ($column_group->_fields as $field) {
                $new_field = $new_table->_fields->where('field', '=', $field->field)->first();
                if ($new_field && !$new_elem->_fields->where('field', '=', $field->field)->count()) {
                    $new_elem->_fields()->attach($new_field->id);
                }
            }
        }
    }

    /**
     * Copy Reference Conditions (OR selected RC)
     *
     * @param Table $old_table
     * @param Table $new_table
     * @param User $new_user
     * @param array $selected_ids
     */
    public function copyReferenceConditions(Table $old_table, Table $new_table, User $new_user, array $selected_ids = []) {
        $old_table->load([
            '_ref_conditions' => function ($q) {
                $q->with( (new TableRefConditionRepository())->refConditionRelations() );
            }
        ]);
        $new_table->load('_fields');
        $newrc_present = $new_table->_ref_conditions()->with('_items')->get();

        //RC to the self
        $this->copied_table_compares[$old_table->id] = $new_table->id;

        //copy Reference Conditions IF RefTable is available for new User
        foreach ($old_table->_ref_conditions as $ref_condition) {
            if (count($selected_ids) && !in_array($ref_condition->id, $selected_ids)) {
                continue;//Copy only Selected if present
            }
            if ($this->rcIsPresent($ref_condition, $newrc_present, true)) {
                continue;//Don't copy already presents
            }
            if (
                $new_user->can('get', [TableData::class, $ref_condition->_ref_table]) //User can get old table
                ||
                !empty($this->copied_table_compares[ $ref_condition->ref_table_id ]) //or Ref Table is copied for new User
            ) {
                $copied_table_id = $this->copied_table_compares[ $ref_condition->ref_table_id ] ?? false;

                $fl = array_merge($ref_condition->toArray(), [
                    'table_id' => $new_table->id,
                    'name' => $this->getRCname($ref_condition, $newrc_present),
                    'ref_table_id' => $copied_table_id ?: $ref_condition->ref_table_id,
                ]);
                $new_elem = TableRefCondition::create(
                    array_merge($this->service->delSystemFields($fl), $this->service->getModified(), $this->service->getCreated())
                );
            }
        }

        $new_table->load('_ref_conditions');

        //Update base ref conditions
        foreach ($old_table->_ref_conditions as $ref_condition) {
            if ($ref_condition->_base_refcond) {
                $related = $new_table->_ref_conditions->where('name', '=', $ref_condition->name)->first();
                $base = $new_table->_ref_conditions->where('name', '=', $ref_condition->_base_refcond->name)->first();
                if ($related && $base) {
                    $related->update([
                        'base_refcond_id' => $base->id,
                    ]);
                }
            }
        }

        //copy Reference Condition Items
        foreach ($old_table->_ref_conditions as $ref_condition) {
            if (count($selected_ids) && !in_array($ref_condition->id, $selected_ids)) {
                continue;
            }
            if (
                $new_user->can('get', [TableData::class, $ref_condition->_ref_table]) //User can get old table
                ||
                !empty($this->copied_table_compares[ $ref_condition->ref_table_id ]) //or Ref Table is copied for new User
            ) {
                $new_elem = $this->findRC($ref_condition->name ?? '', $new_table->_ref_conditions);

                $data = [];
                foreach ($ref_condition->_items as $item) {
                    $new_field = $new_table->_fields->where('field', '=', $item->_field->field ?? '')->first();
                    $compared_field = $this->getListingField($new_table, $new_elem, $item->_compared_field);

                    $fl = array_merge($item->toArray(), [
                        'table_ref_condition_id' => $new_elem->id,
                        'table_field_id' => $new_field->id ?? null,
                        'compared_field_id' => $compared_field->id ?? null,
                    ]);
                    $data[] = array_merge($this->service->delSystemFields($fl), $this->service->getModified(), $this->service->getCreated());
                }
                (count($data) ? TableRefConditionItem::insert($data) : '');
            }
        }
    }

    /**
     * @param TableRefCondition $refcond
     * @param Collection $newrc_present
     * @param bool $deep
     * @return bool
     */
    protected function rcIsPresent(TableRefCondition $refcond, Collection $newrc_present, bool $deep = false) : bool
    {
        $present = $newrc_present->where('name', '=', $refcond->name)->first();
        if (!$present) {
            return false;
        }

        $deep_present = true;
        if ($deep) {
            $deep_present = ($present->_items->count() == $refcond->_items->count());
            foreach ($present->_items as $idx => $item) {
                $deep_present = $deep_present
                    && $refcond->_items[$idx]
                    && $refcond->_items[$idx]->group_clause == $item->group_clause
                    && $refcond->_items[$idx]->item_type == $item->item_type
                    && $refcond->_items[$idx]->compared_operator == $item->compared_operator
                    && $refcond->_items[$idx]->compared_value == $item->compared_value;
            }
        }
        return $deep_present;
    }

    /**
     * @param TableRefCondition $refcond
     * @param Collection $presents
     * @return string
     */
    protected function getRCname(TableRefCondition $refcond, Collection $presents) : string
    {
        if ($this->rcIsPresent($refcond, $presents)) {
            $i = 1;
            while ($presents->where('name', '=', $refcond->name.'_'.$i)->count()) {
                $i++;
            }
            return $refcond->name.'_'.$i;
        } else {
            return $refcond->name;
        }
    }

    /**
     * Find by name (accessible 'Name', 'Name_1', ...)
     *
     * @param string $name
     * @param Collection $ref_conds
     * @return TableRefCondition
     */
    protected function findRC(string $name, Collection $ref_conds) : TableRefCondition
    {
        $rc = $ref_conds->reverse()->filter(function ($rc) use ($name) {
            return ($rc->name == $name) || preg_match('/^'.$name.'_[\d]+$/i', $rc->name);
        })->first();

        return $rc ?: $ref_conds->reverse()->filter(function ($rc) use ($name) {
            return ($rc->name == $name) || preg_match('/^'.$name.'_[\d]+$/i', $rc->name);
        })->first();
    }

    /**
     * Copy Links
     *
     * @param Table $old_table
     * @param Table $new_table
     */
    public function copyLinks(Table $old_table, Table $new_table) {
        $old_table->load([
            '_fields' => function ($q) {
                $q->with([
                    '_links' => function ($i) {
                        $i->with([
                            '_ref_condition',
                            '_listing_field',
                            '_address_field',
                            '_map_field_lat',
                            '_map_field_lng',
                            '_map_field_address',
                        ]);
                    }
                ]);
            }
        ]);
        $new_table->load(['_fields', '_ref_conditions']);

        //copy Links IF available Reference Condition
        foreach ($old_table->_fields as $field) {
            $data = [];
            foreach ($field->_links as $link) {

                $new_field = $new_table->_fields->where('field', '=', $field->field)->first();
                $has_ref_cond = ($link->_ref_condition
                    ? $new_table->_ref_conditions->where('name', '=', $link->_ref_condition->name)->first()
                    : false);

                $has_listing_field = $this->getListingField($new_table, $has_ref_cond, $link->_listing_field);

                $has_address_field = ($link->_address_field
                    ? $new_table->_fields->where('field', '=', $link->_address_field->field)->first()
                    : false);
                $has_map_field_lat = ($link->_map_field_lat
                    ? $new_table->_fields->where('field', '=', $link->_map_field_lat->field)->first()
                    : false);
                $has_map_field_lng = ($link->_map_field_lng
                    ? $new_table->_fields->where('field', '=', $link->_map_field_lng->field)->first()
                    : false);
                $has_map_field_address = ($link->_map_field_address
                    ? $new_table->_fields->where('field', '=', $link->_map_field_address->field)->first()
                    : false);

                if ($new_field) {
                    $fl = array_merge($link->toArray(), [
                        'table_field_id' => $new_field->id,
                        'table_ref_condition_id' => $has_ref_cond ? $has_ref_cond->id : null,
                        'listing_field_id' => $has_listing_field ? $has_listing_field->id : null,
                        'address_field_id' => $has_address_field ? $has_address_field->id : null,
                        'link_field_lat' => $has_map_field_lat ? $has_map_field_lat->id : null,
                        'link_field_lng' => $has_map_field_lng ? $has_map_field_lng->id : null,
                        'link_field_address' => $has_map_field_address ? $has_map_field_address->id : null,
                    ]);
                    $data[] = array_merge($this->service->delSystemFields($fl), $this->service->getModified(), $this->service->getCreated());
                }
            }
            (count($data) ? TableFieldLink::insert($data) : '');
        }
    }

    /**
     * @param Table $old_table
     * @param Table $new_table
     * @param int|null $new_user_id
     */
    public function copyCondFormats(Table $old_table, Table $new_table, int $new_user_id = null)
    {
        $old_table->load(['_cond_formats' => function($q) {
            $q->with(['_row_group', '_column_group']);
        }]);
        $new_table->load(['_row_groups', '_column_groups']);
        //copy Cond Formats
        foreach ($old_table->_cond_formats as $cf) {
            $new_row = $new_table->_row_groups->where('name', '=', $cf->_row_group->name ?? '')->first();
            $new_column = $new_table->_column_groups->where('name', '=', $cf->_column_group->name ?? '')->first();

            $data = [];
            $fl = array_merge($cf->toArray(), [
                'table_id' => $new_table->id,
                'user_id' => $new_user_id,
                'table_column_group_id' => $new_column ? $new_column->id : null,
                'table_row_group_id' => $new_row ? $new_row->id : null,
            ]);
            $data[] = array_merge($this->service->delSystemFields($fl), $this->service->getModified(), $this->service->getCreated());
            (count($data) ? CondFormat::insert($data) : '');
        }
    }

    /**
     * Copy Charts
     *
     * @param Table $old_table
     * @param Table $new_table
     */
    public function copyAddonBi(Table $old_table, Table $new_table) {
        $old_table->load([
            '_bi_charts',
            '_chart_tabs',
        ]);

        //copy Chart Tabs
        foreach ($old_table->_chart_tabs as $tab) {
            $fl = array_merge($tab->toArray(), [
                'table_id' => $new_table->id,
            ]);
            $data = array_merge($this->service->delSystemFields($fl));
            TableChartTab::insert($data);
        }

        $new_table->load(['_chart_tabs']);

        //copy Charts
        foreach ($old_table->_bi_charts as $chart) {
            $newTab = $this->mapIdRelation($old_table->_chart_tabs, $new_table->_chart_tabs, $chart['table_chart_tab_id'], true);
            $fl = array_merge($chart->toArray(), [
                'table_id' => $new_table->id,
                'user_id' => $new_table->user_id,
                'table_chart_tab_id' => $newTab ? $newTab->id : null,
            ]);
            $data = array_merge($this->service->delSystemFields($fl));
            $new_id = TableChart::insertGetId($data);

            $data['id'] = $new_id;
            $data['chart_settings'] = json_decode($data['chart_settings'], true);
            $data['chart_settings']['id'] = $new_id;
            $data['chart_settings'] = json_encode($data['chart_settings']);
            TableChart::where('id', $new_id)->update($data);
        }
    }

    /**
     * Copy Map icons
     *
     * @param Table $old_table
     * @param Table $new_table
     */
    public function copyAddonGsi(Table $old_table, Table $new_table) {
        $old_table->load([
            '_fields' => function ($q) {
                $q->with('_map_icons');
            },
            '_map_addons' => function ($q) {
                $q->with('_map_field_settings');
            },
        ]);
        $new_table->load(['_fields']);

        //copy Map Settings
        foreach ($old_table->_map_addons as $map) {
            $data = $map->getAttributes();
            foreach ($map->getFillable() as $key) {
                if (Str::contains($key, 'field_id')) {
                    $data[$key] = $this->mapIdRelation($old_table->_fields, $new_table->_fields, $map[$key]) ?: $map[$key];
                }
                if (Str::contains($key, 'refid') || Str::contains($key, 'hdr_id')) {
                    $data[$key] = $this->mapIdRelation($old_table->_ref_conditions, $new_table->_ref_conditions, $map[$key]) ?: $map[$key];
                }
            }
            $data['table_id'] = $new_table->id;
            $data = $this->service->delSystemFields($data);
            $new_id = TableMap::insertGetId($data);

            //copy Fields Settings
            foreach ($map->_map_field_settings as $field_setting) {
                $data = $field_setting->getAttributes();
                $data['table_map_id'] = $new_id;
                $newFld = $this->mapIdRelation($old_table->_fields, $new_table->_fields, $field_setting['table_field_id'], true);
                if ($newFld) {
                    $data['table_field_id'] = $newFld->id;
                    $data = $this->service->delSystemFields($data);
                    TableMapFieldSetting::insert($data);
                }
            }
        }

        //copy Map Icons
        foreach ($old_table->_fields as $field) {
            $data = [];
            foreach ($field->_map_icons as $icon) {
                $new_field = $new_table->_fields->where('field', '=', $field->field)->first();
                if ($new_field) {
                    $fl = array_merge($icon->toArray(), [
                        'table_field_id' => $new_field->id,
                        'icon_path' => preg_replace('/'.$old_table->id.'_/', $new_table->id.'_', $icon->icon_path),
                    ]);
                    $data[] = array_merge($this->service->delSystemFields($fl), $this->service->getModified(), $this->service->getCreated());
                }
            }
            (count($data) ? TableMapIcon::insert($data) : '');
        }
    }

    /**
     * Copy Data Requests
     *
     * @param Table $old_table
     * @param Table $new_table
     */
    public function copyAddonDcr(Table $old_table, Table $new_table) {
        $old_table->load([
            '_table_requests' => function ($q) {
                $q->with('_data_request_columns', '_default_fields', '_fields_pivot', '_dcr_linked_tables');
            },
            '_fields',
            '_column_groups',
            '_table_permissions',
        ]);
        $new_table->load(['_fields', '_column_groups', '_table_permissions']);

        //copy DCRs
        foreach ($old_table->_table_requests as $dcr) {
            $data = $dcr->getAttributes();
            foreach ($dcr->getFillable() as $key) {
                if (Str::endsWith($key, '_id')) {
                    $data[$key] = $this->mapIdRelation($old_table->_fields, $new_table->_fields, $dcr[$key]);
                }
            }
            $data['table_id'] = $new_table->id;
            $data['user_id'] = $new_table->user_id;
            $data['custom_url'] = null;
            $data['dcr_hash'] = Uuid::uuid4();
            $data['link_hash'] = Uuid::uuid4();
            $data['qr_link'] = (new QRGenerator())->forDCR($data['link_hash'], $dcr->dcr_qr_with_name ? $dcr->name : '')->asPNG();
            $data = $this->service->delSystemFields($data);
            $new_id = TableDataRequest::insertGetId($data);

            //copy Request Columns
            foreach ($dcr->_data_request_columns as $request_column) {
                $data = $request_column->getAttributes();
                $data['table_data_requests_id'] = $new_id;
                $newCol = $this->mapIdRelation($old_table->_column_groups, $new_table->_column_groups, $request_column['table_column_group_id'], true);
                if ($newCol) {
                    $data['table_column_group_id'] = $newCol->id;
                    $data = $this->service->delSystemFields($data);
                    TableDataRequestColumn::insert($data);
                }
            }

            //copy Pivot Field Settings
            foreach ($dcr->_fields_pivot as $field_pivot) {
                $data = $field_pivot->getAttributes();
                $data['table_data_requests_id'] = $new_id;
                $newFld = $this->mapIdRelation($old_table->_fields, $new_table->_fields, $field_pivot['table_field_id'], true);
                if ($newFld) {
                    $data['table_field_id'] = $newFld->id;
                    $data = $this->service->delSystemFields($data);
                    TableDataRequest2Fields::insert($data);
                }
            }

            //copy Default Fields
            foreach ($dcr->_default_fields as $default_field) {
                $data = $default_field->getAttributes();
                $data['table_data_requests_id'] = $new_id;
                $newFld = $this->mapIdRelation($old_table->_fields, $new_table->_fields, $default_field['table_field_id'], true);
                if ($newFld) {
                    $data['table_field_id'] = $newFld->id;
                    $data = $this->service->delSystemFields($data);
                    TableDataRequestDefaultField::insert($data);
                }
            }

            //copy DCR Linked Tables
            foreach ($dcr->_dcr_linked_tables as $embed) {
                $data = $embed->getAttributes();
                $data['table_request_id'] = $new_id;
                $data['linked_permission_id'] = $this->mapIdRelation($old_table->_table_permissions, $new_table->_table_permissions, $embed['linked_permission_id']);
                $data['position_field_id'] = $this->mapIdRelation($old_table->_fields, $new_table->_fields, $embed['position_field_id']);
                $data['passed_ref_cond_id'] = $this->mapIdRelation($old_table->_ref_conditions, $new_table->_ref_conditions, $embed['passed_ref_cond_id']);
                $data = $this->service->delSystemFields($data);
                DcrLinkedTable::insert($data);
            }
        }
    }

    /**
     * Copy Data Requests
     *
     * @param Table $old_table
     * @param Table $new_table
     */
    public function copyAddonAna(Table $old_table, Table $new_table) {
        $old_table->load([
            '_alerts' => function ($q) {
                $q->with(TableAlertsRepository::relations());
            },
            '_fields',
            '_column_groups',
            '_ref_conditions',
        ]);
        $new_table->load(['_fields', '_column_groups', '_ref_conditions']);

        //copy Alerts
        foreach ($old_table->_alerts as $alert) {
            $data = $alert->getAttributes();
            foreach ($alert->getFillable() as $key) {
                if (Str::contains($key, 'field_id')) {
                    $data[$key] = $this->mapIdRelation($old_table->_fields, $new_table->_fields, $alert[$key]) ?: $alert[$key];
                }
                if (Str::contains($key, 'ref_cond_id')) {
                    $data[$key] = $this->mapIdRelation($old_table->_ref_conditions, $new_table->_ref_conditions, $alert[$key]);
                }
                if (Str::contains($key, 'col_group_id')) {
                    $data[$key] = $this->mapIdRelation($old_table->_column_groups, $new_table->_column_groups, $alert[$key]);
                }
            }
            $data['table_id'] = $new_table->id;
            $data['user_id'] = $new_table->user_id;
            $data = $this->service->delSystemFields($data);
            $new_id = TableAlert::insertGetId($data);

            //copy Conditions
            foreach ($alert->_conditions as $condition) {
                $data = $condition->getAttributes();
                $data['table_alert_id'] = $new_id;
                $newFld = $this->mapIdRelation($old_table->_fields, $new_table->_fields, $condition['table_field_id'], true);
                if ($newFld) {
                    $data['table_field_id'] = $newFld->id;
                    $data = $this->service->delSystemFields($data);
                    TableAlertCondition::insert($data);
                }
            }

            //copy Snapshot Fields
            foreach ($alert->_snapshot_fields as $snapshot_field) {
                $data = $snapshot_field->getAttributes();
                $data['table_alert_id'] = $new_id;
                $newFld = $this->mapIdRelation($old_table->_fields, $new_table->_fields, $snapshot_field['table_field_id'], true);
                if ($newFld) {
                    $data['current_field_id'] = $newFld->id;
                    $data = $this->service->delSystemFields($data);
                    TableAlertSnapshotField::insert($data);
                }
            }

            //copy Ufv tables
            foreach ($alert->_ufv_tables as $ufv_table) {
                $data = $ufv_table->getAttributes();
                $data['table_alert_id'] = $new_id;
                $data['table_id'] = $new_table->id;
                $data = $this->service->delSystemFields($data);
                $ufvId = AlertUfvTable::insertGetId($data);

                //copy Ufv Fields
                foreach ($ufv_table->_ufv_fields as $ufv_field) {
                    $data = $ufv_field->getAttributes();
                    foreach ($ufv_field->getFillable() as $key) {
                        if (Str::contains($key, 'field_id')) {
                            $data[$key] = $this->mapIdRelation($old_table->_fields, $new_table->_fields, $ufv_field[$key]) ?: $ufv_field[$key];
                        }
                    }
                    $data['ufv_table_id'] = $ufvId;
                    $data = $this->service->delSystemFields($data);
                    AlertUfvTableField::insert($data);
                }
            }

            //copy Anr tables
            foreach ($alert->_anr_tables as $anr_table) {
                $data = $anr_table->getAttributes();
                $data['table_alert_id'] = $new_id;
                $data['table_id'] = $new_table->id;
                $data = $this->service->delSystemFields($data);
                $anrId = AlertAnrTable::insertGetId($data);

                //copy Anr Fields
                foreach ($anr_table->_anr_fields as $anr_field) {
                    $data = $anr_field->getAttributes();
                    foreach ($ufv_field->getFillable() as $key) {
                        if (Str::contains($key, 'field_id')) {
                            $data[$key] = $this->mapIdRelation($old_table->_fields, $new_table->_fields, $anr_field[$key]) ?: $anr_field[$key];
                        }
                    }
                    $data['anr_table_id'] = $anrId;
                    $data = $this->service->delSystemFields($data);
                    AlertAnrTableField::insert($data);
                }
            }
        }
    }

    /**
     * Copy Kanban
     *
     * @param Table $old_table
     * @param Table $new_table
     */
    public function copyAddonKanban(Table $old_table, Table $new_table) {
        $old_table->load([
            '_kanban_settings' => function ($q) {
                $q->with([
                    '_fields_pivot',
                    '_group_params',
                ]);
            },
            '_fields',
        ]);
        $new_table->load(['_fields']);

        //copy Kanban Settings
        foreach ($old_table->_kanban_settings as $kanban_setting) {
            $data = $kanban_setting->getAttributes();
            foreach ($kanban_setting->getFillable() as $key) {
                if (Str::contains($key, 'field_id')) {
                    $data[$key] = $this->mapIdRelation($old_table->_fields, $new_table->_fields, $kanban_setting[$key]) ?: $kanban_setting[$key];
                }
            }
            $data['table_id'] = $new_table->id;
            $data = $this->service->delSystemFields($data);
            $new_id = TableKanbanSettings::insertGetId($data);

            //copy Fields Pivot
            foreach ($kanban_setting->_fields_pivot as $field_pivot) {
                $data = $field_pivot->getAttributes();
                $data['table_kanban_setting_id'] = $new_id;
                $newFld = $this->mapIdRelation($old_table->_fields, $new_table->_fields, $field_pivot['table_field_id'], true);
                if ($newFld) {
                    $data['table_field_id'] = $newFld->id;
                    $data = $this->service->delSystemFields($data);
                    TableKanbanSettings2Fields::insert($data);
                }
            }

            //copy Group Params
            foreach ($kanban_setting->_group_params as $group_param) {
                $data = $group_param->getAttributes();
                $data['table_kanban_id'] = $new_id;
                $newFld = $this->mapIdRelation($old_table->_fields, $new_table->_fields, $group_param['table_field_id'], true);
                if ($newFld) {
                    $data['table_field_id'] = $newFld->id;
                    $data = $this->service->delSystemFields($data);
                    TableKanbanGroupParam::insert($data);
                }
            }
        }
    }

    /**
     * Copy Gantt
     *
     * @param Table $old_table
     * @param Table $new_table
     */
    public function copyAddonGantt(Table $old_table, Table $new_table) {
        $old_table->load([
            '_gantt_addons' => function ($q) {
                $q->with([
                    '_specifics',
                ]);
            },
            '_fields',
        ]);
        $new_table->load(['_fields']);

        //copy Gantt
        foreach ($old_table->_gantt_addons as $gantt) {
            $data = $gantt->getAttributes();
            foreach ($gantt->getFillable() as $key) {
                if (Str::contains($key, 'fldid')) {
                    $data[$key] = $this->mapIdRelation($old_table->_fields, $new_table->_fields, $gantt[$key]) ?: $gantt[$key];
                }
            }
            $data['table_id'] = $new_table->id;
            $data = $this->service->delSystemFields($data);
            $new_id = TableGantt::insertGetId($data);

            //copy Specifics
            foreach ($gantt->_specifics as $specific) {
                $data = $specific->getAttributes();
                $data['table_gantt_id'] = $new_id;
                $newFld = $this->mapIdRelation($old_table->_fields, $new_table->_fields, $specific['table_field_id'], true);
                if ($newFld) {
                    $data['table_field_id'] = $newFld->id;
                    $data = $this->service->delSystemFields($data);
                    TableGanttSetting::insert($data);
                }
            }
        }
    }

    /**
     * Copy Email
     *
     * @param Table $old_table
     * @param Table $new_table
     */
    public function copyAddonEmail(Table $old_table, Table $new_table) {
        $old_table->load([
            '_email_addon_settings',
            '_row_groups',
            '_fields',
        ]);
        $new_table->load(['_fields', '_row_groups']);

        //copy Gantt
        foreach ($old_table->_email_addon_settings as $addon_setting) {
            $data = $addon_setting->getAttributes();
            foreach ($addon_setting->getFillable() as $key) {
                if (Str::contains($key, 'd_id')) {
                    $data[$key] = $this->mapIdRelation($old_table->_fields, $new_table->_fields, $addon_setting[$key]) ?: $addon_setting[$key];
                }
                if (Str::contains($key, 'group_id')) {
                    $data[$key] = $this->mapIdRelation($old_table->_row_groups, $new_table->_row_groups, $addon_setting[$key]) ?: $addon_setting[$key];
                }
            }
            $data['table_id'] = $new_table->id;
            $data['acc_sendgrid_key_id'] = null;
            $data['acc_google_key_id'] = null;
            $data = $this->service->delSystemFields($data);
            $new_id = TableEmailAddonSetting::insertGetId($data);
        }
    }

    /**
     * Copy Calendar
     *
     * @param Table $old_table
     * @param Table $new_table
     */
    public function copyAddonCalendar(Table $old_table, Table $new_table) {
        $old_table->load([
            '_calendar_addons',
            '_fields',
        ]);
        $new_table->load(['_fields']);

        //copy Gantt
        foreach ($old_table->_calendar_addons as $calendar) {
            $data = $calendar->getAttributes();
            foreach ($calendar->getFillable() as $key) {
                if (Str::contains($key, 'fldid')) {
                    $data[$key] = $this->mapIdRelation($old_table->_fields, $new_table->_fields, $calendar[$key]) ?: $calendar[$key];
                }
            }
            $data['table_id'] = $new_table->id;
            $data = $this->service->delSystemFields($data);
            $new_id = TableCalendar::insertGetId($data);
        }
    }

    /**
     * Copy Sms
     *
     * @param Table $old_table
     * @param Table $new_table
     */
    public function copyAddonSms(Table $old_table, Table $new_table) {
        $old_table->load([
            '_twilio_addon_settings',
            '_row_groups',
            '_fields',
        ]);
        $new_table->load(['_fields', '_row_groups']);

        //copy Twilio Sms
        foreach ($old_table->_twilio_addon_settings as $twilio) {
            $data = $twilio->getAttributes();
            foreach ($twilio->getFillable() as $key) {
                if (Str::contains($key, 'd_id')) {
                    $data[$key] = $this->mapIdRelation($old_table->_fields, $new_table->_fields, $twilio[$key]);
                }
                if (Str::contains($key, 'group_id')) {
                    $data[$key] = $this->mapIdRelation($old_table->_row_groups, $new_table->_row_groups, $twilio[$key]);
                }
            }
            $data['table_id'] = $new_table->id;
            $data['acc_twilio_key_id'] = null;
            $data = $this->service->delSystemFields($data);
            $new_id = TableTwilioAddonSetting::insertGetId($data);
        }
    }

    /**
     * Copy Reports
     *
     * @param Table $old_table
     * @param Table $new_table
     */
    public function copyAddonReport(Table $old_table, Table $new_table) {
        $old_table->load([
            '_report_templates',
            '_reports' => function ($q) {
                $q->with([
                    '_sources' => function ($v) {
                        $v->with('_variables');
                    }
                ]);
            },
            '_bi_charts',
            '_row_groups',
            '_fields',
        ]);
        $new_table->load(['_fields', '_bi_charts', '_row_groups']);

        //copy Report Templates
        foreach ($old_table->_report_templates as $template) {
            $data = $template->getAttributes();
            if ($old_table->user_id != $new_table->user_id) {
                $old = $old_table->_user->_clouds()->where('id', '=', $template['connected_cloud_id'])->first();
                $new = $new_table->_user->_clouds()->whereNotNull('token_json')->where('cloud', '=', $old ? $old['cloud'] : '!')->first();
                $data['connected_cloud_id'] = $new ? $new['id'] : null;
            }
            $data['table_id'] = $new_table->id;
            $data['user_id'] = $new_table->user_id;
            $data = $this->service->delSystemFields($data);
            TableReportTemplate::insert($data);
        }

        //copy Reports
        foreach ($old_table->_reports as $report) {
            $data = $report->getAttributes();
            $data['report_field_id'] = $this->mapIdRelation($old_table->_fields, $new_table->_fields, $report['report_field_id']);
            $data['report_template_id'] = $this->mapIdRelation($old_table->_report_templates, $new_table->_report_templates, $report['report_template_id']);
            $data['table_id'] = $new_table->id;
            $data['user_id'] = $new_table->user_id;
            $data = $this->service->delSystemFields($data);
            $new_id = TableReport::insertGetId($data);

            //copy Sources
            foreach ($report->_sources as $source) {
                $data = $source->getAttributes();
                $data['table_report_id'] = $new_id;
                $data['ref_cond_id'] = $this->mapIdRelation($old_table->_ref_conditions, $new_table->_ref_conditions, $source['ref_cond_id']);
                $data = $this->service->delSystemFields($data);
                $src_id = TableReportSource::insertGetId($data);

                //copy Variables
                foreach ($source->_variables as $variable) {
                    $data = $variable->getAttributes();
                    $data['table_report_source_id'] = $src_id;
                    switch ($variable->type) {
                        case 'field':
                            $data['var_object_id'] = $this->mapIdRelation($old_table->_fields, $new_table->_fields, $variable['var_object_id']);
                            break;
                        case 'rows':
                            $data['var_object_id'] = $this->mapIdRelation($old_table->_row_groups, $new_table->_row_groups, $variable['var_object_id']);
                            break;
                        case 'bi':
                            $data['var_object_id'] = $this->mapIdRelation($old_table->_bi_charts, $new_table->_bi_charts, $variable['var_object_id']);
                            break;
                    }
                    $data = $this->service->delSystemFields($data);
                    $src_id = TableReportSourceVariable::insertGetId($data);
                }
            }
        }
    }

    /**
     * Copy Tournaments
     *
     * @param Table $old_table
     * @param Table $new_table
     */
    public function copyAddonTournament(Table $old_table, Table $new_table) {
        $old_table->load([
            '_tournaments',
            '_row_groups',
            '_fields',
        ]);
        $new_table->load(['_fields', '_row_groups']);

        //copy Tournaments
        foreach ($old_table->_tournaments as $tournament) {
            $data = $tournament->getAttributes();
            foreach ($tournament->getFillable() as $key) {
                if (Str::contains($key, 'fld_id')) {
                    $data[$key] = $this->mapIdRelation($old_table->_fields, $new_table->_fields, $tournament[$key]);
                }
                if (Str::contains($key, 'group_id')) {
                    $data[$key] = $this->mapIdRelation($old_table->_row_groups, $new_table->_row_groups, $tournament[$key]);
                }
            }
            $data['table_id'] = $new_table->id;
            $data = $this->service->delSystemFields($data);
            $new_id = TableTournament::insertGetId($data);
        }
    }

    /**
     * Copy Simplemaps
     *
     * @param Table $old_table
     * @param Table $new_table
     */
    public function copyAddonSimplemap(Table $old_table, Table $new_table) {
        $old_table->load([
            '_simplemaps',
            '_fields',
        ]);
        $new_table->load(['_fields']);

        //copy Simplemaps
        foreach ($old_table->_simplemaps as $simplemap) {
            $data = $simplemap->getAttributes();
            $data['table_id'] = $new_table->id;
            $data = $this->service->delSystemFields($data);
            $new_id = TableSimplemap::insertGetId($data);
        }
    }

    /**
     * Copy Ais
     *
     * @param Table $old_table
     * @param Table $new_table
     */
    public function copyAddonAi(Table $old_table, Table $new_table) {
        $old_table->load([
            '_table_ais',
        ]);

        //copy Ais
        foreach ($old_table->_table_ais as $ai) {
            $data = $ai->getAttributes();
            $data['table_id'] = $new_table->id;
            $data = $this->service->delSystemFields($data);
            $new_id = TableAi::insertGetId($data);
        }
    }

    /**
     * Copy Groupings
     *
     * @param Table $old_table
     * @param Table $new_table
     */
    public function copyAddonGrouping(Table $old_table, Table $new_table) {
        $old_table->load([
            '_groupings',
        ]);

        //copy Ais
        foreach ($old_table->_groupings as $grouping) {
            $data = $grouping->getAttributes();
            $data['table_id'] = $new_table->id;
            $data = $this->service->delSystemFields($data);
            $new_id = TableGrouping::insertGetId($data);
        }
    }

    /**
     * @param Collection $old
     * @param Collection $new
     * @param $id
     * @return Model|int|null
     */
    protected function mapIdRelation(Collection $old, Collection $new, $id, bool $asFld = false)
    {
        $oldItem = $old->where('id', '=', $id)->first();
        $newItem = $new->where('name', '=', $oldItem ? $oldItem['name'] : '!')->first();
        return $asFld
            ? $newItem
            : ($newItem ? $newItem->id : null);
    }

    /**
     * @param Table $old_table
     * @param Table $new_table
     * @return void
     */
    public function copyTableParams(Table $old_table, Table $new_table)
    {
        $params = $old_table->getAttributes();

        $params['user_id'] = $new_table->user_id;
        $params['db_name'] = $new_table->db_name;
        $params['name'] = $new_table->name;

        $new_table->update($this->service->delSystemFields($params));
    }

    /**
     * Copy Header Settings from oldTable owner to newTable owner
     *
     * @param Table $old_table
     * @param Table $new_table
     */
    public function copyHeaderSettings(Table $old_table, Table $new_table) {
        $old_table->load([
            '_fields' => function ($q) use ($old_table) {
                $q->with([
                    '_user_headers' => function ($sq) use ($old_table) {
                        $sq->where('user_id', '=', $old_table->user_id);
                    },
                ]);
            },
        ]);
        $new_table->load(['_fields']);

        //copy Map Icons
        foreach ($old_table->_fields as $field) {
            $data = [];
            foreach ($field->_user_headers as $user_header) {

                $new_field = $new_table->_fields->where('field', '=', $field->field)->first();

                if ($new_field) {
                    $fl = array_merge($user_header->toArray(), [
                        'table_field_id' => $new_field->id,
                        'user_id' => $new_table->user_id,
                    ]);
                    $data[] = array_merge($this->service->delSystemFields($fl), $this->service->getModified(), $this->service->getCreated());
                }
            }
            (count($data) ? UserHeaders::insert($data) : '');
        }
    }

    /**
     * Copy DDLs (or one DDL)
     *
     * @param Table $old_table
     * @param Table $new_table
     * @param array $selected_ids
     */
    public function copyDDLs(Table $old_table, Table $new_table, array $selected_ids = []) {
        $old_table->load([
            '_ddls' => function ($q) {
                $q->with([
                    '_items',
                    '_references' => function ($i) {
                        $i->with('_ref_condition', '_target_field', '_image_field', '_color_field',
                            '_show_field', '_apply_row_group', '_reference_clr_img', '_max_selections_field');
                    }
                ]);
            }
        ]);
        $new_table->load(['_ref_conditions', '_row_groups']);

        //copy DDLs
        foreach ($old_table->_ddls as $ddl) {
            if (count($selected_ids) && !in_array($ddl->id, $selected_ids)) {
                continue;
            }

            $fl = array_merge($ddl->toArray(), ['table_id' => $new_table->id]);
            $new_ddl = DDL::create(
                array_merge($this->service->delSystemFields($fl), $this->service->getModified(), $this->service->getCreated())
            );

            $data = [];
            //copy DDL Items
            foreach ($ddl->_items as $item) {
                $has_apply_group = ($item->_apply_row_group
                    ? $new_table->_row_groups->where('name', '=', $item->_apply_row_group->name)->first()
                    : false);

                $fl = array_merge($item->toArray(), [
                    'ddl_id' => $new_ddl->id,
                    'apply_target_row_group_id' => $has_apply_group ? $has_apply_group->id : null,
                ]);
                $data[] = array_merge($this->service->delSystemFields($fl), $this->service->getModified(), $this->service->getCreated());
            }
            (count($data) ? DDLItem::insert($data) : '');

            //copy DDL References
            foreach ($ddl->_references as $reference) {

                $has_ref_cond = ($reference->_ref_condition
                    ? $this->findRC($reference->_ref_condition->name ?? '', $new_table->_ref_conditions)
                    : false);
                $has_apply_group = ($reference->_apply_row_group
                    ? $new_table->_row_groups->where('name', '=', $reference->_apply_row_group->name)->first()
                    : false);

                $has_listing_field = $this->getListingField($new_table, $has_ref_cond, $reference->_target_field);
                $has_image_field = $this->getListingField($new_table, $has_ref_cond, $reference->_image_field);
                $has_color_field = $this->getListingField($new_table, $has_ref_cond, $reference->_color_field);
                $has_show_field = $this->getListingField($new_table, $has_ref_cond, $reference->_show_field);

                if ($has_ref_cond) {
                    $fl = array_merge($reference->toArray(), [
                        'ddl_id' => $new_ddl->id,
                        'table_ref_condition_id' => $has_ref_cond->id,
                        'apply_target_row_group_id' => $has_apply_group ? $has_apply_group->id : null,
                        'target_field_id' => $has_listing_field ? $has_listing_field->id : null,
                        'show_field_id' => $has_show_field ? $has_show_field->id : null,
                        'image_field_id' => $has_image_field ? $has_image_field->id : null,
                        'color_field_id' => $has_color_field ? $has_color_field->id : null,
                    ]);
                    $new_ref = DDLReference::create(array_merge(
                        $this->service->delSystemFields($fl),
                        $this->service->getModified(),
                        $this->service->getCreated()
                    ));

                    $count = $reference->get_ref_clr_img_count();
                    for ($i = 0; $i*100 < $count; $i++) {
                        $data = [];
                        foreach ($reference->_reference_clr_img()->offset($i*100)->limit(100)->get() as $ddlColor) {
                            $fl = array_merge($ddlColor->toArray(), [
                                'ddl_reference_id' => $new_ref->id,
                            ]);
                            $data[] = $this->service->delSystemFields($fl);
                        }
                        (count($data) ? DDLReferenceColor::insert($data) : '');
                    }
                }
            }
        }
    }

    /**
     * @param Table $old_table
     * @param Table $new_table
     * @param User $new_user
     * @param array $selected_ids
     */
    public function copySingleDDL(Table $old_table, Table $new_table, User $new_user, array $selected_ids = [])
    {
        $ddlRepo = new DDLRepository();
        //$group_ids = $ddlRepo->getRelatedGroups($selected_ids);
        $rc_ids = $ddlRepo->getRelatedRefConds($selected_ids);
        if ($rc_ids) {
            $this->copyReferenceConditions($old_table, $new_table, $new_user, $rc_ids);
        }
        If ($selected_ids) {
            $this->copyDDLs($old_table, $new_table, $selected_ids);
        }
    }

    /**
     * Get Field ID for new Table with new RefCondition.
     *
     * @param Table $new_table
     * @param TableRefCondition|false $has_ref_cond
     * @param TableField|null $listing_field
     * @return bool
     */
    private function getListingField(Table $new_table, $has_ref_cond, $listing_field) {
        if ($has_ref_cond && $listing_field) {
            $ref_cond = $new_table->_ref_conditions->where('id', '=', $has_ref_cond->id)->first();
            $ref_tb = $ref_cond ? $ref_cond->_ref_table : false;

            $has_listing_field = ($ref_tb
                ? $ref_tb->_fields()->where('field', '=', $listing_field->field)->first()
                : false);
        } else {
            $has_listing_field = false;
        }
        return $has_listing_field;
    }
}
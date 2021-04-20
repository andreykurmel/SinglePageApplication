<?php

namespace Vanguard\Repositories\Tablda;


use Illuminate\Database\Eloquent\Collection;
use Vanguard\Models\DataSetPermissions\CondFormat;
use Vanguard\Models\DataSetPermissions\TableColumnGroup;
use Vanguard\Models\DataSetPermissions\TableRefCondition;
use Vanguard\Models\DataSetPermissions\TableRefConditionItem;
use Vanguard\Models\DataSetPermissions\TableRowGroup;
use Vanguard\Models\DataSetPermissions\TableRowGroupCondition;
use Vanguard\Models\DataSetPermissions\TableRowGroupRegular;
use Vanguard\Models\DDL;
use Vanguard\Models\DDLItem;
use Vanguard\Models\DDLReference;
use Vanguard\Models\Table\TableChart;
use Vanguard\Models\Table\TableData;
use Vanguard\Models\Table\TableFieldLink;
use Vanguard\Models\Table\Table;
use Vanguard\Models\Table\TableField;
use Vanguard\Models\Table\TableMapIcon;
use Vanguard\Models\Table\UserHeaders;
use Vanguard\Repositories\Tablda\Permissions\TableRefConditionRepository;
use Vanguard\Repositories\Tablda\Permissions\TableRowGroupRepository;
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
     */
    public function copyBasics(Table $old_table, Table $new_table) {
        $old_table->load([
            '_fields' => function($q) {
                $q->with('_unit_ddl','_ddl');
            }
        ]);
        $new_table->load('_fields', '_ddls');

        //copy TableField Settings
        foreach ($old_table->_fields as $field) {

            $new_field = $new_table->_fields->where('field', '=', $field->field)->first();
            if ($new_field) {

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
                TableField::where('id', $new_field->id)->update( $this->service->delSystemFields($fl) );
            }
        }
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
                $q->with('_regulars', '_ref_condition');
            }
        ]);
        $new_table->load(['_ref_conditions']);

        //copy row groups
        foreach ($old_table->_row_groups as $row_group) {
            if (count($selected_ids) && !in_array($row_group->id, $selected_ids)) {
                continue;
            }
            $has_ref_cond = ($row_group->_ref_condition
                ? $new_table->_ref_conditions->where('name', '=', $row_group->_ref_condition->name)->first()
                : false);

            $fl = array_merge($row_group->toArray(), [
                'table_id' => $new_table->id,
                'row_ref_condition_id' => $has_ref_cond ? $has_ref_cond->id : null,
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
     * Copy Map icons and Charts
     *
     * @param Table $old_table
     * @param Table $new_table
     */
    public function copyMapIconsAndCharts(Table $old_table, Table $new_table) {
        $old_table->load([
            '_fields' => function ($q) {
                $q->with('_map_icons');
            },
            '_charts'
        ]);
        $new_table->load(['_fields']);

        //copy map icon field
        $map_fld_old = $old_table->_fields->where('id', '=', $old_table->map_icon_field_id)->first();
        $map_fld_new = $new_table->_fields->where('field', '=', $map_fld_old->field ?? '')->first();
        if ($map_fld_new) {
            $new_table->update([
                'map_icon_field_id' => $map_fld_new->id,
            ]);
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

        //copy Charts
        foreach ($old_table->_charts as $chart) {
            $fl = array_merge($chart->toArray(), [
                'table_id' => $new_table->id,
                'user_id' => $new_table->user_id,
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
                        $i->with('_ref_condition', '_target_field', '_image_field', '_show_field', '_apply_row_group');
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

            $data = [];
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
                $has_show_field = $this->getListingField($new_table, $has_ref_cond, $reference->_show_field);

                if ($has_ref_cond) {
                    $fl = array_merge($reference->toArray(), [
                        'ddl_id' => $new_ddl->id,
                        'table_ref_condition_id' => $has_ref_cond->id,
                        'apply_target_row_group_id' => $has_apply_group ? $has_apply_group->id : null,
                        'target_field_id' => $has_listing_field ? $has_listing_field->id : null,
                        'show_field_id' => $has_show_field ? $has_show_field->id : null,
                        'image_field_id' => $has_image_field ? $has_image_field->id : null,
                    ]);
                    $data[] = array_merge($this->service->delSystemFields($fl), $this->service->getModified(), $this->service->getCreated());
                }
            }
            (count($data) ? DDLReference::insert($data) : '');
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
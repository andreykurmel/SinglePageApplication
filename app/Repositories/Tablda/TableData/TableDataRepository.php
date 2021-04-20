<?php

namespace Vanguard\Repositories\Tablda\TableData;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;
use Ramsey\Uuid\Uuid;
use Vanguard\AppsModules\AppOnChangeHandler;
use Vanguard\Jobs\DDLChangedWatcherJob;
use Vanguard\Models\DDL;
use Vanguard\Models\DDLReference;
use Vanguard\Models\FavoriteRow;
use Vanguard\Models\Table\Table;
use Vanguard\Models\Table\TableField;
use Vanguard\Models\Table\UserHeaders;
use Vanguard\Repositories\Tablda\HistoryRepository;
use Vanguard\Repositories\Tablda\ImportRepository;
use Vanguard\Repositories\Tablda\TableFieldRepository;
use Vanguard\Repositories\Tablda\TableRepository;
use Vanguard\Services\Tablda\HelperService;
use Vanguard\Services\Tablda\Permissions\UserPermissionsService;
use Vanguard\Watchers\DDLWatcher;

class TableDataRepository
{
    protected $service;
    protected $permissionsService;

    /**
     * TableDataRepository constructor.
     */
    public function __construct()
    {
        $this->service = new HelperService();
        $this->permissionsService = new UserPermissionsService();
        $this->dbcolRepository = new ImportRepository();
    }

    /**
     * Get distinct values for one field.
     *
     * @param Table $table
     * @param TableField $field
     * @param string $db_field
     * @return array
     */
    public function getDistinctiveField(Table $table, TableField $field, string $db_field)
    {
        $sql = new TableDataQuery($table, true);

        $arr = $sql->getQuery()
            ->distinct()
            ->select($db_field)
            ->get()
            ->pluck($db_field)
            ->toArray();

        $res = [];
        if ($field->f_type == 'User') {
            $arr = MselectData::convert($arr, $field->input_type);
            [$users, $groups] = (new TableDataRowsRepository())->getUsersAndGroups( collect($arr) );

            foreach ($arr as $item) {
                //if User
                $usr = $users->where('id', '=', $item)->first();
                if ($usr) {
                    $res[$item] = $usr->first_name . ($usr->last_name ? ' '.$usr->last_name : '');
                }
                //if UserGroup
                $usrgroup = $groups->where('id', '=', $item)->first();
                if ($usrgroup) {
                    $res[$item] = $usrgroup->first_name . ($usrgroup->last_name ? ' '.$usrgroup->last_name : '');
                }
            }

        } else {
            foreach ($arr as $item) {
                $res[$item] = $item;
            }
        }

        return $res;
    }

    /**
     * Save To AttachmentCell Last File Path.
     *
     * @param array $data
     * @param string $file_path_with_name
     */
    public function saveToCellLastFilePath(array $data, string $file_path_with_name)
    {
        $table_id = $data['table_id'] ?? null;
        $field_id = $data['table_field_id'] ?? null;
        $row_id = $data['row_id'] ?? null;

        if ($table_id && $field_id && $row_id) {
            $table = (new TableRepository())->getTable($table_id);
            $header = $table->_fields()->where('id', $field_id)->first();

            if ($table && $header) {
                try {
                    (new TableDataQuery($table))->getQuery()
                        ->where('id', $row_id)
                        ->update([$header->field => $file_path_with_name]);
                } catch (\Exception $e) {
                    //
                }
            }
        }
    }

    /**
     * @param Table $table
     */
    public function updateCopiedAttachPaths(Table $table)
    {
        $attach_fields = $table->_fields()->where('f_type', '=', 'Attachment')->get();
        if ($attach_fields->count()) {
            $rows = (new TableDataQuery($table))->getQuery();
            foreach ($attach_fields as $fld) {
                $rows->whereNotNull($fld->field);
            }
            $rows = $rows->get();
            foreach ($rows as $r) {
                $updater = [];
                foreach ($attach_fields as $fld) {
                    if (preg_match('/^\d+_/i', $r->{$fld->field}) && !preg_match('/^'.$table->id.'_/i', $r->{$fld->field})) {
                        $updater[$fld->field] = preg_replace('/^\d+_/i', $table->id.'_', $r->{$fld->field});
                    }
                }
                if ($updater) {
                    (new TableDataQuery($table))->getQuery()
                        ->where('id', $r->id)
                        ->update($updater);
                }
            }
        }
    }

    /**
     * Get DDL values for reference DDL.
     *
     * @param DDL $ddl
     * @param array $row
     * @param string $search
     * @param int $limit
     * @return array of items: [
     *  'value' => string,
     *  'description' => string,
     *  'image' => string,
     * ]
     */
    public function getDDLvalues(DDL $ddl, array $row = [], string $search = '', int $limit = 200)
    {
        $results = [];
        $applied_row_groups = $row['_applied_row_groups'] ?? [];

        foreach ($ddl->_references as $ref) {
            if (count($results) < $limit) {
                //Empty TargetRowGroup or Row belong to TargetRowGroup
                if (
                    !$applied_row_groups
                    || !$ref->apply_target_row_group_id
                    || in_array($ref->apply_target_row_group_id, $applied_row_groups)
                ) {
                    $ref_table = $ref->_ref_condition->_ref_table ?? null;

                    if ($ref_table) {
                        $ref_rows = $this->fillResultsFromReference($ref, $ref_table, $row, $search, $limit);
                        $results = array_merge($results, $ref_rows);
                    }

                }
            }
        }

        $ddl_items = [];
        foreach ($ddl->_items as $item) {
            $found = !$search || strpos(strtolower($item->option), $search) !== false;
            if ($found) {
                //Empty TargetRowGroup or Row belong to TargetRowGroup
                if (!$item->apply_target_row_group_id || in_array($item->apply_target_row_group_id, $applied_row_groups)) {
                    $ddl_items[] = [
                        'value' => $item->option ?: null,
                        'description' => $item->description ?: null,
                        'image' => $item->image_path ?: null,
                    ];
                }
            }
        }
        if (count($results) < $limit) {
            if ($ddl->items_pos == 'before') {
                $results = array_merge($ddl_items, $results);
            } else {
                $results = array_merge($results, $ddl_items);
            }
        }

        return $results;
    }

    /**
     * @param DDLReference $ref
     * @param Table $ref_table
     * @param array $row
     * @param string $search
     * @param int $limit
     * @return array
     */
    protected function fillResultsFromReference(DDLReference $ref, Table $ref_table, array $row, string $search = '', int $limit = 200)
    {
        $target_fld = $ref->_target_field ? $ref->_target_field->field : 'id';
        $image_fld = $ref->_image_field ? $ref->_image_field->field : null;
        $show_fld = $ref->_show_field ? $ref->_show_field->field : null;

        $sql = new TableDataQuery($ref_table, true);
        $sql->applyRefConditionRow($ref->_ref_condition, $row);
        $sql = $sql->getQuery();

        $sel_columns = array_filter([$target_fld, $image_fld, $show_fld]);
        $sql->select($sel_columns);
        $sql->distinct();
        if ($search) {
            $sql->where($show_fld ?: $target_fld, 'like', "%$search%");
        }
        if ($ref->sort_type) {
            $sql->orderBy($target_fld, strtolower($ref->sort_type));
        }

        try {
            $sql_rows = $sql->limit($limit)
                ->get()
                ->map(function ($row) use ($target_fld, $image_fld, $show_fld) {
                    return [
                        'value' => $row[$target_fld] ?: null,
                        'show' => $row[$show_fld ?: $target_fld] ?: null,
                        'image' => $row[$image_fld] ?: null,
                    ];
                })
                ->toArray();
        } catch (\Exception $e) {
            //incorrect DDL Reference
            //$ref->delete();
            $sql_rows = [];
        }

        return $sql_rows;
    }

    /**
     * Favorite Toggle Row
     *
     * @param $data
     * @return array
     */
    public function favoriteToggleRow($data)
    {
        $data['user_id'] = auth()->id();
        $favoriteRow = FavoriteRow::where('table_id', $data['table_id'])
            ->where('user_id', $data['user_id'])
            ->where('row_id', $data['row_id'])
            ->first();

        if (!$favoriteRow && $data['change']) {
            $dataSave = array_merge($data, $this->service->getModified(), $this->service->getCreated());
            FavoriteRow::create($dataSave);
        } else if ($favoriteRow && !$data['change']) {
            $favoriteRow->delete();
        }
        return ['change_status' => $data['change']];
    }

    /**
     * Favorite Toggle All Rows
     *
     * @param int $table_id
     * @param bool $status
     * @param array $rows_ids
     * @return array
     */
    public function toggleAllFavorites(int $table_id, bool $status, array $rows_ids)
    {
        $user_id = auth()->id();
        //clear all before adding
        $res = FavoriteRow::where('table_id', $table_id)
            ->where('user_id', $user_id)
            ->whereIn('row_id', $rows_ids)
            ->delete();
        //if needed -> add all rows
        if ($status) {
            $data = [];
            foreach ($rows_ids as $r_id) {
                $data[] = array_merge([
                    'table_id' => $table_id,
                    'user_id' => $user_id,
                    'row_id' => $r_id,
                ], $this->service->getModified(), $this->service->getCreated());
            }
            $res = FavoriteRow::insert($data);
        }
        return ['change_status' => $res];
    }

    /**
     * Show Hide Column Toggle
     *
     * @param $user_id
     * @param $table_data_ids
     * @param $is_showed
     * @return bool
     */
    public function showColumnsToggle($user_id, $table_data_ids, $is_showed)
    {
        $dataSave = $this->service->getModified();
        $dataSave['is_showed'] = $is_showed;
        return UserHeaders::where('user_id', $user_id)
            ->whereIn('table_field_id', $table_data_ids)
            ->update($dataSave);
    }

    /**
     * Get Direct TableRow by Id.
     *
     * @param Table $table
     * @param int $row_id
     * @return mixed
     */
    public function getDirectRow(Table $table, int $row_id = null)
    {
        $sql = (new TableDataQuery($table))->getQuery( !!$table->is_system );
        if ($row_id) {
            $sql->where($table->db_name.'.id', $row_id);
        } else {
            $sql->where($table->db_name.'.row_hash', $this->service->sys_row_hash['cf_temp']);
        }
        return $sql->first();
    }

    /**
     * Get Direct TableRows by array of row Ids.
     *
     * @param Table $table
     * @param array $rows_ids
     * @return mixed
     */
    public function getDirectMassRows(Table $table, array $rows_ids)
    {
        return (new TableDataQuery($table))
            ->getQuery()
            ->whereIn($table->db_name.'.id', $rows_ids)
            ->get();
    }

    /**
     * Get data from user`s table
     *
     * @link TableDataRowsRepository::getRows()
     *
     * @param array $data
     * @param $user_id
     * @return array
     */
    public function getRows(Array $data, $user_id)
    {
        return (new TableDataRowsRepository())->getRows($data, $user_id);
    }

    /**
     * Get Markers or Bounds for Table with GSI addon.
     * @param $type
     * @param Table $table
     * @param array $data
     * @param $user_id
     * @return array
     */
    public function getMapThing($type, Table $table, Array $data, $user_id)
    {
        $table->load([
            '_fields' => function ($q) {
                $q->joinOwnerHeader();
            }
        ]);

        $lat_header = $table->_fields->where('is_lat_field', 1)->first();
        $long_header = $table->_fields->where('is_long_field', 1)->first();
        $icon_header = $table->_fields->where('id', $table->map_icon_field_id)->first();

        if ($lat_header && $long_header) {
            //get SQL
            $sql = new TableDataQuery($table, true, $user_id);
            //apply searching, filtering, row rules to getRows
            $sql->applyWhereClause($data, $user_id);
            //select only 'icon' and 'lat','lng'
            $icn_field = $icon_header ? $icon_header->field : '""';
            $sql->getQuery()->selectRaw(implode(', ', [
                'id',
                $lat_header->field . ' as lat',
                $long_header->field . ' as lng',
                $icn_field . ' as icon'
            ]));

            ini_set('memory_limit', '256M');

            switch ($type) {
                case 'Bounds':
                    $res = $this->getMapBounds($sql->getQuery(), $lat_header->field, $long_header->field);
                    break;
                case 'Markers':
                    $res = $this->getMapMarkers($sql->getQuery(), $data);
                    break;
                default:
                    $res = [];
                    break;
            }

            return $res;
        } else {
            return [];
        }
    }

    /**
     * Get marker's bounds for table rows.
     *
     * @param Builder $sql
     * @param $lat_field
     * @param $long_field
     * @return array
     */
    private function getMapBounds(Builder $sql, $lat_field, $long_field)
    {
        return [
            'top' => (clone $sql)->where($lat_field, '!=', '')->min($lat_field),
            'bottom' => (clone $sql)->where($lat_field, '!=', '')->max($lat_field),
            'left' => (clone $sql)->where($long_field, '!=', '')->min($long_field),
            'right' => (clone $sql)->where($long_field, '!=', '')->max($long_field),
        ];
    }

    /**
     * Get markers from table rows.
     *
     * @param Builder $sql
     * @param array $data
     * @return array
     */
    private function getMapMarkers(Builder $sql, Array $data)
    {
        $gr = 5; //grouping in 32x32 pixel square (2^5);
        $chunk_len = 75000;

        $res = [];
        $zoom = $data['map_bounds']['zoom'] ?? 0;
        $zoom = min($zoom, 21);
        $len = $sql->count();
        for ($i = 0; $i * $chunk_len < $len; $i++) {

            $rows = (clone $sql)
                ->offset($i * $chunk_len)
                ->limit($chunk_len)
                ->get();

            foreach ($rows as $row) {
                $row->lat = floatval(preg_replace('/[^\d\.-]/i', '', $row->lat));
                $row->lng = floatval(preg_replace('/[^\d\.-]/i', '', $row->lng));

                $lat = $this->latToY($row->lat) >> ((21 - $zoom) + $gr);
                $lng = $this->lonToX($row->lng) >> ((21 - $zoom) + $gr);
                if (!empty($res[$lat . '_' . $lng])) {
                    $res[$lat . '_' . $lng]['cnt']++;
                    if ($res[$lat . '_' . $lng]['id']) {
                        $res[$lat . '_' . $lng]['id'] = null;
                        $res[$lat . '_' . $lng]['icon'] = $this->getClusterIcon($res[$lat . '_' . $lng]['cnt']);
                        //get center of clustering squares
                        $res[$lat . '_' . $lng]['lat'] = $this->YToLat(($lat + 0.5) * pow(2, (21 - $zoom) + $gr));
                        $res[$lat . '_' . $lng]['lng'] = $this->XToLon(($lng + 0.5) * pow(2, (21 - $zoom) + $gr));
                    }
                } else {
                    $res[$lat . '_' . $lng] = [
                        'cnt' => 1,
                        'lat' => $row->lat,
                        'lng' => $row->lng,
                        'icon' => $row->icon,
                        'id' => $row->id,
                    ];
                }
            }
        }

        return $res;
    }


    /**
     * map convert functions
     * LAT/LNG to Google Map's Pixels
     * source of idea: https://appelsiini.net/2008/introduction-to-marker-clustering-with-google-maps/
     */
    private function latToY($lat)
    {
        return round(268435456 - 85445659.4471 * log((1 + sin($lat * pi() / 180)) / (1 - sin($lat * pi() / 180))) / 2);
    }

    private function lonToX($lon)
    {
        return round(268435456 + 85445659.4471 * $lon * pi() / 180);
    }

    function getClusterIcon($cnt)
    {
        return '/images/m' . min(strlen($cnt), 5) . '.png';
    }

    private function YToLat($Y)
    {
        return asin((exp((268435456 - $Y) / 85445659.4471 * 2) - 1) / (exp((268435456 - $Y) / 85445659.4471 * 2) + 1)) / pi() * 180;
    }

    private function XToLon($X)
    {
        return ($X - 268435456) / 85445659.4471 / pi() * 180;
    }

    /**
     * Get markers for table with GSI addon.
     *
     * @param Table $table
     * @param $row_id
     * @return array
     */
    public function getMarkerPopup(Table $table, $row_id)
    {
        $sql = new TableDataQuery($table);
        return $sql->getQuery()
            ->where($sql->getSqlFld(), $row_id)
            ->first()
            ->toArray();
    }

    /**
     * @param Table $table
     * @param array $params
     * @return bool|mixed
     */
    public function removeByParams(Table $table, array $params)
    {
        if (count($params)) {
            $sql = (new TableDataQuery($table))->getQuery();
            foreach ($params as $key => $val) {
                $sql->where($key, '=', $val);
            }
            return $sql->delete();
        } else {
            return false;
        }
    }

    /**
     * insert row into the table
     *
     * @param Table $table_info :
     * @param array $fields :
     * [
     *  table_id: int,
     *  fields: [
     *      table_field: value,
     *      ...
     *  ]
     * ]
     * @param int $user_id
     * @param int|null $permission_id
     * @return int
     */
    public function insertRow(Table $table_info, Array $fields, $user_id, $permission_id = null)
    {
        $this->rowIsUniqueCheck($table_info, $fields);

        $table_info->loadMissing('_fields');
        $fields = $this->setDefaults($table_info, $fields, $user_id, $permission_id);

        $table_info->num_rows += 1;
        Table::where('id', $table_info->id)
            ->update(['num_rows' => $table_info->num_rows]);

        $sql = (new TableDataQuery($table_info, true))->getQuery();

        $dataSave = array_merge(
            $this->service->delSystemFields($fields),
            $this->service->getModified($table_info),
            $this->service->getCreated($table_info)
        );
        if ($permission_id) {
            $dataSave['request_id'] = $permission_id;
        }
        $dataSave = $this->setSpecialValues($table_info, $dataSave);

        $row_id = $sql->insertGetId($dataSave);

        $after_inserts = ['row_order' => $fields['row_order'] ?? $row_id];
        foreach ($fields as $key => $val) {
            $hdr = $table_info->_fields->where('field', $key)->first();
            if ($hdr && $hdr->f_type == 'Auto Number') {
                $after_inserts[$key] = $row_id;
            }
        }
        (new TableDataQuery($table_info, true))->getQuery()
            ->where('id', $row_id)
            ->update($after_inserts);

        return $row_id;
    }

    /**
     * @param Table $table
     * @param string $fld
     * @param string $formula
     * @return int
     */
    public function setDefFormulaToAll(Table $table, string $fld, string $formula)
    {
        return (new TableDataQuery($table))->getQuery()->update([
            $fld.'_formula' => $formula,
        ]);
    }

    /**
     * Set Default Values to fields.
     *
     * @param Table $table
     * @param array $fields
     * @param $user_id
     * @param null $table_permission_id
     * @return array
     */
    public function setDefaults(Table $table, array $fields, $user_id, $table_permission_id = null)
    {
        if (!empty($fields['_defaults_applied'])) {
            //$fields = $this->setDefaultsFromColumnDatabase($table, $fields);
            return $fields;
        }

        if (!$table->_permissions_for_def) {
            $table->_permissions_for_def = $this->permissionsService->loadTablePermissions($table, $user_id, false, $table_permission_id);
        }

        //UserRight Defaults have more priority than Column DataBase
        if ($table->user_id != $user_id || $table_permission_id) {
            //set Defaults from UserRights
            foreach ($table->_permissions_for_def as $permission) {
                foreach ($permission->_default_fields as $def_col) {
                    if ($def_col->_field) {

                        $fld_key = $def_col->_field->input_type !== 'Formula'
                            ? $def_col->_field->field
                            : $def_col->_field->field . '_formula';

                        if ($fld_key && $def_col->default && empty($fields[$fld_key])) {
                            $fields[$fld_key] = $this->parseDefval($def_col->default);
                        }

                    }
                }
            }
        }

        $fields = $this->setDefaultsBySpecialRules($table, $fields, $user_id);

        $fields = $this->setDefaultsFromColumnDatabase($table, $fields);

        $fields['_defaults_applied'] = true;

        return $fields;
    }

    /**
     * Fill rows with new default value
     * @param Table $table
     * @param string $field
     * @param string $f_default
     * @return int
     */
    public function updateDatabaseDefaults(Table $table, string $field, string $f_default)
    {
        if (strlen($f_default)) {
            return (new TableDataQuery($table))
                ->getQuery()
                ->where($field, '=', '')
                ->orWhereNull($field)
                ->update([
                    $field => $f_default
                ]);
        } else {
            return 0;
        }
    }

    /**
     * @param Table $table
     * @param TableField $db_field
     * @param array $field
     * @return array
     */
    public function updateNamesInFormulaes(Table $table, TableField $db_field, array $field)
    {
        $nodef1 = [];
        $nodef2 = [];
        $newname = $field['name'] ?? '';
        if ($newname && $db_field->name && $db_field->name != $newname) {
            $nodef1 = $this->namesUpdater($table, $db_field->name, $newname);
        }
        $newfs = $field['formula_symbol'] ?? '';
        if ($newfs && $db_field->formula_symbol && $db_field->formula_symbol != $newfs) {
            $nodef2 = $this->namesUpdater($table, $db_field->formula_symbol, $newfs);
        }
        return array_merge($nodef1, $nodef2);
    }

    /**
     * @param Table $table
     * @param $from
     * @param $to
     * @return array
     */
    protected function namesUpdater(Table $table, $from, $to)
    {
        $updater = [];
        $nodef = [];
        foreach ($table->_fields as $header) {
            if ($header->input_type === 'Formula') {
                $updater[$header->field.'_formula'] = DB::raw("REPLACE(" . $header->field.'_formula' . ", '{" . $from . "}', '{" . $to . "}')");
            }
            if (strpos($header->f_default, $from) !== false) {
                $header->f_default = preg_replace('/\{' . $from . '\}/i', '{' . $to . '}', $header->f_default);
                (new TableFieldRepository())->updateTableField($table, $header->id, $header->toArray());
                $nodef[] = $header->field;
            }
        }
        if ($updater) {
            (new TableDataQuery($table))->getQuery()->update($updater);
        }
        return $nodef;
    }

    /**
     * @param Table $table
     * @return mixed
     */
    public function tableUserFields(Table $table)
    {
        return $table->_fields()
            ->where('f_type', 'User')
            ->get(['field'])
            ->pluck('field')
            ->toArray();
    }

    /**
     * Defaults from special rules.
     *
     * @param Table $table
     * @param array $fields
     * @param $user_id
     * @return array
     */
    private function setDefaultsBySpecialRules(Table $table, array $fields, $user_id)
    {
        if ($table->user_id != $user_id) {
            //set Defaults from UserRights
            foreach ($table->_fields as $header) {
                if ($header->f_type == 'User' && empty($fields[$header->field])) {
                    $fields[$header->field] = auth()->id();
                }
            }
        }
        if ($table->db_name == 'correspondence_tables') {
            $fields['active'] = empty($fields['active']) ? 0 : 1;
        }
        return $fields;
    }

    /**
     * @param $def_val
     * @return string
     */
    private function parseDefval($def_val)
    {
        switch ($def_val) {
            case '{$first_name}':
                $def = auth()->user() ? auth()->user()->first_name : '';
                break;
            case '{$last_name}':
                $def = auth()->user() ? auth()->user()->last_name : '';
                break;
            case '{$email}':
                $def = auth()->user() ? auth()->user()->email : '';
                break;
            case '{$user}':
                $def = auth()->user() ? auth()->user()->id : '';
                break;
            default:
                $def = $def_val;
        }
        return $def;
    }

    /**
     * @param Table $table
     * @param array $fields
     * @return array
     */
    private function setDefaultsFromColumnDatabase(Table $table, array $fields)
    {
        //set Defaults from Column DataBase
        foreach ($table->_fields as $col) {
            $col_val = isset($fields[$col->field]) ? (string)$fields[$col->field] : '';
            if (!strlen($col_val)) {
                $fields[$col->field] = $this->parseDefval($col->f_default);
            }
            $frm_key = $col->field . '_formula';
            $frm_val = isset($fields[$frm_key]) ? (string)$fields[$frm_key] : '';
            if ($col->input_type === 'Formula' && !strlen($frm_val)) {
                $fields[$frm_key] = ($col->f_formula);
            }
        }
        return $fields;
    }

    /**
     * Insert Mass Rows into Table.
     *
     * @param Table $table
     * @param array $rows
     */
    public function insertMass(Table $table, array $rows)
    {
        $max_id = (new TableDataQuery($table))->getQuery()
            ->orderBy('id', 'desc')
            ->first(['id']);
        $max_id = ($max_id ? $max_id->id + 1 : 1);

        foreach ($rows as $r_index => $row) {
            $dataSave['_defaults_applied'] = true;
            $dataSave = $this->setDefaults($table, $dataSave, auth()->id());
            $dataSave = array_merge(
                $this->service->delSystemFields($row),
                $this->service->getModified($table),
                $this->service->getCreated($table)
            );
            $dataSave = $this->setSpecialValues($table, $dataSave);
            $dataSave['row_order'] = $max_id + $r_index;
            $rows[$r_index] = $dataSave;
        }

        $sql = new TableDataQuery($table);
        $sql->getQuery()->insert($rows);
    }

    /**
     * Update row by id
     *
     * @param Table $table_info
     * @param int $row_id
     * @param array $fields :
     *  [
     *      table_field: value,
     *      ...
     *  ]
     * @param int $user_id :
     * @param array $extra :
     *
     * @return bool|null
     * @throws \Exception
     */
    public function updateRow(Table $table_info, $row_id, Array $fields, $user_id, array $extra = [])
    {
        $this->rowIsUniqueCheck($table_info, $fields, $row_id);

        $dataQuery = (new TableDataQuery($table_info, true));
        $sql = $dataQuery->getQuery();

        $old_row = $sql->where($dataQuery->getSqlFld(), '=', $row_id)->first();
        $old_row = $old_row ? $old_row->toArray() : [];

        $new_row = array_merge($old_row, $fields);
        //$new_row = $this->setDefaults($table_info, $new_row, $user_id);
        if (!isset($extra['nohandler'])) {
            $new_row = (new AppOnChangeHandler($table_info))->testRow($new_row, $old_row);
        }

        $data_fields = $this->storeInHistory($table_info, $old_row, $row_id, $new_row, $user_id);

        //dispatch(new DDLChangedWatcherJob($table_info, $new_row, $old_row));

        return $this->updateWithRowHash($table_info, $dataQuery, $row_id, $data_fields);
    }

    /**
     * @param Table $table_info
     * @param int $row_id
     * @param array $fields
     * @return int
     */
    public function quickUpdate(Table $table_info, int $row_id, array $fields = [])
    {
        $dataQuery = (new TableDataQuery($table_info, true));
        return $this->updateWithRowHash($table_info, $dataQuery, $row_id, $fields);
    }

    /**
     * @param Table $table
     * @param TableDataQuery $dataQuery
     * @param int $row_id
     * @param array $fields
     * @return int
     */
    protected function updateWithRowHash(Table $table, TableDataQuery $dataQuery, int $row_id, array $fields)
    {
        $dataSave = array_merge($fields, $this->service->getModified($table));
        $dataSave = $this->setSpecialValues($table, $dataSave);

        return $dataQuery->getQuery()
            ->where($dataQuery->getSqlFld(), '=', $row_id)
            ->update($table->is_system ? $fields : $dataSave);
    }

    /**
     * @param Table $table
     * @param array $fields
     * @return array
     * @throws \Error
     */
    protected function setSpecialValues(Table $table, array $fields)
    {
        $fields['row_hash'] = Uuid::uuid4();
        $columns = [];
        $all_len = 0;
        foreach ($table->_fields as $hdr) {
            if ($table->is_system && isset($fields[$hdr->field]) && !$fields[$hdr->field]) {
                $fields[$hdr->field] = null;
            }

            $val = $fields[$hdr->field] ?? '';

            //SmartSize functionality
            if ($this->dbcolRepository->notEnoughSize($hdr->f_type, $hdr->f_size, $val)) {
                $this->calc_fsize($hdr, $val);
                $hdr->save();
                $columns[] = $hdr->getAttributes();
            }

            //Formula col functionality
            if ($hdr->input_type === 'Formula') {
                $frmla = $fields[$hdr->field.'_formula'] ?? '';
                $fields[$hdr->field] = strlen($val) ? $val : '';
                $fields[$hdr->field.'_formula'] = strlen($frmla) ? $frmla : '';
                if (strlen($frmla) > 256) {
                    $this->dbcolRepository->IncFormulaSize($table, $hdr->field.'_formula', strlen($frmla));
                }
            }
            $all_len += in_array($hdr->f_type, ['Text','Long Text','Vote']) ? 16 : $hdr->f_size;
        }
        if ($all_len > 65535) {
            throw new \Error('Table Row Size more 65535 (without Text columns) table_id:'.$table->id);
        }
        $this->dbcolRepository->IncreaseColSize($table, $columns);
        return $fields;
    }


    /**
     * @param TableField $hdr
     * @param $val
     * @param int $depth
     */
    protected function calc_fsize(TableField $hdr, $val, $depth = 1)
    {
        $hdr->f_size = $this->dbcolRepository->increaseSize($hdr->f_type, $hdr->f_size, $val);
        if ($hdr->f_size > 2048) {
            $hdr->f_type = 'Text';
        }
        if ($hdr->f_size > 32768) {
            $hdr->f_type = 'Long Text';
        }

        if ($depth <= 3 && $this->dbcolRepository->notEnoughSize($hdr->f_type, $hdr->f_size, $val)) {
            $this->calc_fsize($hdr, $val, $depth+1);
        }
    }

    /**
     * save new Row in DB.
     *
     * @param Table $table
     * @param array $updated_row
     * @return array
     */
    public function saveInDbNewRow(Table $table, array $updated_row)
    {
        $sql = (new TableDataQuery($table, true))
            ->getQuery(false)
            ->where('row_hash', $this->service->sys_row_hash['cf_temp']);

        $upd_data = $this->setSpecialValues($table, $updated_row);
        $upd_data = $this->service->delSystemFields($upd_data);
        $upd_data['row_hash'] = $this->service->sys_row_hash['cf_temp'];

        if ($sql->count()) {
            $sql->update($upd_data);
        } else {
            $sql->insert($upd_data);
        }

        return array_merge($updated_row, $upd_data);
    }

    /**
     * Check that row is unique.
     *
     * @param Table $table
     * @param array $fields
     * @param int|null $row_id
     * @throws \Exception
     */
    public function rowIsUniqueCheck(Table $table, array $fields, int $row_id = null)
    {
        if ($table->_unique_fields->count()) {
            $query = (new TableDataQuery($table, true))->getQuery();
            //apply unique clause
            foreach ($table->_unique_fields as $header) {
                $query->where($header->field, $fields[$header->field] ?? null);
            }
            //exclude updated row id
            if ($row_id) {
                $query->where('id', '!=', $row_id);
            }
            //check that row with these columns already present
            if ($query->count() > 0) {
                $uniq_fields = $table->_unique_fields->pluck('name')->toArray();
                throw new \Exception('Columns "'.implode(', ', $uniq_fields).'" should be unique!', 1);
            }
        }
    }

    /**
     * Store values in History.
     *
     * @param Table $table_info
     * @param array $old_row
     * @param $row_id
     * @param array $fields
     * @param $user_id
     * @return array
     */
    private function storeInHistory(Table $table_info, array $old_row, $row_id, array $fields, int $user_id = null)
    {
        $user_id = $user_id ?: $table_info->user_id;

        if (!$table_info->_all_fields) {
            //optimisation of a lot calls of updateRow ($table->_fields can be different because User's permissions)
            $table_info->_all_fields = $table_info->_fields()->get();
        }
        $historyRepo = new HistoryRepository();

        $data_fields = $this->service->delSystemFields($fields);
        foreach ($data_fields as $key => $new_value) {
            $field_for_id = $table_info->_all_fields->where('field', '=', $key)->first();
            if ($field_for_id) {

                $old_val = $old_row[$key] ?? null;

                if ($old_val != $new_value && $field_for_id->show_history == 1 && $user_id) {
                    $historyRepo->store($table_info, $user_id, $field_for_id->id, $row_id, $old_val);
                }

            } else {
                if (!preg_match('/(_formula|_sys)$/i', $key)) {
                    //unset all not found columns except of 'Formulas' and 'Systems'
                    unset($data_fields[$key]);
                }
            }
        }
        return $data_fields;
    }

    /**
     * Delete row by id
     *
     * @param \Vanguard\Models\Table\Table $table_info
     * @param int $row_id :
     *
     * @return bool|null
     */
    public function deleteRow(Table $table_info, int $row_id)
    {
        $table_info->num_rows -= 1;
        Table::where('id', $table_info->id)
            ->update(['num_rows' => $table_info->num_rows]);

        $sql = new TableDataQuery($table_info, true);

        return $sql->getQuery()
            ->where($sql->getSqlFld(), '=', $row_id)
            ->delete();
    }

    /**
     * Update Mass Check Boxes for One Table column.
     *
     * @param \Vanguard\Models\Table\Table $table_info
     * @param array $row_ids
     * @param String $field
     * @param $status
     * @return int
     */
    public function updateMassCheckBoxes(Table $table_info, Array $row_ids, String $field, $status)
    {
        $sql = new TableDataQuery($table_info);
        $dataSave = array_merge([$field => $status], $this->service->getModified($table_info));
        return $sql->getQuery()
            ->whereIn('id', $row_ids)
            ->update($dataSave);
    }

    /**
     * Get All rows
     *
     * @param \Vanguard\Models\Table\Table $table_info
     * @param array $data : -> similar to getRows() method ->
     * @param int $user_id :
     *
     * @return mixed
     */
    public function getAllRows(Table $table_info, Array $data, $user_id)
    {
        $sql = new TableDataQuery($table_info, true, $user_id);

        //apply searching, filtering, row rules to getRows
        $sql->applyWhereClause($data, $user_id);

        return $sql->getQuery()->get();
    }

    /**
     * Delete rows
     *
     * @param \Vanguard\Models\Table\Table $table_info
     * @param array $data : -> similar to getRows() method ->
     * @param int $user_id :
     *
     * @return array
     */
    public function deleteAllRows(Table $table_info, Array $data, $user_id)
    {
        $sql = new TableDataQuery($table_info, true, $user_id);

        //apply searching, filtering, row rules to getRows
        $sql->applyWhereClause($data, $user_id);
        $sql = $sql->getQuery();

        $all_ids = (clone $sql)->select('id')->get()->pluck('id')->toArray();
        $sql->delete();
        return $all_ids;
    }

    /**
     * Delete rows by ids
     *
     * @param Table $table
     * @param array $rows_ids
     * @return bool
     */
    public function deleteSelectedRows(Table $table, Array $rows_ids)
    {
        if (!$rows_ids) {
            return false;
        }

        $sql = new TableDataQuery($table, true);
        return $sql->getQuery()
            ->whereIn('id', $rows_ids)
            ->delete();
    }

    /**
     * Mass Copy rows by ids
     *
     * @param Table $table
     * @param array $rows_ids
     * @param array $only_columns
     * @return array : [from_id => to_id]
     */
    public function massCopy(Table $table, Array $rows_ids, Array $only_columns = [])
    {
        $sql = new TableDataQuery($table);
        $rows = $sql->getQuery()
            ->whereIn($sql->getSqlFld(), $rows_ids)
            ->get()
            ->toArray();

        $added_ids = [];

        $only_columns = array_filter($only_columns);

        $sql = new TableDataQuery($table);
        foreach ($rows as $row) {
            $data = !empty($only_columns)
                ? collect($row)->only($only_columns)->toArray()
                : $row;
            $data = $this->service->delSystemFields($data);
            $data = array_merge($data, $this->service->getModified($table), $this->service->getCreated($table));
            $data = $this->setSpecialValues($table, $data);
            $new_row_id = $sql->getQuery()->insertGetId($data);

            if ($new_row_id) {
                (clone $sql->getQuery())
                    ->where($sql->getSqlFld(), '=', $new_row_id)
                    ->update(['row_order' => $new_row_id]);
                $added_ids[ $row['id'] ] = $new_row_id;
            }
        }

        return $added_ids;
    }

    /**
     * Change Order Column
     *
     * @param $data
     * @return array
     */
    public function changeOrderColumn($data)
    {
        $selectId = (int)$data['select_user_header_id'];
        $targetId = (int)$data['target_user_header_id'];

        $table = Table::where('id', $data['table_id'])->first();
        $tableFildIds = TableField::where('table_id', $data['table_id'])->select('id')->get();

        if ($table->user_id != auth()->id()) {
            $this->reorderSysHeaders($tableFildIds);

            $orders = UserHeaders::whereIn('table_field_id', $tableFildIds)
                ->where('user_id', auth()->id());
        } else {
            $orders = TableField::whereIn('id', $tableFildIds);
        }

        $orders = $orders->orderBy('order')
            ->get()
            ->pluck('id')
            ->toArray();

        unset ($orders[array_search($selectId, $orders)]);

        $counter = 0;
        $newOrders = [];
        foreach ($orders as $order => $id) {
            if ($id == $targetId) {
                $newOrders[++$counter] = $selectId;
            }
            $newOrders[++$counter] = $id;
        }

        foreach ($newOrders as $order => $id) {
            if ($table->user_id != auth()->id()) {
                UserHeaders::where('id', $id)->update(['order' => $order]);
            } else {
                TableField::where('id', $id)->update(['order' => $order]);
            }
        }

        return ['change_status' => 1];
    }

    /**
     * @param Collection $tableFildIds
     */
    private function reorderSysHeaders(Collection $tableFildIds)
    {
        $sys_hdrs = UserHeaders::whereHas('_field', function ($q) {
            $q->whereIn('field', $this->service->c2m2_fields);
        })
            ->whereIn('table_field_id', $tableFildIds)
            ->where('user_id', auth()->id())
            ->get();
        foreach ($sys_hdrs as $hdr) {
            $hdr->update([
                'order' => count($tableFildIds)
            ]);
        }
    }

    /**
     * Change Row Order.
     *
     * @param Table $table
     * @param int $from_id
     * @param int $to_id
     * @return array
     */
    public function changeRowOrder(Table $table, int $from_id, int $to_id)
    {
        $orders = (new TableDataQuery($table))->getQuery()
            ->where('row_order', '<=', max($from_id, $to_id))
            ->where('row_order', '>=', min($from_id, $to_id))
            ->orderBy('row_order')
            ->get();

        $changer = ($from_id > $to_id ? 1 : -1);
        foreach ($orders as $order) {
            if ($order->row_order == $from_id) {
                (new TableDataQuery($table))->getQuery()
                    ->where('id', $order->id)
                    ->update([
                        'row_order' => $to_id
                    ]);
            } else {
                (new TableDataQuery($table))->getQuery()
                    ->where('id', $order->id)
                    ->update([
                        'row_order' => $order->row_order + $changer
                    ]);
            }
        }

        return ['change_status' => 1];
    }

    /**
     * Recalc Order Row.
     *
     * @param Table $table
     * @param int $order_recalc
     */
    public function recalcOrderRow($table, int $order_recalc)
    {
        if ( !($table instanceof Table) ) {
            $table = (new TableRepository())->getTableByDB($table);
        }
        (new TableDataQuery($table, true))
            ->getQuery()
            ->where('row_order', '=', $order_recalc)
            ->update([ 'row_order' => DB::raw('`id`') ]);
    }

    /**
     * Get SQL for loading Chart Data.
     *
     * @param Table $table
     * @param array $request_params
     * @param array $excluded_vals // ['verts','hors']
     * @param int|null $row_group_id
     * @return Builder|TableDataQuery
     */
    public function getSqlForChart(Table $table, array $request_params, array $excluded_vals, int $row_group_id = null)
    {
        //prepare table SQL
        $sql = new TableDataQuery($table, true, auth()->id());
        //apply searching, filtering, row rules to getRows
        $sql->testViewAndApplyWhereClauses($request_params, auth()->id());
        $sql->applySorting($request_params['sort'] ?? []);
        //apply 'row group' if selected
        if ($row_group_id) {
            $sql->applySelectedRowGroup($row_group_id);
        }
        $sql = $sql->getQuery();

        //apply excluded values
        $this->applyExcluded($sql, $excluded_vals['hors']);
        $this->applyExcluded($sql, $excluded_vals['verts']);
        //apply excluded values ^^^

        return $sql;
    }

    /**
     * @param Builder $sql
     * @param array $excluded_pairs : ['db_name1' => [v1,v2,v3], 'db_name2' => [q1,q2,q3]]
     */
    protected function applyExcluded(Builder $sql, array $excluded_pairs)
    {
        $fld1 = array_keys($excluded_pairs)[0] ?? '';
        $fld2 = array_keys($excluded_pairs)[1] ?? '';
        $values1 = $excluded_pairs[$fld1] ?? [];
        $values2 = $excluded_pairs[$fld2] ?? [];

        if ($fld1 && $fld2 && $values1 && $values2) {
            foreach ($values1 as $idx => $n) {
                $v1 = is_null($values1[$idx]) ? '' : $values1[$idx];
                $v2 = is_null($values2[$idx]) ? '' : $values2[$idx];
                $sql->where(function (Builder $inner) use ($fld1, $fld2, $v1, $v2) {
                    $inner->orWhere($fld1, '!=', $v1);
                    $inner->orWhere($fld2, '!=', $v2);
                });
            }
        }
        elseif ($fld1 && $values1) {
            $v = array_map(function ($el) { return is_null($el) ? '' : $el; }, $values1);
            $sql->whereNotIn($fld1, $v);
        }
    }

    /**
     * Get filters as independent part.
     *
     * @param int $table_id
     * @param array $data
     * @return array
     */
    public function getFilters(int $table_id, array $data)
    {
        $table = Table::find($table_id);
        $sql = new TableDataQuery($table);
        return (new TableDataFiltersModule($sql))
            ->getFilters($data);
    }

    /**
     * @param TableField $field
     * @param $old_val
     * @param $new_val
     */
    public function updateField(TableField $field, $old_val, $new_val)
    {
        (new TableDataQuery($field->_table))->getQuery()
            ->where($field->field, '=', $old_val)
            ->update([
                $field->field => $new_val,
                'row_hash' => Uuid::uuid4(),
            ]);

        //update hashes
        (new TableRepository())->onlyUpdateTable($field->_table->id, [
            'version_hash' => Uuid::uuid4()
        ]);
    }

    /**
     * Get Column Size.
     *
     * @param array $col
     * @return int
     */
    private function colSize($col)
    {
        $res = 255;
        if ($col) {
            switch ($col->f_type) {
                case 'Integer':
                case 'String':
                    $res = $col->f_size ?: 255;
                    break;
                case 'Decimal':
                case 'Currency':
                case 'Percentage':
                    $res = str_replace(',', '.', $col->f_size);
                    $res = array_sum(explode('.', $col->f_size)) + 1;
                    $res = $res > 1 ? $res : 255;
                    break;
            }
        }
        return $res;
    }
}
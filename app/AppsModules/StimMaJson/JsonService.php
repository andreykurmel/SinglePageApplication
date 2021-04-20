<?php

namespace Vanguard\AppsModules\StimMaJson;


use Illuminate\Support\Arr;
use Tablda\DataReceiver\TabldaDataInterface;
use Vanguard\Models\Table\Table;
use Vanguard\Models\Table\TableFieldLink;
use Vanguard\Modules\Settinger;
use Vanguard\Repositories\Tablda\FileRepository;
use Vanguard\Repositories\Tablda\TableData\MselectData;
use Vanguard\Repositories\Tablda\TableData\TableDataQuery;
use Vanguard\Repositories\Tablda\TableRepository;
use Vanguard\Services\Tablda\TableDataService;

/**
 * Class JsonService
 * @package Vanguard\AppsModules\StimMaJson
 * @inheritdoc "STIM MA JSON" in /readme.md
 */
class JsonService
{
    /**
     * @var \Tablda\DataReceiver\TabldaDataReceiver
     */
    protected $interface;
    /**
     * @var TableDataService
     */
    protected $data_serv;
    /**
     * @var array
     */
    protected $errors_present = [];
    /**
     * @var array
     */
    protected $warnings_present = [];
    /**
     * @var integer
     */
    protected $field_id;
    /**
     * @var integer
     */
    protected $max_lvl;
    /**
     * @var array
     */
    protected $json_obj = [];
    /**
     * @var array
     */
    protected $excluded_rc_tables = [];
    /**
     * @var string
     */
    protected $app_table = '';


    /**
     * JsonService constructor.
     */
    public function __construct()
    {
        $settings = Settinger::get('stim_ma_json');
        $this->interface = app(TabldaDataInterface::class, ['settings' => $settings]);
        $this->data_serv = new TableDataService();

        $appDatas = $this->interface->appDatas();
        foreach ($appDatas['_tables'] as $tbObject) {
            if (preg_match("/json_drill:false/i", $tbObject->options)) {
                $ex_tb = (new TableRepository())->getTableByDB( $tbObject->data_table );
                $this->excluded_rc_tables[ $ex_tb->id ] = $this->onlyExcludedFields($tbObject, $ex_tb);
            } else {
                $this->app_table = $this->interface->getTableWithMaps( $tbObject->app_table );
            }
        }
    }

    /**
     * @param $tbObject
     * @param Table $table
     * @return array
     */
    protected function onlyExcludedFields($tbObject, Table $table)
    {
        $app_table = $this->interface->getTableWithMaps( $tbObject->app_table );
        $fields_dbs = collect($app_table['_app_fields'])
            ->filter(function ($item) {
                return preg_match("/json_drill:false/i", $item->options);//case insensitive search
            })
            ->pluck('data_field')
            ->toArray();

        $result = [];
        if ($fields_dbs) {
            $table->load('_fields');
            foreach ($fields_dbs as $db) {
                $fld = $table->_fields->where('field', '=', $db)->first();
                if ($fld) {
                    $result[] = $fld->id;
                }
            }
        }
        return $result;
    }

    /**
     * @param $row_id
     * @return array
     */
    public function makeFile(int $row_id)
    {
        $max_lvl = $this->app_table['notes'] && preg_match('/^max_lvl:/', $this->app_table['notes'])
            ? intval( preg_replace('/^max_lvl:/', '', $this->app_table['notes']) )
            : 0;
        $this->max_lvl = $max_lvl ?: 3;

        $ma_table = (new TableRepository())->getTableByDB($this->app_table['data_table']);
        $this->loadRelations($ma_table);

        $row = (new TableDataQuery($ma_table))->getQuery()
            ->where('id', $row_id)
            ->first();

        if ($row) {

            $row = $row->toArray();
            $this->checkErrors($ma_table, $row);

            if (!$this->errors_present && $this->field_id) {
                $tree = $this->getTbTreePath($ma_table);

                $t = microtime(true);
                $this->json_obj = $this->setTableInObject($this->json_obj, $tree, $ma_table, $row);
                $this->setAdditionalTables($ma_table, $row, [], 1);

                $json_object = json_encode($this->json_obj, JSON_PRETTY_PRINT);
                $json_file = $row[$this->app_table['_app_maps']['model']] . '.json';
                (new FileRepository())->insertFileAlias($ma_table->id, $this->field_id, $row_id, $json_file, $json_object);
            }

        } else {
            $this->errors_present[] = 'Clicked Row not found!';
        }

        return [$this->errors_present, $this->warnings_present];
    }

    /**
     * @param Table $table
     */
    protected function loadRelations(Table $table)
    {
        $table->load([
            '_fields' => function ($f) {
                $f->with([
                        '_links' => function ($l) {
                            $l->with('_ref_condition');
                        }
                    ])
                    ->orderByDesc('is_floating')
                    ->orderBy('order');
            },
            '_link_initial_folder' => function ($i) {
                $i->with([
                    '_folder' => function ($i) {
                        $i->with('_root_folders');
                    },
                ]);
            },
        ]);
    }

    /**
     * @param Table $ma_table
     * @param array $row
     */
    protected function checkErrors(Table $ma_table, array $row)
    {
        $keys = ['usergroup', 'model', 'siteinfo', 'design', 'structure', 'pos2mbr', 'loading', 'json_file'];
        foreach ($keys as $k) {
            if (!$this->app_table['_app_maps'][$k] ?? null) {
                $this->errors_present[] = '"' . $k . '" not found in Correspondences/Fields';
            }
        }

        if (!$row[$this->app_table['_app_maps']['usergroup']] ?? null) {
            $this->errors_present[] = 'MA needs to be assigned to a user';
        }
        if (!$row[$this->app_table['_app_maps']['model']] ?? null) {
            $this->errors_present[] = 'Name can’t be left blank';
        }
        if (!$row[$this->app_table['_app_maps']['siteinfo']] ?? null) {
            $this->warnings_present[] = 'SiteInfo is blank';
        }
        if (!$row[$this->app_table['_app_maps']['design']] ?? null) {
            $this->warnings_present[] = 'Design is blank';
        }
        if (!$row[$this->app_table['_app_maps']['structure']] ?? null) {
            $this->warnings_present[] = 'Structure is blank';
        }
        if (!$row[$this->app_table['_app_maps']['pos2mbr']] ?? null) {
            $this->warnings_present[] = 'POS2MBR is blank';
        }
        if (!$row[$this->app_table['_app_maps']['loading']] ?? null) {
            $this->warnings_present[] = 'Loading is blank';
        }

        if (!$this->errors_present) {
            $header = $ma_table->_fields->where('field', $this->app_table['_app_maps']['json_file'])->first();
            $this->field_id = $header ? $header->id : null;
        }
    }

    /**
     * @param Table $ma_table
     * @return array
     */
    protected function getTbTreePath(Table $ma_table)
    {
        $init_folder = $ma_table->_link_initial_folder;
        $folder = $init_folder ? $init_folder->_folder : null;
        if ($folder) {
            $tree = $folder->_root_folders
                ->reverse()
                ->pluck('name')
                ->toArray();
        } else {
            $tree = [];
        }
        $tree[] = $ma_table->name;
        return $tree;
    }

    /**
     * @param array $json_object
     * @param array $tree_path
     * @param Table $ma_table
     * @param array $row
     * @return array
     */
    protected function setTableInObject(array $json_object, array $tree_path, Table $ma_table, array $row)
    {
        $el = array_shift($tree_path);
        if ($tree_path) {
            $part = $json_object[$el] ?? [];
            $json_object[$el] = $this->setTableInObject($part, $tree_path, $ma_table, $row);
        } else {
            $json_object[$el] = $this->rowToValues($ma_table, $row, $json_object[$el] ?? []);
        }
        return $json_object;
    }

    /**
     * @param Table $table
     * @param array $row
     * @param array $present
     * @return array
     */
    protected function rowToValues(Table $table, array $row, array $present)
    {
        $values = [];
        foreach ($table->_fields as $hdr) {
            if ($hdr->formula_symbol) {
                //value
                $tmp = [ 'value' => MselectData::tryParse($row[$hdr->field] ?? '') ];
                //unit
                if ($hdr->unit && $hdr->unit_ddl_id) {
                    $tmp['unit'] = $hdr->unit;
                }
                //tooltip
                if ($hdr->tooltip) {
                    $tmp['description'] = $hdr->tooltip;
                }
                //present nested Link
                if (!empty($present)) {
                    $keys = array_keys($present);
                    foreach ($keys as $k) {
                        $tbname = explode('_', $k);
                        $last = array_pop($tbname);
                        $tbname = implode('_', $tbname);
                        if ($hdr->id == $last) {
                            //Table as 'field value' can be only One (not array)
                            $tmp[ $tbname ] = !empty($present[$k][0]) ? $present[$k][0] : $present[$k];
                            unset($present[$k]);
                        }
                    }
                }
                //apply
                $values[$hdr->formula_symbol] = $tmp;
            }
        }
        return array_merge($present, $values);
    }

    /**
     * @param int $table_id
     * @param int $field_id
     * @return bool
     */
    protected function canProceed(int $table_id, int $field_id)
    {
        $present_ex = isset($this->excluded_rc_tables[$table_id]);
        if (!$present_ex) {
            return true;
        } else {
            $ex = $this->excluded_rc_tables[$table_id];
            if (!$ex) {
                return false;
            } else {
                return !in_array($field_id, $ex);
            }
        }
    }

    /**
     * @param Table $table
     * @param array $row
     * @param array $arr_tree
     * @param int $lvl
     */
    protected function setAdditionalTables(Table $table, array $row, array $arr_tree = [], int $lvl = 1)
    {
        if ($lvl > $this->max_lvl) {
            return;
        }

        foreach ($table->_fields as $link_header) {
            if ($this->canProceed($table->id, $link_header->id)) {
                foreach ($link_header->_links as $link) {
                    if ($link->link_type === 'Record' && $link->_ref_condition) {
                        $link_table = (new TableRepository())->getTable($link->_ref_condition->ref_table_id);
                        $this->loadRelations($link_table);

                        [$rows_count, $link_rows] = $this->data_serv->getFieldRows($link_table, $link->toArray(), $row);

                        //single record has "folder path" only if it is not in array of records
                        $full_tree = $arr_tree
                            ? array_merge( $arr_tree, [$link_table->name.'_'.$link_header->id] )
                            : $this->getTbTreePath($link_table);

                        if ($rows_count == 1) {
                            //attach single record
                            $this->setAdditionalTables($link_table, $link_rows[0], $arr_tree, $lvl + 1);
                            $this->json_obj = $this->setTableInObject($this->json_obj, $full_tree, $link_table, $link_rows[0]);
                        } elseif ($rows_count > 1) {
                            //attach array of records
                            foreach ($link_rows as $ridx => $l_row) {
                                $row_tree = array_merge($full_tree, [$ridx]);
                                $this->setAdditionalTables($link_table, $l_row, $row_tree, $lvl + 1);
                                $this->json_obj = $this->setTableInObject($this->json_obj, $row_tree, $link_table, $l_row);
                            }
                        }

                    }
                }
            }
        }
    }

}
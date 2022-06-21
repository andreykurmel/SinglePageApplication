<?php

namespace Vanguard\AppsModules\StimWid;


use Illuminate\Http\Request;
use Vanguard\AppsModules\StimWid\CreateRls\RlConnCreator;
use Vanguard\AppsModules\StimWid\Data\DataReceiver;
use Vanguard\AppsModules\StimWid\Data\UserPermisQuery;
use Vanguard\AppsModules\StimWid\Rts\RtsInterface;
use Vanguard\AppsModules\StimWid\Rts\RtsRotate;
use Vanguard\AppsModules\StimWid\Rts\RtsScale;
use Vanguard\AppsModules\StimWid\Rts\RtsTranslate;
use Vanguard\AppsModules\StimWid\SectionsParse\SectionsParser;
use Vanguard\Models\Table\Table;
use Vanguard\Repositories\Tablda\DDLRepository;
use Vanguard\Repositories\Tablda\TableData\TableDataQuery;
use Vanguard\Repositories\Tablda\TableData\TableDataRepository;
use Vanguard\Repositories\Tablda\TableData\TableDataRowsRepository;
use Vanguard\Repositories\Tablda\TableRepository;
use Vanguard\Services\Tablda\HelperService;
use Vanguard\Services\Tablda\TableDataService;

class PostService
{
    protected $already_copied_ids = [];
    protected $copied_row_corrs = [];

    protected $service;

    /**
     * PostService constructor.
     */
    public function __construct()
    {
        $this->service = new HelperService();
    }

    /**
     * @param Request $request
     * @return array
     */
    public function search_model(Request $request)
    {
        if (!$request->columns) {
            return ['rows' => [], 'rows_count' => 0, 'search_results_len' => 10];
        }

        $table_meta = DataReceiver::meta_table($request->app_table);

        $query = (new UserPermisQuery($table_meta));
        $query->applySearch([
            'search_words' => $request->string,
            'search_columns' => $request->columns,
        ]);
        $query = $query->getQuery();

        //order Models: first are owned
        $first_usr = $table_meta->_fields
            ->whereNotIn('field', $this->service->system_fields)
            ->where('f_type', '=', 'User')
            ->first();
        if ($first_usr && auth()->id()) {
            $query->orderByRaw('(`' . $first_usr->field . '` = ' . auth()->id() . ') DESC');
            $query->orderByRaw('(`' . $first_usr->field . '` like \'%"' . auth()->id() . '"%\') DESC');
        }

        //select just owned models.
        if ($first_usr && $request->just_owned) {
            $query->where($first_usr->field, '=', auth()->id());
        }

        $count = $query->count();
        $rows = $query->limit($table_meta->search_results_len ?: 10)->get();
        $rows = (new TableDataRowsRepository())->attachSpecialFields($rows, $table_meta, auth()->id());

        return [
            'rows_count' => $count,
            'rows' => $rows->values(),
            'search_results_len' => $table_meta->search_results_len ?: 10,
        ];
    }

    /**
     * @param Request $request
     * @return int[]
     * @throws \Exception
     */
    public function save_model(Request $request)
    {
        $res = 0;
        if ($request->app_table && $request->model) {
            $table_meta = DataReceiver::meta_table($request->app_table);
            $app = DataReceiver::app_table_and_fields($request->app_table);

            $model = array_map(function ($el) {
                return $el ?: '';
            }, $request->model);
            $tablda_row = (new StimSettingsRepository())->convertReceiverToTablda($app['_app_maps'], $model);

            if ($request->master_params) {
                $tablda_row = array_merge($tablda_row, $request->master_params);
                if ($request->remove_prev) {
                    (new TableDataService())->removeByParams($table_meta, $tablda_row);
                }
                $res = (new TableDataService())->insertRow($table_meta, $tablda_row, auth()->id());
            } else {
                $res = (new TableDataService())->updateRow($table_meta, (int)$tablda_row['id'], $tablda_row, auth()->id(), ['nohandler' => true]);
            }
        }
        return [ 'res' => $res ];
    }

    /**
     * @param array $colors
     * @return object
     */
    public function convertColorFormat(array $colors) : object
    {
        $result = [];
        foreach ($colors as $row) {
            $result[$row['name']] = [
                'init_val' => $row['color'],
                'model_val' => $row['color'],
                'key' => $row['name'],
                'check' => true,
            ];
        }
        return (object)$result;
    }

    /**
     * @param Request $request
     * @return array
     */
    public function copy_model(Request $request)
    {
        $table_meta = DataReceiver::meta_table($request->main_table);
        $row_id = $request->id;
        $new_ids = [];
        if ($row_id) {
            $this->already_copied_ids = [];
            $this->copied_row_corrs = [];
            $row_arr = (new TableDataRepository())->getDirectRow($table_meta, $row_id);
            if ($row_arr) {
                $row_arr = $row_arr->toArray();
                $replace_val = $this->replaceVal($request->main_table, $row_arr);
                $new_ids = $this->cpAppTable($request->main_table, $replace_val, [], $row_id);

                $additio_tbs = collect($request->additional_tables)->map(function ($el) {
                    return strtolower($el);
                });
                $additio_tbs = $additio_tbs->unique()->toArray();
                foreach ($additio_tbs as $app_tb) {
                    if ($app_tb) {
                        $this->cpAppTable($app_tb, $replace_val, $row_arr);
                    }
                }

                $additio_tbs[] = $request->main_table;
                $this->fillCopiedValShows($additio_tbs);
            }
        }
        return [
            'row' => count($new_ids) ? (new TableDataService())->getDirectRow($table_meta, $new_ids[0]) : null
        ];
    }

    /**
     * @param Request $request
     * @return string
     */
    public function copy_child(Request $request)
    {
        $table_meta = DataReceiver::meta_table($request->master_table);
        $child_meta = DataReceiver::meta_table($request->child_table);
        $row_arr = (new TableDataRepository())->getDirectRow($table_meta, $request->master_id);
        $target_arr = (new TableDataRepository())->getDirectRow($table_meta, $request->target_id);
        $new_ids=  [];
        if ($target_arr && $row_arr && $table_meta && $child_meta) {
            $target_arr = $target_arr->toArray();
            $row_arr = $row_arr->toArray();
            $this->already_copied_ids = [];

            $model = [
                'from' => $this->replaceVal($request->master_table, $row_arr),
                'to' => $this->replaceVal($request->master_table, $target_arr),
            ];
            $usergroup = [
                'from' => $this->usergroupVal($table_meta, $row_arr),
                'to' => $this->usergroupVal($table_meta, $target_arr),
            ];
            $replace_val = $this->modelReplaces($child_meta, $request->child_table, $model, $usergroup);

            $new_ids = $this->cpAppTable($request->child_table, $replace_val, $row_arr);
            $this->fillCopiedValShows([$request->child_table]);
        }
        return $new_ids;
    }

    /**
     * @param string $app_table
     * @param array $master_row
     * @return string
     */
    protected function replaceVal(string $app_table, array $master_row)
    {
        $prefix = $this->prefixField($app_table);
        if ($prefix) {
            return (string)$master_row[$prefix->data_field];
        } else {
            return '';
        }
    }

    /**
     * @param Table $app
     * @param array $master_row
     * @return string
     */
    protected function usergroupVal(Table $app, array $master_row)
    {
        $usr_fields = $this->usergroupField($app);
        if ($usr_fields->count()) {
            $pref = $usr_fields->first();
            return (string)$master_row[$pref->field];
        } else {
            return '';
        }
    }

    /**
     * @param string $app_table
     * @return mixed
     */
    protected function prefixField(string $app_table)
    {
        $table_info = DataReceiver::app_table_and_fields($app_table);
        return $table_info['_app_fields']
            ->filter(function ($fld) {
                return preg_match("/copy_prefix:true/i", $fld->options);
            })
            ->first();
    }

    /**
     * @param Table $app
     * @return mixed
     */
    protected function usergroupField(Table $app)
    {
        return $app->_fields
            ->where('f_type', '=', 'User')
            ->whereNotIn('field', $this->service->system_fields);
    }

    /**
     * @param Request $request
     * @return string
     */
    public function delete_model(Request $request)
    {
        $row_id = $request->id;
        if ($row_id) {
            $table_meta = DataReceiver::meta_table($request->main_table);
            $row_arr = (new TableDataRepository())->getDirectRow($table_meta, $row_id);
            if ($row_arr) {
                $row_arr = $row_arr->toArray();
                $this->delAppTable($request->main_table, [], $row_id);
                foreach ($request->additional_tables as $app_tb) {
                    if ($app_tb && !empty($app_tb['to_del'])) {
                        $this->delAppTable($app_tb['table'], $row_arr);
                    }
                }
            }
        }
        return ['id' => $row_id];
    }

    /**
     * @param string $app_table
     * @param string|array $replace_val
     * @param array $master_row
     * @param int $direct_id
     * @return array
     */
    protected function cpAppTable(string $app_table, $replace_val, array $master_row, int $direct_id = 0)
    {
        $table = DataReceiver::meta_table($app_table);

        if (in_array($table->id, $this->already_copied_ids)) {
            return [];
        }
        $this->already_copied_ids[] = $table->id;

        $request_params = [
            'table_id' => $table->id,
            'page' => 1,
            'rows_per_page' => 0,
            'force_execute' => true,
        ];

        $replaces = is_array($replace_val)
            ? $replace_val
            : $this->copyReplaces($table, $app_table, $replace_val);

        $new_ids = [];
        if ($direct_id) {
            $request_params['row_id'] = $direct_id;
            [$new_ids, $corr_rows] = (new TableDataService())->massCopy($table, [], $request_params, $replaces);
            $this->copied_row_corrs[$table->id] = $corr_rows;
        } else {
            [$search, $columns] = DataReceiver::build_search_array($app_table, $master_row);
            if ($search && $columns) {
                $request_params['search_words'] = $search;
                $request_params['search_columns'] = $columns;
                [$arr, $sub_corr_rows] = (new TableDataService())->massCopy($table, [], $request_params, $replaces);
                $this->copied_row_corrs[$table->id] = $sub_corr_rows;
            }
        }
        return $new_ids;
    }

    /**
     * @param array $app_tables
     */
    protected function fillCopiedValShows(array $app_tables)
    {
        $ddlRepo = new DDLRepository();
        foreach ($app_tables as $app_tb) {
            $table = DataReceiver::meta_table($app_tb);
            $table_query = new TableDataQuery($table);
            $copied_rows = (new TableDataRepository())->getDirectMassRows($table, $this->copied_row_corrs[$table->id] ?? []);

            $ddls_ids = $table->_ddls()->hasIdReferences()->get()->pluck('id')->toArray();

            $fields = $table->_fields()
                ->where('input_type', '!=', 'Input')
                ->whereIn('ddl_id', $ddls_ids)
                ->get();

            if ($ddls_ids && $fields && $copied_rows->count()) {
                $references = $ddlRepo->ddlReferencesWhichIds( $ddls_ids );
                //change referred IDs in 'show/val' DDLs
                foreach ($fields as $fld) {
                    $dref = $references->where('ddl_id', '=', $fld->ddl_id)->first();
                    $ref_tb_id = $dref ? $dref->_ref_condition->ref_table_id : '';
                    $ref_corr_rows = $this->copied_row_corrs[ $ref_tb_id ] ?? [];
                    if ($ref_corr_rows) {
                        foreach ($copied_rows as $row) {
                            if ($ref_corr_rows[ $row->{$fld->field} ] ?? null) {
                                $table_query->clearQuery();
                                $table_query->getQuery()
                                    ->where('id', '=', $row->id)
                                    ->update([
                                        $fld->field => $ref_corr_rows[$row->{$fld->field}],
                                    ]);
                            }
                        }
                    }
                }
                //-------
            }
        }
    }

    /**
     * @param Table $app
     * @param string $app_table
     * @param array $model
     * @param array $usergroup
     * @return array
     */
    protected function modelReplaces(Table $app, string $app_table, array $model, array $usergroup)
    {
        $replaces = $this->makeReplaces($app, $usergroup['to'], $usergroup['from']);
        //prefix fld
        $prefix = $this->prefixField($app_table);
        if ($prefix) {
            $replaces[] = [
                'force' => true,
                'field' => $prefix->data_field,
                'old_val' => $model['from'],
                'new_val' => $model['to'],
            ];
        }
        return $replaces;
    }

    /**
     * @param Table $app
     * @param string $app_table
     * @param string $val
     * @return array
     */
    protected function copyReplaces(Table $app, string $app_table, string $val)
    {
        $replaces = $this->makeReplaces($app);
        //prefix fld
        $prefix = $this->prefixField($app_table);
        if ($prefix) {

            $idx = 1;
            $qur = (new TableDataQuery($app))->getQuery();
            $cnt = (clone $qur)->where($prefix->data_field, '=', $this->get_copy_name($val, $idx))->count();
            while ($cnt) {
                $idx++;
                $cnt = (clone $qur)->where($prefix->data_field, '=', $this->get_copy_name($val, $idx))->count();
            }

            $replaces[] = [
                'force' => true,
                'field' => $prefix->data_field,
                'old_val' => $val,
                'new_val' => $this->get_copy_name($val, $idx),
            ];
        }
        return $replaces;
    }

    /**
     * @param Table $app
     * @param string|null $master_usergroup
     * @param null $old_val
     * @return array
     */
    protected function makeReplaces(Table $app, string $master_usergroup = null, $old_val = null)
    {
        $replaces = [];
        //User fields -> set as cur user
        $usr_fields = $this->usergroupField($app);
        foreach ($usr_fields as $usr) {
            $replaces[] = [
                'force' => true,
                'field' => $usr->field,
                'old_val' => $old_val ?: '',//all
                'new_val' => $master_usergroup ?: auth()->id(),
            ];
        }
        return $replaces;
    }

    /**
     * @param $val
     * @param $idx
     * @return string
     */
    protected function get_copy_name($val, $idx)
    {
        return 'copy'.($idx > 1 ? $idx : '').'_'.$val;
    }

    /**
     * @param string $app_table
     * @param array $master_row
     * @param int $direct_id
     */
    protected function delAppTable(string $app_table, array $master_row, int $direct_id = 0)
    {
        $table = DataReceiver::meta_table($app_table);

        $request_params = [
            'table_id' => $table->id,
            'page' => 1,
            'rows_per_page' => 0,
        ];

        if ($direct_id) {
            $request_params['row_id'] = $direct_id;
            (new TableDataService())->deleteAllRows($table, $request_params, $table->user_id, 'ignore');
        } else {
            [$search, $columns] = DataReceiver::build_search_array($app_table, $master_row);
            if ($search && $columns) {
                $request_params['search_words'] = $search;
                $request_params['search_columns'] = $columns;
                (new TableDataService())->deleteAllRows($table, $request_params, $table->user_id, 'ignore');
            }
        }
    }

    /**
     * @param Request $request
     * @return array
     */
    public function load_3d_rows(Request $request)
    {
        if ($request->type_3d == '3d:structure') {
            return (new Loader3D($request))->Structure();
        } elseif ($request->type_3d == '3d:ma') {
            return (new Loader3D($request))->MA();
        } elseif ($request->type_3d == 'wid_settings') {
            return (new Loader3D($request))->WidSettings();
        } else {
            return [];
        }
    }

    /**
     * @param Request $request
     * @return array
     */
    public function load_2d_data(Request $request)
    {
        if ($request->type == 'configurator') {
            return (new Data2D($request))->Configurator();
        } else {
            return [];
        }
    }

    /**
     * @param Request $request
     * @return array
     * @throws \Exception
     */
    public function rl_elem_store(Request $request)
    {
        $creator = new RlConnCreator();
        return ['error' => $creator->store_rl($request)];
    }

    /**
     * @param Request $request
     * @return array
     * @throws \Exception
     */
    public function rl_elem_delete(Request $request)
    {
        $creator = new RlConnCreator();
        return ['error' => $creator->delete_rl($request)];
    }

    /**
     * @param Request $request
     * @return array
     */
    public function store_eqpt_local_id(Request $request)
    {
        $creator = new RlConnCreator();
        return ['error' => $creator->store_eqpt_local_id($request)];
    }

    /**
     * @param Request $request
     * @return array
     */
    public function do_rts(Request $request)
    {
        if (!$request->app_table || !$request->request_params || !$request->rts_params) {
            return ['error' => 'Incorrect request'];
        }

        if ($request->rts_type == 'rotate') {
            $rts = new RtsRotate($request->rts_params);
            $err = $this->changeRows($request->app_table, $request->request_params, $rts);
        } elseif ($request->rts_type == 'translate') {
            $rts = new RtsTranslate($request->rts_params);
            $err = $this->changeRows($request->app_table, $request->request_params, $rts);
        } elseif ($request->rts_type == 'scale') {
            $rts = new RtsScale($request->rts_params);
            $err = $this->changeRows($request->app_table, $request->request_params, $rts);
        } else {
            $err = 'Incorrect RTS type';
        }
        return ['error' => $err];
    }

    /**
     * @param string $app_table
     * @param array $req_params
     * @param RtsInterface $rts
     * @return string
     */
    protected function changeRows(string $app_table, array $req_params, RtsInterface $rts)
    {
        $err = $rts->set_fields($app_table);
        $table = (new TableRepository())->getTableByDB( $rts->get_app_tb()['data_table'] );
        if (!$err && $table) {
            $sql = new TableDataQuery($table);
            $sql->applyWhereClause($req_params, auth()->id());
            $sql = $sql->getQuery();

            $len = $sql->count();
            $chunk = 100;
            $repo = new TableDataRepository();

            try {
                for ($i = 0; $i*$chunk < $len; $i++) {
                    $rows = (clone $sql)->offset($i * $chunk)->limit($chunk)->get();
                    foreach ($rows as $row) {
                        $fields = $rts->calc_row( $row->toArray() );
                        $repo->quickUpdate($table, $row->id, $fields);
                    }
                }
            } catch (\Exception $e) {
                return 'Rows changing error';
            }
            return '';
        } else {
            return $err;
        }
    }

    /**
     * @param Request $request
     * @return array
     */
    public function do_sec_parse(Request $request)
    {
        $parser = new SectionsParser($request->app_table, $request->request_params);
        return ['error' => $parser->do_parse()];
    }

    /**
     * @param Request $request
     * @return array
     */
    public function eqpt_fill_attachments(Request $request)
    {
        $parser = new EqptFillAttachments($request->app_table, $request->request_params);
        return ['error' => $parser->do_action()];
    }

    /**
     * @param Request $request
     * @return array
     */
    public function load_aisc(Request $request)
    {
        return [
            'aisc' => DataReceiver::mapped_query('AISC')
                ->whereIn('AISC_Size2', $request->sizes_array)
                ->get()
        ];
    }
}
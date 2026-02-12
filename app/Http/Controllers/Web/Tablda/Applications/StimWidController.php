<?php

namespace Vanguard\Http\Controllers\Web\Tablda\Applications;

use Exception;
use Illuminate\Contracts\View\Factory;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\View\View;
use Vanguard\AppsModules\StimWid\Data\DataReceiver;
use Vanguard\AppsModules\StimWid\Data\UserPermisQuery;
use Vanguard\AppsModules\StimWid\PostService;
use Vanguard\AppsModules\StimWid\StimAppViewRepository;
use Vanguard\AppsModules\StimWid\StimSettingsRepository;
use Vanguard\Classes\TabldaUser;
use Vanguard\Http\Controllers\Controller;
use Vanguard\Http\Controllers\Web\Tablda\Applications\Transfers\DirectCallInput;
use Vanguard\Http\Controllers\Web\Tablda\Applications\Transfers\DirectCallOut;
use Vanguard\Models\Correspondences\CorrespApp;
use Vanguard\Models\Correspondences\CorrespTable;
use Vanguard\Models\Correspondences\StimAppView;
use Vanguard\Models\Correspondences\StimAppViewFeedback;
use Vanguard\Models\Correspondences\StimAppViewFeedbackResult;
use Vanguard\Policies\TableDataPolicy;
use Vanguard\Repositories\Tablda\FileRepository;
use Vanguard\Repositories\Tablda\TableRepository;
use Vanguard\Services\Tablda\BladeVariablesService;
use Vanguard\Services\Tablda\HelperService;
use Vanguard\Services\Tablda\TableDataService;

class StimWidController extends Controller implements AppControllerInterface
{

    private $bladeVariablesService;
    private $stimRepo;
    private $viewRepo;
    private $fileRepository;

    /**
     * StimWidController constructor.
     * @param BladeVariablesService $bladeVariablesService
     */
    public function __construct(BladeVariablesService $bladeVariablesService)
    {
        $this->bladeVariablesService = $bladeVariablesService;
        $this->bladeVariablesService->is_app_route = 1;
        $this->stimRepo = new StimSettingsRepository();
        $this->viewRepo = new StimAppViewRepository();
        $this->fileRepository = new FileRepository();
    }

    /**
     * @param Request $request
     * @param CorrespApp $correspApp
     * @return Factory|View
     */
    public function get(Request $request, CorrespApp $correspApp)
    {
        $stim_settings = $this->globalStimSettings();

        $appview = $this->viewRepo->getByHash($request->view);

        $c_tab = $appview->v_tab ?? $request->tab ?? '';
        $c_select = $appview->v_select ?? $request->sel ?? '';
        $tab_table = $this->getInitTable($stim_settings['tabs'], $c_tab, $c_select);

        $model = $this->findInitModel($tab_table, $request->all(), $appview);

        $stim_link_params = [];
        $repo = new TableRepository();
        foreach ($correspApp->_tables as $tb_info) {
            try {
                $table_meta = $repo->getTableByDB($tb_info->data_table);

                $appTb = DataReceiver::app_table_and_fields($tb_info->app_table ?: '');

                if (!$table_meta || !$appTb) {
                    continue;
                }

                $logo_fld = $this->stimRepo->getDataFields($appTb['_app_fields'], 'logo:in_select', false, true);

                $stim_link_params[$tb_info->app_table] = [
                    'app_table' => strtolower($tb_info->app_table),
                    'table_id' => $table_meta->id,
                    'rows_per_page' => $table_meta->rows_per_page,
                    'link_fields' => DataReceiver::get_link_fields($appTb['_app_fields']),
                    'avail_cols_for_app' => $this->stimRepo->getDataFields($appTb['_app_fields'], 'show:true'),
                    'top_columns_show' => $this->stimRepo->getDataFields($appTb['_app_fields'], 'display_top:true'),
                    'in_url_elements' => $this->stimRepo->getDataFields($appTb['_app_fields'], 'in_url:true', true),
                    'name_field' => $this->stimRepo->getDataFields($appTb['_app_fields'], 'is_fld:name', false, true),
                    'on_edit_changers' => $this->stimRepo->getDataFields($appTb['_app_fields'], 'on:edit', true),
                    'filters_active' => preg_match('/filter:true/i', $tb_info->options),
                    'download_active' => preg_match('/download:true/i', $tb_info->options),
                    'halfmoon_active' => preg_match('/halfmoon:true/i', $tb_info->options),
                    'condformat_active' => preg_match('/cond_format:true/i', $tb_info->options),
                    'has_viewpop_active' => preg_match('/view_popup:true/i', $tb_info->options),////////////////
                    'cellheight_btn_active' => preg_match('/cell_height:true/i', $tb_info->options),/////////////
                    'string_replace_active' => preg_match('/string_replace:true/i', $tb_info->options),/////////////
                    'copy_childrene_active' => preg_match('/copy_from_model:true/i', $tb_info->options),/////////////
                    'app_table_options' => $appTb['options'],
                    'app_maps' => $appTb['_app_maps'],
                    'app_fields' => $appTb['_app_fields'],
                    'logo_replace_field' => $logo_fld ?: '',
                ];
            } catch (Exception $e) {
            }
        }

        $feedback = $request->f
            ? $this->viewRepo->getFeedbackBy('part_uuid', $request->f)
            : null;

        $appview ? $appview->_for_user_or_active = true : null;
        $vars = $this->bladeVariablesService->getVariables();
        $vars['route_group'] = 'stim_wid';
        $vars['user']['_app_users_views'] = $this->viewRepo->loadUsersViews();
        $vars['user']['see_view'] = !!$appview;
        $vars['user']['view_hash'] = $appview ? $appview->hash : '';
        $vars['user']['view_locked'] = $appview && $appview->is_locked;
        $vars['user']['view_all'] = $appview;
        $vars['user']['_app_cur_view'] = $appview;
        $stim_settings['_app_cur_view'] = $appview;
        $stim_settings['__selected_feedback'] = $feedback;
        $stim_settings['__feedback_results'] = $feedback ? $this->viewRepo->loadResults($feedback) : [];

        return view('tablda.applications.stim-wid', array_merge(
            $vars,
            [
                'load_three_3d' => true,
                'init_tab' => strtolower($c_tab),
                'init_model' => $model,
                'stim_link_params' => $stim_link_params,
                'init_select' => strtolower($c_select),
                'stim_settings' => $stim_settings,
            ]
        ));
    }

    /**
     * @return array
     */
    protected function globalStimSettings()
    {
        $rows = $this->stimRepo->get();

        $masters = [];

        $result = $rows->groupBy('top_tab_low')->map(function ($item) {
            return $item->groupBy('select_low');
        });
        foreach ($result as $tab => $arr) {
            if ($tab) {
                foreach ($arr as $select => $grouped) {
                    $master = $this->firstFilter($grouped, 'is_master:true', 'options');
                    if (!$master || !$master->db_table) {
                        continue;
                    }
                    $result[$tab][$select] = [
                        'tables' => $grouped,
                        'master_id' => $master->id ?: '',
                        'master_table' => $master->table ?: '',
                        'master_obj' => $master,
                        'type_3d' => $master->model_3d ?: '',
                        'init_top' => $master->top_tab ?: '',
                        'init_select' => $master->select ?: '',
                        'tab_style' => $master->style ?: '',
                        'del_child_tbls' => $this->stimRepo->getAllInherits($master->db_table, 'deletable:false') ?: [],
                        'copy_child_tbls' => $this->stimRepo->getAllInherits($master->db_table, 'copy_prefix:true') ?: [],
                    ];
                    $masters[] = $master->table ?: '';
                }
            }
        }

        $popups_models = [
            'equipment' => strtolower($this->stimRepo->findPopupTb('equipment')),
            'nodes' => strtolower($this->stimRepo->findPopupTb('nodes')),
            'members' => strtolower($this->stimRepo->findPopupTb('members')),
            'lcs' => strtolower($this->stimRepo->findPopupTb('lcs')),
            'feedline' => strtolower($this->stimRepo->findPopupTb('feedline')),
            'rls' => strtolower($this->stimRepo->findPopupTb('rls')),
        ];

        return [
            'tabs' => $result,
            'plain_settings' => $rows,
            'master_tables' => $masters,
            'popups_models' => $popups_models,
        ];
    }

    /**
     * @param Collection $app_fields
     * @param string $match
     * @param string $option
     * @param string $key
     * @return mixed
     */
    protected function firstFilter(Collection $app_fields, string $match, string $option, string $key = '')
    {
        $result = $app_fields->filter(function ($fld) use ($match, $option) {
            return preg_match("/$match/i", $fld->{$option});
        })
            ->first();

        if ($key && $result) {
            $result = $result->{$key};
        }

        return $result;
    }

    /**
     * @param Collection $stim_tabs
     * @param string $tab
     * @param string $sel
     * @return string
     */
    protected function getInitTable(Collection $stim_tabs, string $tab, string $sel)
    {
        $group = $stim_tabs[strtolower($tab)][strtolower($sel)] ?? null;
        return (string)($group ? ($group['master_table'] ?? '') : '');
    }

    /**
     * @param string $tab_table
     * @param array $request
     * @return mixed
     */
    protected function findInitModel(string $tab_table, array $request, $appview)
    {
        if (!$tab_table) {
            return null;
        }

        $user = TabldaUser::get($request);
        $meta_table = DataReceiver::meta_table($tab_table);
        if (!(new TableDataPolicy())->load($user, $meta_table, $request)) {
            return null;
        }

        if ($appview) {
            $row = (new TableDataService())->getDirectRow($meta_table, $appview->master_row_id);
        } else {
            $app_table = DataReceiver::app_table_and_fields($tab_table);
            $in_urls = $this->stimRepo->getDataFields($app_table['_app_fields'], 'in_url:true', true);

            $model = (new UserPermisQuery($meta_table))->getQuery();
            foreach ($in_urls as $fld) {
                if (isset($request[$fld->app_field])) {
                    $can = 1;
                    $operator = !empty($request['range']) ? 'like' : '=';
                    $val = !empty($request['range']) ? '%' . $request[$fld->app_field] . '%' : $request[$fld->app_field];
                    $model->where($fld->data_field, $operator, $val);
                }
            }

            $row = null;
            if (isset($can)) {
                $found = $model->first();
                if ($found) {
                    //attach files and special fields
                    $row = (new TableDataService())->getDirectRow($meta_table, $found->id);
                }
            }
        }

        if ($row && !empty($request['range'])) {
            $row['_virtual_mr'] = true;
        }
        return $row;
    }

    /**
     * @param Request $request
     * @return array
     * @throws Exception
     */
    public function post(Request $request)
    {
        $method = $request->query('method');
        return (new PostService())->{$method}($request);
    }

    /**
     * @param DirectCallInput $input
     * @return DirectCallOut
     */
    public function direct_call(DirectCallInput $input)
    {
        return new DirectCallOut();
    }


    //VIEWS

    public function stateAppView(Request $request)
    {
        $view = $this->viewRepo->getAppView($request->view_id);
        $new_view = '';
        if ($view->state != $request->state) {
            $new_view = $this->viewRepo->loadAppViewWithRelations($view->id);
        }
        return ['new_view' => $new_view];
    }

    /**
     * @param Request $request
     * @return StimAppView
     * @throws Exception
     */
    public function insertAppView(Request $request)
    {
        if (!auth()->id()) {
            throw new Exception('Not available');
        }
        return $this->viewRepo->insertAppView($request->fields);
    }

    /**
     * @param Request $request
     * @return bool
     * @throws Exception
     */
    public function updateAppView(Request $request)
    {
        $view = $this->viewOwner($request->view_id);
        return $this->viewRepo->updateAppView($request->view_id, array_merge($request->fields, ['user_id' => auth()->id()]));
    }

    /**
     * @param int $view_id
     * @return mixed
     * @throws Exception
     */
    protected function viewOwner(int $view_id)
    {
        $view = $this->viewRepo->getAppView($view_id);
        if ($view->user_id != auth()->id()) {
            throw new Exception('Not available');
        }
        return $view;
    }

    /**
     * @param Request $request
     * @return array
     * @throws Exception
     */
    public function deleteAppView(Request $request)
    {
        $view = $this->viewOwner($request->view_id);
        return ['status' => $this->viewRepo->deleteAppView($request->view_id)];
    }


    //VIEW FEEDBACKS

    /**
     * @param Request $request
     * @return mixed
     * @throws Exception
     */
    public function insertAppViewFeedback(Request $request)
    {
        $app = $this->viewOwner($request->view_id);
        $view = $this->viewRepo->insertAppViewFeedback($app, $request->fields);
        return $view->_feedbacks;
    }

    /**
     * @param Request $request
     * @return \Illuminate\Database\Eloquent\Collection|StimAppViewFeedback[]
     * @throws Exception
     */
    public function updateAppViewFeedback(Request $request)
    {
        $app = $this->viewOwner($request->view_id);
        $view = $this->viewRepo->updateAppViewFeedback($app, $request->fields);
        return $view->_feedbacks;
    }

    /**
     * @param Request $request
     * @return array
     * @throws Exception
     */
    public function deleteAppViewFeedback(Request $request)
    {
        $app = $this->viewOwner($request->view_id);
        $view = $this->viewRepo->deleteAppViewFeedback($app, $request->feedback_id);
        return $view->_feedbacks;
    }


    //VIEW FEEDBACK RESULTS

    /**
     * @param Request $request
     * @return bool[]
     */
    public function checkAppViewFeedbackPass(Request $request)
    {
        $feedback = $this->viewRepo->getFeedback($request->feedback_id);
        return ['status' => $feedback->request_pass == $request->pass];
    }

    /**
     * @param Request $request
     * @return Collection
     */
    public function insertAppViewFeedbackResult(Request $request)
    {
        $feedback = $this->viewRepo->getFeedback($request->feedback_id);
        $result = $this->viewRepo->insertAppViewFeedbackResult($feedback, $request->fields);
        if ($request->fields['_tmp_id'] ?? '') {
            $table = (new TableRepository())->getTableByDB('stim_app_view_feedback_results');
            $this->fileRepository->storeTempFiles($table, $request->fields['_tmp_id'], $result->id);
        }
        return $this->viewRepo->loadResults($feedback);
    }

    /**
     * @param Request $request
     * @return \Illuminate\Database\Eloquent\Collection|StimAppViewFeedbackResult[]
     * @throws Exception
     */
    public function updateAppViewFeedbackResult(Request $request)
    {
        $feedback = $this->viewRepo->getFeedback($request->feedback_id);
        $app = $this->viewOwner($feedback->stim_view_id);
        $view = $this->viewRepo->updateAppViewFeedbackResult($feedback, $request->fields);
        return $view->_results;
    }

    /**
     * @param Request $request
     * @return array
     * @throws Exception
     */
    public function deleteAppViewFeedbackResult(Request $request)
    {
        $feedback = $this->viewRepo->getFeedback($request->feedback_id);
        $app = $this->viewOwner($feedback->stim_view_id);
        $view = $this->viewRepo->deleteAppViewFeedbackResult($feedback, $request->result_id);
        return $view->_results;
    }

    /**
     * @param Request $request
     * @return \Illuminate\Database\Eloquent\Collection|StimAppViewFeedback[]
     * @throws Exception
     */
    public function sendFeedbackEmail(Request $request)
    {
        $feedback = $this->viewRepo->getFeedback($request->feedback_id);
        $app = $this->viewOwner($feedback->stim_view_id);

        $this->viewRepo->sendFeedbackEmail($feedback);
        $view = $this->viewRepo->setFeedbackSent($feedback, $request->fields);
        return $view->_feedbacks;
    }
}

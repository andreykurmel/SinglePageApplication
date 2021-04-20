<?php

namespace Vanguard\Http\Controllers\Web\Tablda;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Session;
use Ramsey\Uuid\Uuid;
use Vanguard\Classes\DiscourseSso;
use Vanguard\Http\Controllers\Controller;
use Vanguard\Jobs\AppSendMail;
use Vanguard\Models\Table\Table;
use Vanguard\Models\Table\TableUsedCode;
use Vanguard\Models\Table\TableView;
use Vanguard\Policies\TableDataPolicy;
use Vanguard\Repositories\Tablda\FileRepository;
use Vanguard\Repositories\Tablda\TableViewRepository;
use Vanguard\Services\Tablda\BladeVariablesService;
use Vanguard\Services\Tablda\FolderService;
use Vanguard\Services\Tablda\TableDataService;
use Vanguard\Services\Tablda\TableService;
use Vanguard\User;

class AppController extends Controller
{
    private $folderService;
    private $tableService;
    private $tableDataService;
    private $tableViewRepository;
    private $variablesService;

    /**
     * AppController constructor.
     *
     * @param FolderService $folderService
     * @param TableService $tableService
     * @param TableViewRepository $tableViewRepository
     * @param TableDataService $tableDataService
     * @param BladeVariablesService $variablesService
     */
    public function __construct(
        FolderService $folderService,
        TableService $tableService,
        TableViewRepository $tableViewRepository,
        TableDataService $tableDataService,
        BladeVariablesService $variablesService
    )
    {
        $this->folderService = $folderService;
        $this->tableService = $tableService;
        $this->tableDataService = $tableDataService;
        $this->tableViewRepository = $tableViewRepository;
        $this->variablesService = $variablesService;
    }

    /**
     * Tablda Data
     *
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function index()
    {
        return view('tablda.data', array_merge(
            $this->variablesService->getVariables(),
            [
                'type' => 'table',
                'object' => (Object)['id' => 0, 'name' => ''],
                'filters' => [],
            ]
        ));
    }

    /**
     * Tablda Data
     *
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function blog()
    {
        return view('tablda.blog', array_merge(
            $this->variablesService->getVariables(),
            [
                'lightweight' => true,
            ]
        ));
    }

    /**
     * @param Request $request
     * @return array
     */
    public function user_state(Request $request)
    {
        return [
            'changed' => $request->app_user_id != auth()->id(),
        ];
    }

    /**
     * Ping server
     *
     * @return string
     */
    public function ping(Request $request)
    {
        $result_message = '';
        $error_code = 0;

        try {
            $ses = \DB::connection('mysql')
                ->table('sessions')
                ->where('id', Session::getId())
                ->first();

            $request->pathname = preg_replace('/%20/i', ' ', $request->pathname);

            if (
                $request->app_user['vendor_script'] != mix('assets/js/tablda/vendor.js')->toHtml()
                ||
                $request->app_user['app_script'] != mix('assets/js/tablda/app.js')->toHtml()
            ) { //new scripts version
                $result_message = config('app.debug') ? '' : 'Page reloading needed.';
            }
            elseif ($ses && $ses->user_id && $ses->disconnected) // session disconnected
            {
                $usr_id = auth()->id();
                Auth::logout();
                (new DiscourseSso())->syncUnlog($usr_id);
                $result_message = 'You have been automatically logged out from another terminal.';
            }
            elseif ($request->app_user && $request->app_user['id'] && !auth()->id()) // session expired on back-end
            {
                Log::info('!!!---Session Expired---!!!');
                /*$error_code = 1;
                $result_message = 'Session expired. Please login';*/
            }
            elseif (preg_match('/^\/dcr\//i', $request->pathname)) // data request closed
            {
                $code = preg_replace('/^\/dcr\//i', '', $request->pathname);
                if (!$this->tableService->tableRequest($code)) {
                    $result_message = 'Data Collection Request (DCR) module has been inactivated.';
                }
            }
        } catch (\Exception $e) {
        }
        setcookie('ping_message', $result_message);

        return [
            'error_code' => $error_code, //1 - needs page reloading
            'error' => $result_message,
            'memutree_hash' => auth()->user() ? auth()->user()->memutree_hash : '',
        ];
    }

    /**
     * Table page.
     *
     * @param $table_path
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\Http\RedirectResponse|\Illuminate\Routing\Redirector|\Illuminate\View\View
     */
    public function table($table_path, Request $request)
    {
        $object = $this->tableService->objectExists($table_path);
        if ($object['id']) {

            //Table
            if ($object['type'] == 'table') {
                $data = $this->loadTablePage($object['id'], $object['type'], $request);
            } //Folder
            else {
                $data = $this->variablesService->getVariables();
                $data['type'] = $object['type'];
                $data['object'] = $this->folderService->getFolderMeta($object['id'], auth()->id());
                $data['filters'] = [];
            }

            return view('tablda.data', $data);
        } else {
            if ($request->i) {
                return redirect(route('visiting', ['table_id' => $request->i]));
            } else {
                return redirect(route('data'));
            }
        }
    }

    /**
     * @param int $id
     * @param string $type
     * @param Request $request
     * @return array
     */
    protected function loadTablePage(int $id, string $type, Request $request)
    {
        $tb = $this->tableService->getTable($id);
        $data = $this->variablesService->setTableObj($tb)->getVariables();
        $data['type'] = $type;
        $data['object'] = $tb;
        $this->tableService->autoEnableFilters($data['object'], $request->except('view'), auth()->id());
        $data['filters'] = $this->tableDataService->getAsAppliedFilters($id, $request->except('view'));

        //if View editing -> load TableView's status
        if ($request->view) {
            $view = $this->tableViewRepository->getByName($id, auth()->id(), $request->view);
            if ($view) {
                $data['user']->selected_view = $view->hash;
                $queryPreset = json_decode($view->data, true);
                $data['filters'] = $queryPreset['applied_filters'];
                $data['panels_preset'] = $queryPreset['panels_preset'] ?? null;
            }
        }
        return $data;
    }

    /**
     * Table page by Id.
     *
     * @param $table_id
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\Http\RedirectResponse|\Illuminate\Routing\Redirector|\Illuminate\View\View
     */
    public function tableId($table_id, Request $request)
    {
        $tid = $tb = null;
        if (!TableUsedCode::where('code', $table_id)->first()) {
            $tid = preg_replace('/:.*/i', '', $table_id);
            $tid = preg_replace('/[^\d]/i', '', $tid);
            $tb = $this->tableService->getTable($tid);
        }

        if ($tid && $tb) {
            //TableUsedCode::insert(['code' => $table_id]);
            $visView = (new TableViewRepository())->getSys($tid);
            if ($visView) {
                return $this->view($visView->hash, $request);
            } else {
                $data = $this->loadTablePage($tid, 'table', $request);
                return view('tablda.data', $data);
            }
        } else {
            return redirect(route('data'));
        }
    }

    /**
     * View page.
     *
     * @param $view
     * @param Request $request
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\Http\RedirectResponse|\Illuminate\Routing\Redirector|\Illuminate\View\View
     */
    public function view($view, Request $request)
    {
        $user = auth()->check() ? auth()->user() : new User();
        $viewObj = $this->tableService->viewExists($view);
        //Is TableView
        if ($viewObj) {

            $queryPreset = json_decode($viewObj->data, true);
            $tb = $this->tableService->getTable($viewObj['table_id']);

            return view('tablda.data', array_merge(
                $this->variablesService->setTableObj($tb, $viewObj->_user)->getVariables($viewObj),
                [
                    'type' => 'table',
                    'object' => $tb,
                    'filters' => [],
                    'panels_preset' => $queryPreset['panels_preset'] ?? null,
                ]
            ));
        }

        $viewObj = $this->folderService->folderViewExists($view);
        //Is FolderView
        if ($viewObj && $viewObj->is_active) {
            $vars = $this->variablesService->getVariables();
            $vars['user']->see_view = true;
            $vars['user']->view_locked = !!$viewObj->is_locked;
            $vars['user']->view_all = $viewObj;
            $vars['user']->_is_folder_view = $viewObj->hash;

            $folder = $viewObj->_folder;
            $checked_tables = $viewObj->_checked_tables->pluck('id') ?? [0];
            $this->folderService->getSubTree($folder, $checked_tables);

            $table_id = ($request->table_id ?: $viewObj->def_table_id);
            $object = $table_id
                ?
                $this->tableService->getTable($table_id)
                :
                (Object)['id' => 0, 'name' => ''];

            return view('tablda.data', array_merge(
                $vars,
                [
                    'type' => 'table',
                    'object' => $object,
                    'filters' => [],
                    'tree' => collect(['folder_view' => $folder->_sub_tree]),
                ]
            ));
        }

        return redirect(route('homepage'));
    }

    /**
     * Embed table page.
     *
     * @param $hash
     * @param Request $request
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View|string
     */
    public function embed($hash, Request $request)
    {
        $user = auth()->check() ? auth()->user() : new User();
        $tb = $this->tableService->getTableByHash($hash);
        $view = $this->tableService->viewExists($hash);
        if ($tb) {
            $data = $this->variablesService->setTableObj($tb)->getVariables(null, 1);
            $data['type'] = 'table';
            $data['object'] = $tb;
            $data['filters'] = [];

            return view('tablda.data', $data);
        } elseif ($view && ($view->is_active || $user->id)) {
            $tbview = $this->tableService->getTable($view['table_id']);
            $data = $this->variablesService->setTableObj($tbview, $view->_user)->getVariables($view, 1);
            $data['type'] = 'table';
            $data['object'] = $tbview;
            $data['filters'] = [];

            $queryPreset = json_decode($view->data, true);
            $data['panels_preset'] = $queryPreset['panels_preset'] ?? null;

            return view('tablda.data', $data);
        }

        $viewObj = $this->folderService->folderViewExists($hash);
        if ($viewObj && $viewObj->is_active) {
            $vars = $this->variablesService->getVariables(null, 1);
            $vars['user']->see_view = true;
            $vars['user']->view_locked = !!$viewObj->is_locked;
            $vars['user']->_is_folder_view = $viewObj->hash;

            $folder = $viewObj->_folder;
            $checked_tables = $viewObj->_checked_tables->pluck('id') ?? [0];
            $this->folderService->getSubTree($folder, $checked_tables);

            $object = $request->table_id
                ?
                $this->tableService->getTable($request->table_id)
                :
                (Object)['id' => 0, 'name' => ''];

            return view('tablda.data', array_merge(
                $vars,
                [
                    'type' => 'table',
                    'object' => $object,
                    'filters' => [],
                    'tree' => collect(['folder_view' => $folder->_sub_tree]),
                ]
            ));
        }

        return redirect(route('homepage'));
    }

    /**
     * Table Request Embed page.
     *
     * @param $code
     * @param Request $request
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\Http\RedirectResponse|\Illuminate\Routing\Redirector|\Illuminate\View\View
     */
    public function tableRequest($code, Request $request)
    {
        $permission = $this->tableService->tableRequest($code);
        if ($permission) {

            $permission->load('_link_limits');
            $tb = $this->tableService->getTable($permission['table_id']);
            $data = $this->variablesService->setTableObj($tb, $permission->getOwner())->getVariables(null, 1);
            $data['type'] = 'table';
            $data['object'] = $tb;
            $data['tablePermission'] = $permission;
            $data['is_embed'] = preg_match('/embed-dcr/i', $request->path());
            $data['user']->_dcr_hash = $permission->dcr_hash;

            return view('tablda.table-request', $data);
        } else {
            return redirect(route('data'));
        }
    }

    /**
     * Home Page
     *
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function home()
    {
        return view('tablda.homepage', array_merge(
            $this->variablesService->getVariables(),
            ['route_group' => 'homepage',]
        ));
    }

    /**
     * Terms of Service Page
     *
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function terms()
    {
        return view('tablda.terms', $this->variablesService->getVariables());
    }

    /**
     * Privacy Page
     *
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function privacy()
    {
        return view('tablda.privacy', $this->variablesService->getVariables());
    }

    /**
     * Disclaimer Page
     *
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function disclaimer()
    {
        return view('tablda.disclaimer', $this->variablesService->getVariables());
    }

    /**
     * Send Mail
     *
     * @param Request $request
     * @return mixed
     */
    public function sendMail(Request $request)
    {
        if ($request->email && $request->message) {
            $files = [];
            if ($request->hasFile('attach')) {
                $tmp_name = Uuid::uuid4();
                $fullpath = $request->attach->storeAs('tmp', $tmp_name);

                $files[] = [
                    'path' => storage_path('app/' . $fullpath),
                    'name' => $request->attach->getClientOriginalName()
                ];
            }
            $this->dispatch(new AppSendMail($request->except('attach'), $files));
        }
        return $request->message;
    }

    /**
     * @param Request $request
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function discourseSSO(Request $request)
    {
        return (new DiscourseSso())->getLogin($request);
    }

    /**
     * @param Request $request
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function discourseRedirect(Request $request)
    {
        return redirect(DiscourseSso::communityUrl());
    }

    /**
     * Log the user out of the application.
     *
     * @return \Illuminate\Http\Response
     */
    public function getDiscourseLogout()
    {
        (new DiscourseSso())->logout();
        $path = config('app.discourse_uri') ?: 'https://community.tablda.com';
        return redirect($path);
    }

    /**
     * @param $filehash
     * @param Request $request
     * @return mixed
     */
    public function protectFile($filehash, Request $request)
    {
        if (preg_match('/\//i', $filehash)) {
            $filepath = $filehash;
        } else {
            $file = (new FileRepository())->getByHash($filehash);
            $filepath = $file ? $file->filepath . $file->filename : '';
        }

        $file_full = storage_path('app/public/' . $filepath);

        if ($filepath && $this->isAvailUrl($filepath)) {
            return \Response::file($file_full);
        }

        if ($filepath && file_exists($file_full)) {
            $tb_id = array_first(explode('/', $filepath));
            $tb_id = $tb_id ? array_first(explode('_', $tb_id)) : null;
            $tb_id = intval($tb_id);
            $table = $tb_id ? (new TableService())->getTable($tb_id) : null;

            if (!$table || $this->canLoad($table, $request->s ?: '')) {
                return $request->dwn
                    ? \Response::download($file_full)
                    : \Response::file($file_full);
            }
        }

        return redirect('/');
        //return abort(404);
    }

    /**
     * @param Table $table
     * @param $web_hash
     * @return bool
     */
    protected function canLoad(Table $table, $web_hash)
    {
        $user = auth()->user() ?: new User();
        return (new TableDataPolicy())->load($user, $table, $web_hash ?: '');
    }

    /**
     * @param string $path
     * @return int
     */
    protected function isAvailUrl(string $path)
    {
        return preg_match('/subdomain_icons\//i', $path)
            || preg_match('/ckeditors\//i', $path);
    }
}

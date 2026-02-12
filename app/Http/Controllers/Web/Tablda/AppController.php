<?php

namespace Vanguard\Http\Controllers\Web\Tablda;

use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Session;
use Intervention\Image\ImageManager;
use Intervention\Image\Laravel\ImageResponseFactory;
use Ramsey\Uuid\Uuid;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Vanguard\AppsModules\GeneralJson\GeneralJsonAutoRemover;
use Vanguard\AppsModules\GeneralJson\GeneralJsonImportExport;
use Vanguard\Classes\DiscourseSso;
use Vanguard\Classes\TabldaUser;
use Vanguard\Http\Controllers\Controller;
use Vanguard\Jobs\AppSendMail;
use Vanguard\Models\Table\Table;
use Vanguard\Models\Table\TableData;
use Vanguard\Models\Table\TableFieldLink;
use Vanguard\Models\Table\TableUsedCode;
use Vanguard\Models\Table\TableView;
use Vanguard\Modules\CloudBackup\GoogleApiModule;
use Vanguard\Policies\TableDataPolicy;
use Vanguard\Repositories\Tablda\FileRepository;
use Vanguard\Repositories\Tablda\FolderRepository;
use Vanguard\Repositories\Tablda\RemoteFilesRepository;
use Vanguard\Repositories\Tablda\TableFieldLinkRepository;
use Vanguard\Repositories\Tablda\TableFieldRepository;
use Vanguard\Repositories\Tablda\TableRepository;
use Vanguard\Repositories\Tablda\TableViewRepository;
use Vanguard\Repositories\Tablda\UserCloudRepository;
use Vanguard\Services\Tablda\AlertFunctionsService;
use Vanguard\Services\Tablda\BladeVariablesService;
use Vanguard\Services\Tablda\FolderService;
use Vanguard\Services\Tablda\HelperService;
use Vanguard\Services\Tablda\PagesService;
use Vanguard\Services\Tablda\TableDataService;
use Vanguard\Services\Tablda\TableFieldService;
use Vanguard\Services\Tablda\TableService;
use Vanguard\Support\FileHelper;
use Vanguard\User;

class AppController extends Controller
{
    private $pageService;
    private $folderService;
    private $tableService;
    private $tableDataService;
    private $variablesService;

    /**
     * @param PagesService $pageService
     * @param FolderService $folderService
     * @param TableService $tableService
     * @param TableDataService $tableDataService
     * @param BladeVariablesService $variablesService
     */
    public function __construct(
        PagesService $pageService,
        FolderService $folderService,
        TableService $tableService,
        TableDataService $tableDataService,
        BladeVariablesService $variablesService
    )
    {
        $this->pageService = $pageService;
        $this->folderService = $folderService;
        $this->tableService = $tableService;
        $this->tableDataService = $tableDataService;
        $this->variablesService = $variablesService;
    }

    /**
     * Tablda Data
     *
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function index(Request $request)
    {
        return view('tablda.data', array_merge(
            $this->variablesService->getVariables(),
            [
                'type' => 'table',
                'object' => (Object)['id' => 0, 'name' => ''],
                'filters' => [],
                'request_vars' => $request->all() ?? [],
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
     * Tablda Data
     *
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function docs()
    {
        return view('tablda.docs', array_merge(
            $this->variablesService->getVariables(),
            [
                'lightweight' => true,
                'docs_domain' => str_replace('//docs.', '//docs-content.', url()->current()),
                'route_group' => 'docs',
            ]
        ));
    }

    /**
     * Ping server
     *
     * @return array
     */
    public function ping(Request $request)
    {
        $result_message = '';
        $error_code = 0;

        try {
            $ses = auth()->id()
                ? \DB::connection('mysql')
                    ->table('sessions')
                    ->where('id', Session::getId())
                    ->first()
                : null;

            $request->pathname = preg_replace('/%20/i', ' ', $request->pathname);

            /*if (
                $request->app_user['vendor_script'] != mix('assets/js/tablda/vendor.js')->toHtml()
                ||
                $request->app_user['app_script'] != mix('assets/js/tablda/app.js')->toHtml()
            ) { //new scripts version
                $result_message = config('app.debug') ? '' : 'Page reloading needed.';
            }*/
            if ($ses && $ses->user_id && $ses->disconnected) // session disconnected
            {
                $usr_id = auth()->id();
                Auth::logout();
                (new DiscourseSso())->syncUnlog($usr_id);
                $result_message = 'You have been automatically logged out from another terminal.';
            }
            if ($request->app_user['id'] && !auth()->id()) // session expired on back-end
            {
                Log::info('!!!---Session Expired---!!! uid:'.$request->app_user['id'].' session:'.Session::getId());
                /*$error_code = 1;
                $result_message = 'Session expired. Please login';*/
            }
            if (preg_match('/^\/dcr\//i', $request->pathname)) // data request closed
            {
                $code = preg_replace('/^\/dcr\//i', '', urldecode($request->pathname));
                if (!$this->tableService->tableRequest($code)) {
                    $result_message = 'Data Collection Request (DCR) module has been inactivated.';
                }
            }
        } catch (\Exception $e) {
            Log::info('Ping error: '.$e->getMessage());
        }
        setcookie('ping_message', $result_message);

        return [
            'sync_reloading' => auth()->user() ? auth()->user()->sync_reloading : TabldaUser::unlogged()->sync_reloading,
            'user_changed' => $request->app_user['id'] != auth()->id(),
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
        $needed_path = preg_replace('/^\/data\//i', '', $request->getRequestUri());
        $object = $this->tableService->objectExists($needed_path);
        if ($object['id']) {

            //Table
            if ($object['type'] == 'table') {
                $data = $this->loadTablePage($object['id'], $object['type'], $request);
            } //Page
            elseif ($object['type'] == 'page') {
                $data = $this->variablesService->getVariables();
                $data['type'] = 'page';
                $data['object'] = $this->pageService->getPage($object['id']);
                $data['filters'] = [];
            } //Folder
            else {
                $data = $this->variablesService->getVariables();
                $data['type'] = 'folder';
                $data['object'] = $this->folderService->getFolderMeta($object['id'], auth()->id());
                $data['filters'] = [];
            }

            $data['request_vars'] = $request->all() ?? [];

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
                $data['request_vars'] = $request->all() ?? [];
                return view('tablda.data', $data);
            }
        } else {
            return redirect(route('data'));
        }
    }

    /**
     * @param $tablehash
     * @param $view
     * @param Request $request
     * @return \Illuminate\Routing\Redirector|\Illuminate\View\View
     */
    public function singleRecordView($tablehash, Request $request)
    {
        $user = auth()->check() ? auth()->user() : new User();
        $tb = $this->tableService->getTableByHash($tablehash);
        //Is TableView
        if ($tb) {
            $vars = array_merge(
                $this->variablesService->setTableObj($tb)->getVariables(null, 1),
                [
                    'type' => 'table',
                    'object' => $tb,
                    'filters' => [],
                    'panels_preset' => null,
                    'lightweight' => true,
                ]
            );

            $owner = $tb->_user;
            $this->variablesService->setUserTheme($owner);
            $vars['user']->_selected_theme = $owner->_selected_theme;
            $vars['user']->_srv_hash = $tb->hash;//will be changed to 'srvRecord.statis_hash' at front end

            return view('tablda.single-record-view', $vars);
        }
        return redirect(route('homepage'));
    }

    /**
     * View page.
     *
     * @param $view
     * @param Request $request
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\Http\RedirectResponse|\Illuminate\Routing\Redirector|\Illuminate\View\View
     */
    public function multiRecordView($view, Request $request)
    {
        $viewHash = explode('/', $view)[0] ?? '';
        $rowHash = explode('/', $view)[1] ?? '';

        $user = auth()->check() ? auth()->user() : new User();
        $viewObj = $this->tableService->viewExists($viewHash);
        //Is TableView
        if ($viewObj) {

            $queryPreset = json_decode($viewObj->data, true);
            $tb = $this->tableService->getTable($viewObj['table_id']);

            $viewObj->view_filtering_row = $this->receiveFilteringRow($tb, $rowHash);

            return view('tablda.data', array_merge(
                $this->variablesService->setTableObj($tb, $viewObj->_user)->getVariables($viewObj),
                [
                    'type' => 'table',
                    'object' => $tb,
                    'filters' => [],
                    'panels_preset' => $queryPreset['panels_preset'] ?? null,
                    'request_vars' => $request->all() ?? [],
                ]
            ));
        }
        return redirect(route('homepage'));
    }

    /**
     * View dashboard.
     *
     * @param $view
     * @param Request $request
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\Http\RedirectResponse|\Illuminate\Routing\Redirector|\Illuminate\View\View
     */
    public function dashboardView($view, Request $request)
    {
        $page = $this->pageService->viewExists($view);
        if ($page) {
            return view('tablda.data', array_merge(
                $this->variablesService->getVariables(),
                [
                    'type' => 'page',
                    'object' => $page,
                    'filters' => [],
                    'request_vars' => $request->all() ?? [],
                ]
            ));
        }
        return redirect(route('homepage'));
    }

    /**
     * @param Table $tb
     * @param $rowHash
     * @return array|null
     */
    protected function receiveFilteringRow(Table $tb, $rowHash)
    {
        $row = null;
        if ($rowHash) {
            $customUrlFieldIds = $tb->_fields
                ->pluck('_links')
                ->flatten()
                ->pluck('share_custom_field_id')
                ->filter();
            $customFields = (new TableFieldRepository())->getField($customUrlFieldIds->toArray());

            $rowKeys = $customFields->pluck('field')->toArray();
            $rowKeys[] = 'static_hash';
            foreach ($rowKeys as $key) {
                $row = $row ?: $this->tableDataService->getRowBy($tb, $key, $rowHash);
            }
        }
        return $row ? $row->toArray() : null;
    }

    /**
     * View page.
     *
     * @param $view
     * @param Request $request
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\Http\RedirectResponse|\Illuminate\Routing\Redirector|\Illuminate\View\View
     */
    public function folderView($view, Request $request)
    {
        $user = auth()->check() ? auth()->user() : new User();
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
                    'request_vars' => $request->all() ?? [],
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
            $data['request_vars'] = $request->all() ?? [];

            return view('tablda.data', $data);
        } elseif ($view && ($view->is_active || $user->id)) {
            $tbview = $this->tableService->getTable($view['table_id']);
            $data = $this->variablesService->setTableObj($tbview, $view->_user)->getVariables($view, 1);
            $data['type'] = 'table';
            $data['object'] = $tbview;
            $data['filters'] = [];

            $queryPreset = json_decode($view->data, true);
            $data['panels_preset'] = $queryPreset['panels_preset'] ?? null;
            $data['request_vars'] = $request->all() ?? [];

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
                    'request_vars' => $request->all() ?? [],
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
        $dcr = $this->tableService->tableRequest($code);
        if ($dcr) {

            $tb = $this->tableService->getTable($dcr['table_id']);
            $data = $this->variablesService->setTableObj($tb, $dcr->getOwner())->getVariables(null, 1);
            $data['type'] = 'table';
            $data['object'] = $tb;
            $data['dcrObject'] = $dcr;
            $data['is_embed'] = preg_match('/embed-dcr/i', $request->path());
            $data['user']->_dcr_hash = $dcr->dcr_hash;
            $data['user']->_dcr_uid = $dcr->id;

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
     * Consulting Page
     *
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function consultingEn()
    {
        return view('tablda.consulting-en', $this->variablesService->getVariables());
    }

    /**
     * Consulting Page
     *
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function consultingCn()
    {
        return view('tablda.consulting-cn', $this->variablesService->getVariables());
    }

    /**
     * @param Request $request
     * @return \Illuminate\Foundation\Application|\Illuminate\Http\RedirectResponse|\Illuminate\Routing\Redirector
     * @throws \Exception
     */
    public function anaClick(Request $request)
    {
        $datas = explode('_', $request->link);
        if (!empty($datas[1]) && !empty($datas[2])) {
            try {
                $str = (new AlertFunctionsService())->anaClickUpdateRun($datas[1], $datas[2]);
                Session::flash('home_message_flash', $str);
            } catch (\Exception $e) {
                Session::flash('home_message_flash', $e->getMessage());
            }
        } else {
            Session::flash('home_message_flash', 'Error: Link is incorrect!');
        }
        return redirect('/');
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
        $file = null;
        $base_dir = 'app/public/';

        if (preg_match('/\//i', $filehash)) {
            $filepath = $filehash;
            $lasthash = Arr::last(explode('/', $filehash));
            if ($file = (new RemoteFilesRepository())->getByHash($lasthash)) {
                $base_dir = 'app/remote_image_thumbs/';
            }
        } else {
            $file = (new FileRepository())->getByHash($filehash);
            $filepath = $file ? $file->filepath . $file->filename : '';
        }

        $file_full = storage_path($base_dir . $filepath);

        if ($filepath && file_exists($file_full)) {
            if ($this->isAvailUrl($filepath)) {
                return $this->fileResponse($file_full);
            }

            if ($file) {
                $tb_id = $file->table_id;
            } else {
                $tb_id = Arr::first(explode('/', $filepath));
                $tb_id = $tb_id ? Arr::first(explode('_', $tb_id)) : null;
                $tb_id = intval($tb_id);
            }
            $table = $tb_id ? (new TableService())->getTable($tb_id) : null;

            if (!$table || $this->canLoad($table, $request->s ?: '')) {
                if ($request->dwn || $this->mustDwn($file_full)) {
                    return $this->fileResponse($file_full, true);
                } else {
                    return $request->thumb
                        ? $this->magickResizer($file_full, $filepath, $request->thumb)
                        : $this->fileResponse($file_full);
                }
            }
        }

//        return redirect('/');
        abort(403);
    }

    /**
     * @param Request $request
     * @return mixed
     */
    public function saveRecent(Request $request)
    {
        (new FolderRepository())->saveRecent($request->type, $request->id);

        $service = new HelperService();
        return auth()->id()
            ? $service->getRecentsForUser(auth()->user())
            : [];
    }

    /**
     * @param Request $request
     * @return string
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function autoExport(Request $request)
    {
        $this->validate($request, [
            'table_id' => 'required|integer',
            'row_id' => 'required|integer',
        ]);
        $table = (new TableRepository())->getTable($request->table_id);
        $this->authorizeForUser(auth()->user(), 'get', [TableData::class, $table, $request->all()]);
        $link = (new TableFieldLinkRepository())->findExportLink($table);

        if ($link && $link->json_auto_export) {
            $message = (new GeneralJsonImportExport($table))->export($request->row_id, $link->json_export_field_id, $link->id);
            if ($link->json_auto_remove_after_export) {
                (new GeneralJsonAutoRemover($table))->removeLinkedRows($request->row_id);
            }
        } else {
            $message = 'Link was not found!';
        }
        return $message;
    }

    /**
     * @param string $filepath
     * @param bool $download
     * @return BinaryFileResponse
     */
    protected function fileResponse(string $filepath, bool $download = false): BinaryFileResponse
    {
        $headers = [
            'Content-Disposition attachment; filename="'.FileHelper::name($filepath).'"'
        ];
        return $download
            ? \Response::download($filepath, FileHelper::name($filepath), $headers)
            : \Response::file($filepath, $headers);
    }

    /**
     * @param string $file_full
     * @param string $filepath
     * @param string $size
     * @return mixed
     */
    protected function magickResizer(string $file_full, string $filepath, string $size)
    {
        if (!in_array($size, ['sm', 'md', 'lg'])) {
            return \Response::file($file_full);
        }
        try {
            return \Cache::rememberForever("resizer-$filepath-$size", function () use ($file_full, $size) {
                switch ($size) {
                    case 'sm': $w = 128; $h = 96; break;
                    case 'md': $w = 256; $h = 192; break;
                    case 'lg': $w = 512; $h = 384; break;
                    default: $w = 512; $h = 512; break;
                }
                $image = ImageManager::imagick()->read($file_full)->scale($w, $h);
                return ImageResponseFactory::make($image);
            });
        } catch (\Exception $e) {
            return $this->fileResponse($file_full);
        }
    }

    /**
     * @param Table $table
     * @param $web_hash
     * @return bool
     */
    protected function canLoad(Table $table, $web_hash)
    {
        $user = auth()->user() ?: new User();
        return (new TableDataPolicy())->load($user, $table, [
            'special_params' => [
                'view_hash' => $web_hash ?: '',
                'is_folder_view' => $web_hash ?: '',
                'dcr_hash' => $web_hash ?: '',
                'srv_hash' => $web_hash ?: '',
            ],
        ]);
    }

    /**
     * @param string $path
     * @return int
     */
    protected function isAvailUrl(string $path)
    {
        return preg_match('/subdomain_icons\//i', $path)
            || preg_match('/ckeditors\//i', $path)
            || preg_match('/Stim_View_Feedback_Results\//i', $path);
    }

    /**
     * @param string $file
     * @return bool
     */
    protected function mustDwn(string $file)
    {
        return in_array(FileHelper::extension($file), ['json','r3d','docx','csv']);
    }
}

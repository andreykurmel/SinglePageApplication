<?php

namespace Vanguard\Http\Controllers\Web\Tablda;

use Illuminate\Http\Request;
use Ramsey\Uuid\Uuid;
use Vanguard\Http\Controllers\Controller;
use Vanguard\Ideas\Repos\CachedTableRepository;
use Vanguard\Mail\TabldaMail;
use Vanguard\Models\File;
use Vanguard\Models\Table\Table;
use Vanguard\Models\Table\TableEmailAddonSetting;
use Vanguard\Models\Table\TableEntity;
use Vanguard\Repositories\Tablda\DDLRepository;
use Vanguard\Repositories\Tablda\FolderRepository;
use Vanguard\Repositories\Tablda\TableData\TableDataQuery;
use Vanguard\Repositories\Tablda\TableData\TableDataRepository;
use Vanguard\Repositories\Tablda\TableRepository;
use Vanguard\Services\Tablda\HelperService;
use Vanguard\Services\Tablda\Permissions\UserPermissionsService;
use Vanguard\Services\Tablda\TableService;
use Vanguard\Services\Tablda\UserService;
use Vanguard\Singletones\AuthUserModule;
use Vanguard\Singletones\AuthUserSingleton;
use Vanguard\Watchers\FormulaWatcher;

class TestController extends Controller
{
    protected $service;
    protected $tableService;
    protected $userService;
    protected $tableDataRepository;
    protected $permissionsService;
    protected $tableRepository;
    protected $DDLRepository;
    protected $folderRepository;
    private $available_math_symbols = ['sqrt', 'pow', 'week', 'month'];

    private $registered = [];

    public function __construct(
        UserService $userService,
        TableService $tableService,
        \Vanguard\Repositories\Tablda\TableData\TableDataRepository $tableDataRepository,
        UserPermissionsService $permissionsService,
        HelperService $service,
        TableRepository $tableRepository,
        DDLRepository $DDLRepository,
        FolderRepository $folderRepository
    )
    {
        $this->service = $service;
        $this->tableService = $tableService;
        $this->userService = $userService;
        $this->tableDataRepository = $tableDataRepository;
        $this->permissionsService = $permissionsService;
        $this->tableRepository = $tableRepository;
        $this->DDLRepository = $DDLRepository;
        $this->folderRepository = $folderRepository;

        $this->register(AuthUserSingleton::class, AuthUserModule::class);

    }

    // CONTAINER FUNCTIONS --->>>
    private function register($abstract, $concrete) {
        $this->registered[$abstract] = $concrete;
    }

    private function resolve($abstract) {
        $class = $this->registered[$abstract] ?? null;
        if ($class) {
            return new $class();
        } else {
            throw new \Exception('Undefined abstract binding');
        }
    }
    // CONTAINER FUNCTIONS ^^^^^


    // Strategies functions --->>>
    private function strategy($type) {
        switch ($type) {
            case 'module': $class = AuthUserModule::class;
                    break;
            default: throw new \Exception('Undefined strategy type');
        }
        return new $class();
    }
    // Strategies functions ^^^^^


    /**
     * Sites - sites20190204032007
     * BI_Pilot - bi_test20181220022920
     *
     * whereHas
     * whereDoesntHave
     */
    public function test(Request $request) {
        ini_set('max_execution_time', 1200);

        /*$tb_entity = (new \Vanguard\Ideas\QueriesOnlyFromRepo\TableRepository())->get(977);
        dd($tb_entity->_user());
        return 'finish';*/

        $cacheRepo = new CachedTableRepository();
        $justRepo = new \Vanguard\Ideas\Repos\TableRepository();
        $tb1 = $cacheRepo->get([35,36,40,45]);
        $tb = $cacheRepo->get([35])->first();
        $tb2 = $cacheRepo->get([40]);
        //$tb3 = $justRepo->get([40]);

        $t = microtime(true);
        for ($i=0; $i<1000; $i++) {
            $tb->_table->add_notes = $i;
        }
        return (microtime(true)-$t);
        dd($tb, $tb1, $tb2);

        if (auth()->id() == 1) {
            $permiss = TableEmailAddonSetting::all();
            foreach ($permiss as $perm) {
                $perm->hash = Uuid::uuid4();
                $perm->save();
            }
            dd('dcr_hash');
        }
        dd('not avail');

        $table = Table::where('id', '=', 977)->first();
        $fields_arr = $table->_fields()
            ->whereNotIn('field', $this->service->system_fields)
            ->whereNotIn('f_type', ['Attachment'])
            ->joinHeader()
            ->get()
            ->map(function ($fld) {
                return $fld->only(['id', 'name', 'field', 'unit_display', 'unit']);
            })
            ->toArray();
        $rows_arr = (new TableDataQuery($table))->getQuery()->where('id', '=', 103)->get()->toArray();

        $mailr = new TabldaMail('tablda.emails.row_changed', [
            'greeting' => 'Hello greeting',
            'replace_main_message' => 'Main Message',
            'table_arr' => $table->getAttributes(),
            'fields_arr' => $fields_arr,
            'has_unit' => true,
            'all_rows_arr' => $rows_arr,
            'changed_fields' => ['fser_or_group_15','fholesaleprice_8'],
            'alert_arr' => ['mail_format' => 'list'],
            'type' => 'updated',
        ], [
            'from.name' => config('app.name').' DCR',
            'from.account' => 'noreply',
            'subject' =>  '$subject',
            'to.address' => '$recipients',
            'to.name' => '$toname',
        ]);
        return $mailr->render();

        if (auth()->id() == 1) {

            $files = File::all();
            foreach ($files as $fl) {
                if (preg_match('/^\d+_/i', $fl->filepath) && !preg_match('/^'.$fl->table_id.'_/i', $fl->filepath)) {
                    File::where('id', '=', $fl->id)->update([
                        'filepath' => preg_replace('/^\d+_/i', $fl->table_id.'_', $fl->filepath)
                    ]);
                }

                if (preg_match('/\/ddl_[^\/]+\//i', $fl->filepath) && !preg_match('/\/ddl_item\//i', $fl->filepath)) {
                    $newpath = preg_replace('/\/ddl_[^\/]+\//i', '/ddl_item/', $fl->filepath);
                    File::where('id', '=', $fl->id)->update([
                        'filepath' => $newpath
                    ]);
                    try {
                        \Storage::copy(
                            'public/' . $fl->filepath . $fl->filename,
                            'public/' . $newpath . $fl->filename
                        );
                    } catch (\Exception $e) {}
                }
            }
            dd('admin');
        }
        dd('finished');
    }

    /**
     * @return mixed
     */
    private function formulaWatcherPerformanceTest() {
        $dataRepo = new TableDataRepository();
        //$table = new Table();
        $table = Table::find(754);
        $row = (new TableDataQuery($table))->getQuery()->where('id', 69)->first()->toArray();
        $row['credit_8'] += 1;
        $dataRepo->updateRow($table, 69, $row, $table->user_id);


        $table->version_hash = Uuid::uuid4();
        (new TableRepository())->onlyUpdateTable($table->id, $table->toArray());

        $t1 = microtime(true);
        (new FormulaWatcher())->watchReferences($table);
        return ( microtime(true) - $t1 );
    }

    /**
     * test Vue reactivity.
     *
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function testVue(Request $request)
    {
        return view('tablda.for_test');
    }

}

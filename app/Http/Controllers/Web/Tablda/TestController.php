<?php

namespace Vanguard\Http\Controllers\Web\Tablda;

use DB;
use Exception;
use Illuminate\Contracts\View\Factory;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Redis;
use Illuminate\Support\Facades\Schema;
use Illuminate\View\View;
use Ramsey\Uuid\Uuid;
use Vanguard\Http\Controllers\Controller;
use Vanguard\Ideas\Repos\CachedTableRepository;
use Vanguard\Models\Table\Table;
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
        UserService            $userService,
        TableService           $tableService,
        TableDataRepository    $tableDataRepository,
        UserPermissionsService $permissionsService,
        HelperService          $service,
        TableRepository        $tableRepository,
        DDLRepository          $DDLRepository,
        FolderRepository       $folderRepository
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
    private function register($abstract, $concrete)
    {
        $this->registered[$abstract] = $concrete;
    }

    /**
     * Sites - sites20190204032007
     * BI_Pilot - bi_test20181220022920
     *
     * whereHas
     * whereDoesntHave
     */
    public function test(Request $request)
    {
        ini_set('max_execution_time', 1200);

        if (auth()->id() == 1) {
            foreach (Table::all() as $tb) {
                $tb->update(['hash' => Uuid::uuid4()]);
            }
            dd('dcr_hash');
        }

        Redis::set('asd', 'hgy23fg2837i23');
        dd(Redis::get('asd'));

        /*$tb_entity = (new \Vanguard\Ideas\QueriesOnlyFromRepo\TableRepository())->get(977);
        dd($tb_entity->_user());
        return 'finish';*/

        //Patterns
        /*$woodFactory = ShapeStrategy::getFactory('steel');
        $sph = $woodFactory->buildSphere(16);
        $cude = $woodFactory->buildCube(5, 10, 15);

        $collect = new ShapeCollection();
        $collect->addShape($sph);
        $collect->addShape($cude);

        $it = $collect->shapeIterator();
        while ($shape = $it->next()) {
            echo $shape->getVolume().'<br>';
        }
        echo '<br>';

        return ($cude->getVolume().' '.$cude->getWeight());*/
        //Patterns


        $cacheRepo = new CachedTableRepository();
        $justRepo = new \Vanguard\Ideas\Repos\TableRepository();
        $tb1 = $cacheRepo->get([35, 36, 40, 45]);
        $tb = $cacheRepo->get([35])->first();
        $tb2 = $cacheRepo->get([40]);
        //$tb3 = $justRepo->get([40]);

        $t = microtime(true);
        for ($i = 0; $i < 1000; $i++) {
            $tb->_table->add_notes = $i;
        }
        return (microtime(true) - $t);
        dd($tb, $tb1, $tb2);

        dd('finished');
    }
    // CONTAINER FUNCTIONS ^^^^^


    // Strategies functions --->>>

    /**
     * test Vue reactivity.
     *
     * @return Factory|View
     */
    public function testVue(Request $request)
    {
        return view('tablda.for_test');
    }
    // Strategies functions ^^^^^

    private function resolve($abstract)
    {
        $class = $this->registered[$abstract] ?? null;
        if ($class) {
            return new $class();
        } else {
            throw new Exception('Undefined abstract binding');
        }
    }

    private function strategy($type)
    {
        switch ($type) {
            case 'module':
                $class = AuthUserModule::class;
                break;
            default:
                throw new Exception('Undefined strategy type');
        }
        return new $class();
    }

    /**
     * @return mixed
     */
    private function formulaWatcherPerformanceTest()
    {
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
        return (microtime(true) - $t1);
    }

}

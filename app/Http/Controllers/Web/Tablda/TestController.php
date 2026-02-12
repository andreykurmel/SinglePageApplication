<?php

namespace Vanguard\Http\Controllers\Web\Tablda;

use Carbon\Carbon;
use Exception;
use GuzzleHttp\Psr7\MimeType;
use Illuminate\Contracts\View\Factory;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Redis;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use Illuminate\View\View;
use Maatwebsite\Excel\Excel as ExcelFormat;
use Maatwebsite\Excel\Facades\Excel;
use Ramsey\Uuid\Uuid;
use Vanguard\AppsModules\EriParserWriterModule;
use Vanguard\AppsModules\GeneralJson\GeneralJsonImportExport;
use Vanguard\AppsModules\StimMaJson\JsonService;
use Vanguard\Classes\SysColumnCreator;
use Vanguard\Http\Controllers\Controller;
use Vanguard\Jobs\AnaSnapshots;
use Vanguard\Jobs\OldSessionsRemover;
use Vanguard\Jobs\TablesUsagesFixing;
use Vanguard\Jobs\UsersDailyPay;
use Vanguard\Mail\TabldaMail;
use Vanguard\Models\AppSetting;
use Vanguard\Models\AutomationHistory;
use Vanguard\Models\Correspondences\CorrespField;
use Vanguard\Models\DataSetPermissions\TableRefCondition;
use Vanguard\Models\DataSetPermissions\TableRowGroup;
use Vanguard\Models\Dcr\TableDataRequest;
use Vanguard\Models\Folder\Folder;
use Vanguard\Models\Pages\Pages;
use Vanguard\Models\Table\Table;
use Vanguard\Models\Table\TableAlert;
use Vanguard\Models\Table\TableBackup;
use Vanguard\Models\Table\TableData;
use Vanguard\Models\Table\TableEmailAddonSetting;
use Vanguard\Models\Table\TableField;
use Vanguard\Models\Table\TableFieldLink;
use Vanguard\Models\Table\TableFieldLinkEriTable;
use Vanguard\Models\Table\TableKanbanSettings;
use Vanguard\Models\Table\TableView;
use Vanguard\Models\User\UserApiKey;
use Vanguard\Models\User\UserCloud;
use Vanguard\Models\User\UserGroup;
use Vanguard\Modules\AiRequests\OpenAiApi;
use Vanguard\Modules\Jira\JiraApiModule;
use Vanguard\Modules\QRGenerator;
use Vanguard\Modules\Salesforce\SalesforceApiModule;
use Vanguard\Repositories\Tablda\DDLRepository;
use Vanguard\Repositories\Tablda\FolderRepository;
use Vanguard\Repositories\Tablda\Permissions\TableRefConditionRepository;
use Vanguard\Repositories\Tablda\Permissions\TableRowGroupRepository;
use Vanguard\Repositories\Tablda\TableData\TableDataQuery;
use Vanguard\Repositories\Tablda\TableData\TableDataRepository;
use Vanguard\Repositories\Tablda\TableFieldLinkRepository;
use Vanguard\Repositories\Tablda\TableFieldRepository;
use Vanguard\Repositories\Tablda\TableGroupingRepository;
use Vanguard\Repositories\Tablda\TableKanbanRepository;
use Vanguard\Repositories\Tablda\TableRepository;
use Vanguard\Repositories\Tablda\UserRepository;
use Vanguard\Services\Tablda\AlertFunctionsService;
use Vanguard\Services\Tablda\HelperService;
use Vanguard\Services\Tablda\ImportService;
use Vanguard\Services\Tablda\PaymentService;
use Vanguard\Services\Tablda\Permissions\TableDataRequestService;
use Vanguard\Services\Tablda\Permissions\UserPermissionsService;
use Vanguard\Services\Tablda\TableAlertService;
use Vanguard\Services\Tablda\TableDataService;
use Vanguard\Services\Tablda\TableService;
use Vanguard\Services\Tablda\UserService;
use Vanguard\Singletones\AuthUserModule;
use Vanguard\Singletones\AuthUserSingleton;
use Vanguard\Support\Excel\ArrayExport;
use Vanguard\Support\FileHelper;
use Vanguard\Support\SimilarityHelper;
use Vanguard\User;
use Vanguard\Watchers\FormulaWatcher;
use function GuzzleHttp\Psr7\mimetype_from_extension;

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
        dd('Completed!');

        return (new TableDataRequestService())->sendRequestEmails(
            Table::find(2043),
            2281,
            (new TableDataRepository())->getDirectRow(Table::find(2043), 7)->toArray()
        );

        return (new AlertFunctionsService())->sendEmailNotificationjob(
            50,
            [(new TableDataRepository())->getDirectRow(Table::find(2043), 7)->toArray()],
            ['to'=>'test@gmail.com'],
            [],
            'added'
        );
    }
    // CONTAINER FUNCTIONS ^^^^^
    public function tokenize(string $s): array
    {
        // lower, trim, collapse whitespace, strip punctuation except digits/letters/spaces
        $s = mb_strtolower($s);
        $s = preg_replace('/[^\p{L}\p{N}\s]+/u', ' ', $s);
        $s = preg_replace('/\s+/u', ' ', trim($s));
        if ($s === '') {
            return [];
        }
        return array_values(array_unique(explode(' ', $s)));
    }


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

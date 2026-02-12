<?php

namespace Vanguard\Http\Controllers\Web\Tablda;


use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Http\Request;
use Vanguard\Http\Controllers\Controller;
use Vanguard\Models\Table\TableData;
use Vanguard\Repositories\Tablda\SrvRepository;
use Vanguard\Repositories\Tablda\TableFieldRepository;
use Vanguard\Services\Tablda\TableDataService;
use Vanguard\Services\Tablda\TableService;

class SrvController extends Controller
{
    protected $tableDataService;
    protected $tableService;
    protected $fieldRepository;
    protected $srvRepository;

    /**
     *
     */
    public function __construct()
    {
        $this->tableDataService = new TableDataService();
        $this->tableService = new TableService();
        $this->fieldRepository = new TableFieldRepository();
        $this->srvRepository = new SrvRepository();
    }

    /**
     * @param Request $request
     * @return array
     */
    public function getSrv(Request $request)
    {
        $table = $this->tableService->getTable($request->table_id);
        $srv = $this->tableDataService->getRowSRV($table, str_replace('#', '', $request->srv_hash));
        return [
            'srv' => $srv
        ];
    }

    /**
     * @param Request $request
     * @return array
     * @throws AuthorizationException
     */
    public function insertBgiFile(Request $request)
    {
        $table = $this->tableService->getTable($request->table_id);
        $this->authorize('isOwner', [TableData::class, $table]);
        return [
            'filepath' => $this->srvRepository->insertBgiFile($table, $request->bgi_file)
        ];
    }

    /**
     * @param Request $request
     * @return array
     * @throws AuthorizationException
     */
    public function deleteBgiFile(Request $request)
    {
        $table = $this->tableService->getTable($request->table_id);
        $this->authorize('isOwner', [TableData::class, $table]);
        return [
            'status' => $this->srvRepository->deleteBgiFile($table)
        ];
    }

    /**
     * @param Request $request
     * @return bool[]
     */
    public function checkPass(Request $request)
    {
        $table = $this->tableService->getTable($request->table_id);
        $field = $this->fieldRepository->getField($table->single_view_password_id);
        $row = $this->tableDataService->getDirectRow($table, $request->row_id);

        return [
            'status' => $field && $row && $row[$field->field] == $request->pass,
        ];
    }

    /**
     * @param Request $request
     * @return array
     * @throws AuthorizationException
     */
    public function attachDetachFld(Request $request)
    {
        $table = $this->tableService->getTable($request->table_id);
        $this->authorize('isOwner', [TableData::class, $table]);
        if ($request->field_id) {
            $this->fieldRepository->srvAttachIfNeeded($request->table_id, $request->field_id);
            $this->fieldRepository->srvChangePivotFld($request->table_id, $request->field_id, $request->setting, $request->val);
            $table->load('_fields_pivot');
            return $table->toArray();
        }
        return [];
    }
}

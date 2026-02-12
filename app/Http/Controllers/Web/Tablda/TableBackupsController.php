<?php

namespace Vanguard\Http\Controllers\Web\Tablda;


use Vanguard\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Vanguard\Models\Table\TableData;
use Vanguard\Repositories\Tablda\TableRepository;
use Vanguard\Services\Tablda\TableBackupService;

class TableBackupsController extends Controller
{
    protected $tableRepository;
    protected $bkpService;

    /**
     * TableBackupsController constructor.
     *
     * @param TableRepository $tableRepository
     */
    public function __construct(TableRepository $tableRepository)
    {
        $this->tableRepository = new TableRepository();
        $this->bkpService = new TableBackupService();
    }

    /**
     * Add Backup
     *
     * @param Request $request
     * @return mixed
     */
    public function insert(Request $request){
        $table = $this->tableRepository->getTable($request->table_id);

        $this->authorize('isOwner', [TableData::class, $table]);

        return $this->bkpService->addTableBackup( array_merge($request->fields, ['table_id' => $table->id]), $request->table_url );
    }

    /**
     * Update Backup.
     *
     * @param Request $request
     * @return array
     */
    public function update(Request $request){
        $table_backup = $this->bkpService->getTableBackup($request->table_backup_id);
        $table = $table_backup->_table;

        $this->authorize('isOwner', [TableData::class, $table]);

        $this->bkpService->updateTableBackup( $table_backup->id, array_merge($request->fields, ['table_id' => $table->id]), $request->table_url );
        return $this->bkpService->getTableBackup($table_backup->id);
    }

    /**
     * Delete Backup
     *
     * @param Request $request
     * @return mixed
     */
    public function delete(Request $request){
        $table_backup = $this->bkpService->getTableBackup($request->table_backup_id);
        $table = $table_backup->_table;

        $this->authorize('isOwner', [TableData::class, $table]);

        return [
            'status' => $this->bkpService->deleteTableBackup( $table_backup->id )
        ];
    }
}

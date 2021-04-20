<?php

namespace Vanguard\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;
use Vanguard\Models\Table\Table;
use Vanguard\Repositories\Tablda\TableData\TableDataQuery;
use Vanguard\Repositories\Tablda\TableData\TableDataRepository;
use Vanguard\Services\Tablda\Permissions\UserPermissionsService;

class TablesUsagesFixing implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    private $tableDataRepository;
    private $permissionsService;

    /**
     * TablesUsagesFixing constructor.
     * @param \Vanguard\Repositories\Tablda\TableData\TableDataRepository $tableDataRepository
     * @param UserPermissionsService $permissionsService
     */
    public function __construct(TableDataRepository $tableDataRepository, UserPermissionsService $permissionsService)
    {
        $this->tableDataRepository = $tableDataRepository;
        $this->permissionsService = $permissionsService;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $tables = Table::where('is_system', '=', '0')->with(['_fields', '_public_links'])->get();
        foreach ($tables as $table) {
            $sql = new TableDataQuery($table, true);

            $info_schema = DB::connection('mysql_info_schema')
                ->select('SELECT `AVG_ROW_LENGTH` FROM `TABLES` WHERE `TABLE_NAME` = "'.$table->db_name.'"');
            $avg_row = count($info_schema) ? $info_schema[0]->AVG_ROW_LENGTH : 0;

            $data = [
                'num_rows' => $sql->getQuery()->count(),
                'num_columns' => $table->_fields->count(),
                'num_collaborators' => $this->permissionsService->getUsersCountForTable($table->id),
                'usage_type' => ($table->_public_links->count() ? 'Public' : ($table->num_collaborators > 1 ? 'Semi-Private' : 'Private'))
            ];
            if ($avg_row > 0) {
                $data['avg_row_length'] = $avg_row;
            }

            $table->update($data);
        }
    }

    public function failed()
    {
        //
    }
}

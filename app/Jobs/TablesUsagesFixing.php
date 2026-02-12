<?php

namespace Vanguard\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Vanguard\Models\PromoCode;
use Vanguard\Models\Table\Table;
use Vanguard\Repositories\Tablda\FileRepository;
use Vanguard\Repositories\Tablda\TableData\TableDataQuery;
use Vanguard\Repositories\Tablda\TableData\TableDataRepository;
use Vanguard\Services\Tablda\Permissions\UserPermissionsService;

class TablesUsagesFixing implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $tableDataRepository;
    protected $permissionsService;
    protected $fileRepository;
    protected $withDate;

    /**
     * TablesUsagesFixing constructor.
     */
    public function __construct(bool $withDate = true)
    {
        $this->withDate = $withDate;
        $this->fileRepository = new FileRepository();
        $this->tableDataRepository = new TableDataRepository();
        $this->permissionsService = new UserPermissionsService();
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        Table::where('is_system', '=', '0')
            ->when($this->withDate, function ($q) {
                $q->where('modified_on', '>', now()->subDay());
            })
            ->with(['_fields', '_public_links'])
            ->chunk(100, function ($tables) {
                foreach ($tables as $table) {
                    $sql = new TableDataQuery($table, true);

                    $info_schema = DB::connection('mysql_info_schema')
                        ->select('SELECT `AVG_ROW_LENGTH` FROM `TABLES` WHERE `TABLE_NAME` = "'.$table->db_name.'"');
                    $avg_row = count($info_schema) ? $info_schema[0]->AVG_ROW_LENGTH : 0;

                    $attach_size = exec('du -sh '.storage_path('app/public/'.$this->fileRepository->getStorageTable($table)));
                    $attach_size = Arr::first( preg_split('/\t/i', $attach_size) );

                    $data = [
                        'num_rows' => $sql->getQuery()->count(),
                        'num_columns' => $table->_fields->count(),
                        'num_collaborators' => $this->permissionsService->getUsersCountForTable($table->id),
                        'usage_type' => ($table->_public_links->count() ? 'Public' : ($table->num_collaborators > 1 ? 'Semi-Private' : 'Private')),
                        'attachments_size' => $attach_size,
                    ];
                    if ($avg_row > 0) {
                        $data['avg_row_length'] = $avg_row;
                    }

                    $table->update($data);
                }
            });

        //Notes: Daily promo codes
        PromoCode::where('end_at', '<', now()->toDateString())
            ->where('is_active', '=', 1)
            ->update([
                'is_active' => 0,
            ]);
    }

    public function failed()
    {
        //
    }
}

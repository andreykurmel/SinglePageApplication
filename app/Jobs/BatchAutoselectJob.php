<?php

namespace Vanguard\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Vanguard\Models\Table\Table;
use Vanguard\Modules\DdlShow\DdlShowModule;
use Vanguard\Repositories\Tablda\FileRepository;
use Vanguard\Repositories\Tablda\TableData\TableDataRepository;
use Vanguard\Repositories\Tablda\TableData\TableDataRowsRepository;
use Vanguard\Repositories\Tablda\TableFieldRepository;
use Vanguard\Support\FileHelper;

class BatchAutoselectJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $job_id;
    protected $table;
    protected $select_field_id;
    protected $auto_ddl;
    protected $auto_comparison;
    protected $row_group_id;

    /**
     * @param int $job_id
     * @param Table $table
     * @param int $select_field_id
     * @param string $auto_comparison - (ddl|prev_ddl):(val_to_show|show_to_val)
     * @param int|null $row_group_id
     */
    public function __construct(int $job_id, Table $table, int $select_field_id, string $auto_comparison, int $row_group_id = null)
    {
        $this->job_id = $job_id;
        $this->table = $table;
        $this->select_field_id = $select_field_id;
        $this->row_group_id = $row_group_id;

        $arr = explode(':', $auto_comparison);
        $this->auto_comparison = Arr::last($arr);
        $this->auto_ddl = Arr::first($arr);
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $sql = (new TableDataRowsRepository())->dataQuerySql($this->table, $this->row_group_id);

        $targetField = $this->table->_fields->where('id', '=', $this->select_field_id)->first();
        $relatedDdl = $this->auto_ddl == 'prev_ddl' ? $targetField->_prev_ddl : $targetField->_ddl;
        $allddl = $relatedDdl ? (new TableDataRepository())->getDDLvalues($relatedDdl, [], '', 1000) : [];
        $allddl = collect($allddl);

        $ddlShow = DdlShowModule::hasSyscol($this->table, $targetField);

        $page = 10;
        $lines = $sql->count();
        for ($cur = 0; ($cur * $page) < $lines; $cur++) {
            DB::connection('mysql')->table('imports')->where('id', '=', $this->job_id)->update([
                'complete' => (int)((($cur * $page) / $lines) * 100)
            ]);

            $rows = $sql->offset($cur * $page)
                ->limit($page)
                ->get();

            foreach ($rows as $row) {
                try {
                    $oldKey = $newKey = '';
                    switch ($this->auto_comparison) {
                        case 'val_to_show': $oldKey = 'value'; $newKey = 'show'; break;
                        case 'show_to_val': $oldKey = 'show'; $newKey = 'value'; break;
                    }

                    $rowValue = $row[$targetField->field] ?? null;
                    $dval = $allddl->where($oldKey, '=', $rowValue)->first();
                    $arr = ["{$targetField->field}" => $dval ? ($dval[$newKey] ?? $rowValue) : $rowValue];
                    if ($ddlShow) {
                        $arr[$ddlShow] = $dval ? ($dval['show'] ?? $rowValue) : $rowValue;
                    }
                    (clone $sql)->where('id', '=', $row['id'])->update($arr);

                } catch (\Exception $e) {
                    Log::info('TableDataRowsRepository - batchUploading Error');
                    Log::info($e->getMessage());
                }
            }
        }

        (new TableFieldRepository())->updateTableField($this->table, $targetField->id, ['prev_ddl_id' => $targetField->ddl_id]);

        DB::connection('mysql')->table('imports')->where('id', '=', $this->job_id)->update([
            'status' => 'done'
        ]);
    }

    /**
     *
     */
    public function failed()
    {
        //
    }
}

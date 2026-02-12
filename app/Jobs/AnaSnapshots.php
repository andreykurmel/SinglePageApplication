<?php

namespace Vanguard\Jobs;

use Exception;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;
use Vanguard\Models\Table\TableAlert;
use Vanguard\Repositories\Tablda\AutomationHistoryRepository;
use Vanguard\Repositories\Tablda\TableAlertsRepository;
use Vanguard\Repositories\Tablda\TableData\FormulaEvaluatorRepository;
use Vanguard\Repositories\Tablda\TableData\TableDataQuery;
use Vanguard\Repositories\Tablda\TableRepository;
use Vanguard\Services\Tablda\FileService;
use Vanguard\Services\Tablda\TableAlertService;
use Vanguard\Services\Tablda\TableDataService;

class AnaSnapshots implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $tableDataService;
    protected $fileService;
    protected $tableRepo;
    protected $alertRepo;
    protected $alertService;

    /**
     * UsersDailyPay constructor.
     */
    public function __construct()
    {
        $this->tableDataService = new TableDataService();
        $this->fileService = new FileService();
        $this->tableRepo = new TableRepository();
        $this->alertRepo = new TableAlertsRepository();
        $this->alertService = new TableAlertService();
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $snapshots = $this->alertRepo->activeSnapshots();

        foreach ($snapshots as $snapshot) {
            if ($snapshot->enabled_snapshot && $snapshot->_table && $snapshot->_snp_source_table) {
                $this->makeSnapshot($snapshot);
            }

            if ($snapshot->_table && $snapshot->snp_row_group_id) {
                $query = (new TableDataQuery($snapshot->_table));
                $query->applySelectedRowGroup($snapshot->snp_row_group_id);
                $rows = $query->getQuery()->get()->toArray();

                $this->alertService->checkAndSendNotifArray($snapshot->_table, 'added', $rows, [], ['user_id' => $snapshot->_table->user_id]);
                $this->alertService->checkAndSendNotifArray($snapshot->_table, 'updated', $rows, ['any_value'], ['user_id' => $snapshot->_table->user_id]);
                $this->alertService->checkAndSendNotifArray($snapshot->_table, 'deleted', $rows, [], ['user_id' => $snapshot->_table->user_id]);
            }
        }
    }

    /**
     * @param TableAlert $snapshot
     * @return void
     */
    protected function makeSnapshot(TableAlert $snapshot): void
    {
        try {
            $automationHistory = new AutomationHistoryRepository($snapshot->user_id, $snapshot->table_id);
            $automationHistory->startTimer();

            //present Documents for copying
            $documents = [];
            foreach ($snapshot->_snapshot_fields as $corrs) {
                if ($corrs->_source_field->f_type == 'Attachment' && $corrs->_cur_field->f_type == 'Attachment') {
                    $documents[$corrs->_source_field->id] = $corrs->_cur_field->id;
                }
            }

            //get query
            $query = (new TableDataQuery($snapshot->_snp_source_table));
            $query->checkAndApplyDataRange($snapshot->snp_data_range ?: '');
            $query->getQuery()->chunk(100, function (Collection $all_rows) use ($snapshot, $documents) {
                $pack = [];
                foreach ($all_rows as $row) {
                    $insert = $this->tableDataService->setDefaults($snapshot->_table, [], null);
                    foreach ($snapshot->_snapshot_fields as $corrs) {
                        $insert[$corrs->_cur_field->field] = $row[$corrs->_source_field->field] ?? null;
                    }
                    if ($snapshot->_snp_field_name && $snapshot->snp_name) {
                        $evaluator = new FormulaEvaluatorRepository($snapshot->_table);
                        $arrow = is_array($row) ? $row : $row->toArray();
                        $insert[$snapshot->_snp_field_name->field] = $evaluator->formulaReplaceVars($arrow, $snapshot->snp_name, true);
                    }
                    if ($snapshot->_snp_field_time) {
                        $insert[$snapshot->_snp_field_time->field] = now()->format('Y-m-d H:i:s');//
                    }
                    $insert['refer_tb_id'] = $snapshot->snp_src_table_id;
                    $pack[] = $insert;
                }

                $old_ids = $all_rows->pluck('id')->toArray();
                $new_ids = $this->tableDataService->insertMass($snapshot->_table, $pack);

                if ($documents && count($old_ids) == count($new_ids)) {
                    $this->fileService->copyAttachForRowsSpecial($snapshot->_snp_source_table, $snapshot->_table, array_combine($old_ids, $new_ids), $documents);
                }

                $this->tableRepo->onlyUpdateTable($snapshot->table_id, [
                    'num_rows' => $snapshot->_table->num_rows + count($pack)
                ]);
            });

            $automationHistory->stopTimerAndSave('ANA', $snapshot->name, 'Automation', 'Snapshot');
        } catch (Exception $e) {
            Log::info('ANA Snapshot - Reference General Error');
            Log::info($e->getMessage());
            $this->failed();
        }
    }

    public function failed()
    {
        //
    }
}

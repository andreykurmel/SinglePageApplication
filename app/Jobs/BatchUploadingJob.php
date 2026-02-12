<?php

namespace Vanguard\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Vanguard\Models\Table\Table;
use Vanguard\Repositories\Tablda\FileRepository;
use Vanguard\Repositories\Tablda\TableData\TableDataRowsRepository;
use Vanguard\Support\FileHelper;

class BatchUploadingJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $job_id;
    protected $table;
    protected $url_field_id;
    protected $attach_field_id;
    protected $row_group_id;

    /**
     * @param int $job_id
     * @param Table $table
     * @param int $url_field_id
     * @param int $attach_field_id
     * @param int|null $row_group_id
     */
    public function __construct(int $job_id, Table $table, int $url_field_id, int $attach_field_id, int $row_group_id = null)
    {
        $this->job_id = $job_id;
        $this->table = $table;
        $this->url_field_id = $url_field_id;
        $this->attach_field_id = $attach_field_id;
        $this->row_group_id = $row_group_id;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $sql = (new TableDataRowsRepository())->dataQuerySql($this->table, $this->row_group_id);

        $url = $this->table->_fields->where('id', '=', $this->url_field_id)->first();
        $filerepo = new FileRepository();

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
                    $filelink = $row[$url->field];
                    $filename = FileHelper::name($filelink);
                    $filecontent = file_get_contents($filelink);
                    $filerepo->insertFileAlias($this->table->id, $this->attach_field_id, $row['id'], $filename, $filecontent);
                } catch (\Exception $e) {
                    Log::info('TableDataRowsRepository - batchUploading Error');
                    Log::info($e->getMessage());
                }
            }
        }

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

<?php

namespace Vanguard\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Vanguard\Models\Table\Table;
use Vanguard\Repositories\Tablda\TableData\TableDataQuery;
use Vanguard\Repositories\Tablda\TableData\TableDataRepository;
use Vanguard\Services\Tablda\HelperService;
use Vanguard\Services\Tablda\TableDataService;

class AutoUpdateDDLSingleMultiple implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    private $table;
    private $field;
    private $toMultiple;

    /**
     * @param Table $table
     * @param string $field
     * @param bool $toMultiple
     */
    public function __construct(Table $table, string $field, bool $toMultiple)
    {
        $this->table = $table;
        $this->field = $field;
        $this->toMultiple = $toMultiple;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $dataService = new TableDataService();
        $dataRepository = new TableDataRepository();
        $sql = new TableDataQuery($this->table);

        $lines = $sql->getQuery()->count();
        $chunk = 500;

        for ($cur = 0; ($cur * $chunk) < $lines; $cur++) {

            $all_rows = $sql->getQuery()
                ->offset($cur * $chunk)
                ->limit($chunk)
                ->get()
                ->toArray();

            foreach ($all_rows as $row) {
                $updater = [
                    $this->field => !empty($row[$this->field])
                        ? ($this->toMultiple ? $this->multipleVal($row) : $this->singleVal($row))
                        : null
                ];
                $dataRepository->quickUpdate($this->table, $row['id'], $updater, false);
            }
            $dataService->newTableVersion($this->table);
        }
    }

    /**
     * @param $row
     * @return false|string
     */
    protected function multipleVal($row)
    {
        if ($row[$this->field] && in_array($row[$this->field][0], ['[', '{'])) {
            return $row[$this->field];
        } else {
            return json_encode([$row[$this->field]]);
        }
    }

    /**
     * @param $row
     * @return string
     */
    protected function singleVal($row)
    {
        if ($row[$this->field] && in_array($row[$this->field][0], ['[', '{'])) {
            return implode(', ', json_decode($row[$this->field], true));
        } else {
            return $row[$this->field];
        }
    }

    /**
     * @return void
     */
    public function failed()
    {
        //
    }
}

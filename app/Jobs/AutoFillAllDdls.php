<?php

namespace Vanguard\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Support\Facades\Log;
use Vanguard\Models\Table\Table;
use Vanguard\Repositories\Tablda\TableData\TableDataQuery;
use Vanguard\Services\Tablda\TableDataService;
use Vanguard\User;

class AutoFillAllDdls implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    private $table;
    private $user_id;

    /**
     * AutoFillAllDdls constructor.
     *
     * @param Table $table
     * @param $user_id
     */
    public function __construct(Table $table, $user_id)
    {
        $this->table = $table;
        $this->user_id = $user_id;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $dataService = new TableDataService();
        $sql = new TableDataQuery($this->table);

        $lines = $sql->getQuery()->count();
        $chunk = 500;
        $table_fields = $dataService->loadTableFieldForAutocomplete($this->table);

        for ($cur = 0; ($cur*$chunk) < $lines; $cur++) {

            $all_rows = $sql->getQuery()
                ->offset($cur * $chunk)
                ->limit($chunk)
                ->get()
                ->toArray();

            $dataService->autocompleteRowsByTableDdls($table_fields, $this->table, $all_rows, auth()->id());
            $dataService->newTableVersion($this->table);
        }
    }

    public function failed()
    {
        //
    }
}

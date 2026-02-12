<?php

namespace Vanguard\AppsModules\SmartAutoselectParser;

use Exception;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use Vanguard\Models\Import;
use Vanguard\Models\Table\Table;
use Vanguard\Models\Table\TableFieldLink;
use Vanguard\Models\Table\TableStatuse;
use Vanguard\Repositories\Tablda\TableData\TableDataQuery;
use Vanguard\Repositories\Tablda\TableFieldLinkRepository;
use Vanguard\Repositories\Tablda\TableRepository;
use Vanguard\Services\Tablda\TableDataService;
use Vanguard\Support\SimilarityHelper;

class ExecuteSmartAutoselect implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected TableFieldLink $link;
    protected Table $table;
    protected array $cached = [];
    protected int $user_id;
    protected int $recalc_id;
    protected TableDataService $dataService;

    /**
     * @param int $table_id
     * @param int $link_id
     * @param int $recalc_id
     */
    public function __construct(int $user_id, int $table_id, int $link_id, int $recalc_id)
    {
        $this->table = (new TableRepository())->getTable($table_id);
        $this->link = (new TableFieldLinkRepository())->getLink($link_id);
        $this->user_id = $user_id;
        $this->recalc_id = $recalc_id;
        $this->dataService = new TableDataService();
    }

    /**
     * @throws Exception
     */
    public function handle()
    {
        ini_set('memory_limit', '512M');
        ini_set('max_execution_time', '1200');

        $query = self::getSql($this->table, $this->link, $this->user_id);
        $total = $query->count();

        $query->chunk(10, function (Collection $all_rows) use ($total) {
            foreach ($all_rows as $row) {
                $src = $row[$this->link->_smart_source_fld->field] ?? '';
                if (!$src) {
                    continue;
                }
                if (! empty($this->cached[$src])) {
                    $this->setAndStoreRow($row, $src);
                    continue;
                }

                $result = ['similarity' => 0, 'value' => ''];
                foreach ($this->getDDLvalues($row) as $value) {
                    $result = $this->similarityCheck($result, $src, $value);
                }
                if ($result['value']) {
                    $this->cached[$src] = $result['value'];
                    $this->setAndStoreRow($row, $src);
                }
            }

            $import = Import::find($this->recalc_id);
            $complete = $import->complete + (10 / $total * 100);
            Import::where('id', '=', $this->recalc_id)->update([
                'complete' => min($complete, 100),
            ]);
        });

        Import::where('id', '=', $this->recalc_id)->update([
            'status' => 'done',
        ]);
    }

    /**
     * @param Model $row
     * @return array
     */
    protected function getDDLvalues(Model $row): array
    {
        $src = $row[$this->link->_smart_source_fld->field] ?? '';
        $len = ceil(strlen($src) / 3);
        $token = Arr::first(explode(' ', $src));
        $search = substr($token, 0, $len);

        $ddlValues = $this->dataService->getDDLvalues(
            $this->link->_smart_target_fld->_ddl,
            $row->toArray(),
            $search,
            200,
            ['ddl_applied_field_id' => $this->link->smart_select_target_field_id ?: 0]
        );

        if (!$ddlValues) {
            $ddlValues = $this->dataService->getDDLvalues(
                $this->link->_smart_target_fld->_ddl,
                $row->toArray(),
                '',
                500,
                ['ddl_applied_field_id' => $this->link->smart_select_target_field_id ?: 0]
            );
        }

        return $ddlValues;
    }

    /**
     * @param array $result
     * @param $src
     * @param array $ddlItem
     * @return array
     */
    protected function similarityCheck(array $result, $src, array $ddlItem): array
    {
        if (empty($ddlItem['value'])) {
            return $result;
        }

        $similarity = SimilarityHelper::check($src, $ddlItem['show']);
        if ($similarity > $result['similarity']) {
            $result['similarity'] = $similarity;
            $result['value'] = $ddlItem['value'];
        }

        return $result;
    }

    /**
     * @param Model $row
     * @param $src
     * @return void
     * @throws Exception
     */
    protected function setAndStoreRow(Model $row, $src): void
    {
        $row[$this->link->_smart_target_fld->field] = $this->cached[$src];

        $this->dataService->updateRow($this->table, $row['id'], $row->toArray(), $this->table->user_id);
    }

    /**
     * @return void
     */
    public function failed()
    {
        //
    }

    /**
     * @param Table $table
     * @param TableFieldLink $link
     * @param int $user_id
     * @return Builder
     */
    public static function getSql(Table $table, TableFieldLink $link, int $user_id): Builder
    {
        $statuse = (new TableRepository())->getStatuse($table->id, $user_id);
        $request_params = $statuse ? json_decode($statuse->status_data, true) : [];

        $query = new TableDataQuery($table);
        if ($request_params) {
            $query->testViewAndApplyWhereClauses($request_params, auth()->id());
        }
        $query->checkAndApplyDataRange($link->smart_select_data_range ?: '');

        return $query->getQuery();
    }
}

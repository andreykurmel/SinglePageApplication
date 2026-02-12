<?php

namespace Vanguard\AppsModules\GeneralJson;


use Illuminate\Support\Collection;
use Vanguard\Models\Table\Table;
use Vanguard\Models\Table\TableFieldLink;
use Vanguard\Repositories\Tablda\TableFieldLinkRepository;
use Vanguard\Services\Tablda\HelperService;
use Vanguard\Services\Tablda\TableDataService;
use Vanguard\Services\Tablda\TableService;

trait GeneralJsonTrait
{
    protected $maxLvl = 3;

    /** @var Table */
    protected $table;

    /** @var TableFieldLink */
    protected $link;

    /** @var TableService */
    protected $tableService;

    /** @var TableDataService */
    protected $dataService;

    /** @var HelperService */
    protected $service;

    /** @var array */
    protected $processedTables = [];

    /** @var array */
    protected $cachedTables = [];

    /**
     * @param Table $table
     */
    public function __construct(Table $table)
    {
        $this->table = $this->getCached($table);
        $this->link = (new TableFieldLinkRepository())->findExportLink($table);

        $this->tableService = new TableService();
        $this->dataService = new TableDataService();
        $this->service = new HelperService();
    }

    /**
     * @param $tableOrId
     * @param bool $load
     * @return Table
     */
    protected function getCached($tableOrId, bool $load = true): Table
    {
        $tableId = $tableOrId instanceof Table ? $tableOrId->id : $tableOrId;

        if (!empty($this->cachedTables[$tableId])) {
            return $this->cachedTables[$tableId];
        }

        $table = $tableOrId instanceof Table
            ? $tableOrId
            : $this->tableService->getTable($tableOrId);

        if ($load) {
            $this->loadRelations($table);
        }

        $this->cachedTables[$tableId] = $table;

        return $table;
    }

    /**
     * @param Table $table
     */
    protected function loadRelations(Table $table): void
    {
        $table->load([
            '_fields' => function ($f) {
                $f->with([
                    '_links' => function ($l) {
                        $l->with('_ref_condition');
                    }
                ])
                    ->orderByDesc('is_floating')
                    ->orderBy('order');
            },
        ]);
    }

    /**
     * @param TableFieldLink $link
     * @param int $lvl
     * @return bool
     */
    protected function canProceed(TableFieldLink $link, int $lvl): bool
    {
        if ($link->link_type != 'Record' || !$link->_ref_condition) {
            return false;
        }

        $present = false;
        for ($i = 0; $i < $lvl; $i++) {
            $present = $present || in_array($link->_ref_condition->ref_table_id, $this->processedTables[$i] ?? []);
        }

        return !$present;
    }

    /**
     * @param Table $table
     * @param TableFieldLink|null $link
     * @return Collection
     */
    protected function jsonFields(Table $table, TableFieldLink $link = null): Collection
    {
        $fldIds = $link ? json_decode($link->link_export_drilled_fields) : [];
        return $table->_fields
            ->filter(function ($field) use ($fldIds) {
                return !$fldIds || in_array($field->id, $fldIds);
            });
    }
}
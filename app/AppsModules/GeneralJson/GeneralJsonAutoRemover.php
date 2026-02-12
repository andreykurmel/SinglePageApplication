<?php

namespace Vanguard\AppsModules\GeneralJson;


use Illuminate\Support\Arr;
use Vanguard\Models\Table\Table;
use Vanguard\Models\Table\TableFieldLink;

class GeneralJsonAutoRemover
{
    use GeneralJsonTrait;

    /**
     * @param int $row_id
     * @return string
     */
    public function removeLinkedRows(int $row_id): string
    {
        $this->processedTables = [
            0 => [$this->table->id]
        ];

        $masterRow = $this->dataService->getDirectRow($this->table, $row_id, ['none'])->toArray();
        $this->removeChildren($this->table, $masterRow, 1, $this->link);

        return 'Linked rows were removed!';
    }

    /**
     * @param Table $table
     * @param array $row
     * @param int $lvl
     * @param TableFieldLink|null $parentLink
     * @return void
     */
    protected function removeChildren(Table $table, array $row, int $lvl = 1, TableFieldLink $parentLink = null): void
    {
        if ($lvl > $this->maxLvl) {
            return;
        }

        foreach ($this->jsonFields($table, $parentLink) as $link_header) {
            foreach ($link_header->_links as $link) {
                if ($this->canProceed($link, $lvl) && !in_array($link->_ref_condition->ref_table_id, $this->processedTables[$lvl] ?? [])) {
                    $this->processedTables[$lvl][] = $link->_ref_condition->ref_table_id;
                }
            }
        }

        foreach ($this->jsonFields($table, $parentLink) as $link_header) {
            foreach ($link_header->_links as $link) {
                if ($this->canProceed($link, $lvl) && $link->link_export_json_drill) {
                    $link_table = $this->getCached($link->_ref_condition->ref_table_id);

                    [$rows_count, $link_rows] = $this->dataService->getFieldRows($link_table, $link->toArray(), $row, 1, 250, [], ['none']);

                    foreach ($link_rows as $l_row) {
                        $this->removeChildren($link_table, $l_row, $lvl + 1, $link);
                    }
                    $this->dataService->deleteSelectedRows($link_table, Arr::pluck($link_rows, 'id'));

                }
            }
        }
    }
}
<?php

namespace Vanguard\AppsModules\SmartAutoselectParser;


use Exception;
use Vanguard\Models\Import;
use Vanguard\Models\Table\Table;
use Vanguard\Models\Table\TableFieldLink;
use Vanguard\Repositories\Tablda\TableData\TableDataQuery;

class SmartAutoselectParserModule
{
    protected TableFieldLink $link;
    protected Table $table;

    /**
     * @param Table $table
     * @param TableFieldLink $link
     */
    public function __construct(Table $table, TableFieldLink $link)
    {
        $this->table = $table;
        $this->link = $link;
    }

    /**
     * @param int $row_id
     * @return string
     * @throws Exception
     */
    public function parse(int $row_id): string
    {
        $this->link->load(['_smart_source_fld', '_smart_target_fld._ddl']);

        if (!$this->link->_smart_source_fld) {
            throw new Exception('Source field was not found!', 1);
        }
        if (!$this->link->_smart_target_fld || !$this->link->_smart_target_fld->_ddl) {
            throw new Exception('Target field was not found or doesn`t have a DDL!', 1);
        }

        $query = ExecuteSmartAutoselect::getSql($this->table, $this->link, auth()->id());
        $rowCount = $query->count();

        if ($rowCount) {
            $recalc = Import::create([
                'table_id' => $this->table->id,
                'file' => '',
                'status' => 'initialized',
                'type' => 'SmartAutoselect',
            ]);
            dispatch(new ExecuteSmartAutoselect(auth()->id(), $this->table->id, $this->link->id, $recalc->id));
        }

        return 'Smart Auto Select has been started! Found rows: '.$rowCount.'! You can see the progress in the top-left corner.';
    }
}
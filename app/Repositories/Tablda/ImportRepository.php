<?php

namespace Vanguard\Repositories\Tablda;


use Illuminate\Database\Connection;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Query\Builder;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;
use Vanguard\Models\Table\Table;
use Vanguard\Models\Table\TableField;
use Vanguard\Modules\ColumnAutoSizer;
use Vanguard\Repositories\Tablda\TableData\TableDataQuery;
use Vanguard\Watchers\FieldRenameWatcher;
use Vanguard\Watchers\RefCondTargetFieldWatcher;
use Vanguard\Services\Tablda\HelperService;

class ImportRepository
{
    protected $owner;
    protected $service;

    /**
     * ImportRepository constructor.
     */
    public function __construct(string $owner = '')
    {
        $this->owner = $owner;
        $this->service = new HelperService();
    }

    /**
     * Create table with system columns in DataBase
     *
     * @param $db_name
     * @return string - error message
     */
    public function createTableWithColumns($db_name) {
        //create table
        try {
            Schema::connection('mysql_data')->create($db_name, function (Blueprint $table) {
                $table->increments('id');
                $table->integer('refer_tb_id')->default(0);
                $table->string('row_hash', 64)->nullable();
                $table->string('static_hash', 64)->nullable();
                $table->integer('row_order')->default(0);
                $table->string('request_id', 32)->default('0');
                $table->integer('created_by')->nullable();
                $table->dateTime('created_on')->nullable();
                $table->integer('modified_by')->nullable();
                $table->dateTime('modified_on')->nullable();
            });
            return "";
        } catch (\Exception $e) {
            return "Seems that table with provided name already exists!<br>".$e->getMessage();
        }
    }

    /**
     * Change columns for user`s table in DataBase
     *
     * @param Table $table
     * @param $data array
     * [
     *  'import_type' => string,
     *  'columns' => [
     *   [
     *      status: [add/edit/del],
     *      field: string,
     *      name: string,
     *      f_size: float,
     *      f_type: string,
     *      f_required: int(0|1),
     *   ],
     *   ...
     *  ]
     * ]
     * @return string - error message
     */
    public function modifyTableColumns(Table $table, array &$data)
    {
        $repo = new TableFieldRepository();

        //remove if columns already deleted
        foreach ($data['columns'] as $idx => $col) {
            if ($col['status'] == 'del' && !Schema::connection('mysql_data')->hasColumn($table->db_name, $col['field'])) {
                $db_field = $repo->getFieldBy($table->id, 'field', $col['field']);
                if ($db_field) {
                    $repo->removeFieldCorrectly($table, $db_field, $col);
                }
                unset($data['columns'][$idx]);
            }
        }

        $this->prepareDataForConversion($table, $data);

        //modify table
        try {
            Schema::connection('mysql_data')->table($table->db_name, function (Blueprint $bp_table) use ($data) {
                foreach ($data['columns'] as $col) {
                    //for deleting columns
                    if ($col['status'] == 'del' && !in_array($col['field'], $this->service->system_fields)) {
                        //del column
                        $bp_table->dropColumn($col['field']);
                    }
                }
            });
            Schema::connection('mysql_data')->table($table->db_name, function (Blueprint $bp_table) use ($data) {
                foreach ($data['columns'] as $col) {
                    $col_size = ColumnAutoSizer::get_col_size($col['f_size']);

                    //for changing columns
                    if ($col['status'] == 'edit' && !in_array($col['field'], $this->service->system_fields)) {
                        //edit column
                        $t = $this->defineColumnType($col, $bp_table, $col_size);
                        $t->change();
                    }

                    //for adding columns
                    if ($col['status'] == 'add' && !in_array($col['field'], $this->service->system_fields)) {
                        //add column
                        $t = $this->defineColumnType($col, $bp_table, $col_size);
                    }
                }
            });

            return "";
        } catch (\Exception $e) {
            return "Seems that your table schema is incorrect!<br>".$e->getMessage();
        }
    }

    /**
     * Prepare data for conversion if column type are changing to different type
     *
     * @param Table $table
     * @param array $data
     */
    protected function prepareDataForConversion(Table $table, array $data)
    {
        $columns = $data['columns'];

        $table_fields = $table->_fields()->get();
        $db = $this->service->getConnectionForTable($table);
        $dbcln = (clone $db)->table($table->db_name);

        foreach ($columns as $idx => $col) {
            $oldCol = $table_fields->where('field', $col['field'])->first();

            if (!in_array($data['import_type'], ['csv', 'mysql', 'reference', 'paste'])) {
                //prepare data for edited columns
                if ($col['status'] == 'edit' && $col['field'] && !in_array($col['field'], $this->service->system_fields)) {

                    //Column renamed
                    if ($oldCol && $oldCol->name != $col['name']) {
                        (new FieldRenameWatcher())->watchRename($table, $oldCol->name, $col['name']);
                        (new RefCondTargetFieldWatcher())->watchRename($table->id, $oldCol->name, $col['name']);
                    }

                    //Changed String to Numeric
                    $nums = ['Integer','Decimal','Currency','Percentage'];
                    if ($oldCol && !in_array($oldCol->f_type, $nums) && in_array($col['f_type'], $nums)) {
                        try {
                            //prepare numeric values and remove all symbols
                            $db->table($table->db_name)
                                ->whereNotNull($col['field'])
                                ->select(['id', $col['field']])
                                ->orderBy('id')
                                ->chunk(100, function ($rows) use ($col, $dbcln) {
                                    foreach ($rows as $row) {
                                        $row = (array)$row;
                                        (clone $dbcln)->where('id', $row['id'])->update([
                                            $col['field'] => preg_replace('/[^\d-\.]/i', '', $row[$col['field']])
                                        ]);
                                    }
                                });
                        } catch (\Exception $e) {}
                    }
                }
            }

        }
    }

    /**
     * @param Table $table
     * @param array $col
     */
    public function fillEmptyAutoRows(Table $table, array $col)
    {
        $tbQuery = (new TableDataQuery($table))->getQuery();
        $conn = $this->service->getConnectionForTable($table);
        $sql = $conn->table($table->db_name)
            ->whereNull($col['field'])
            ->orWhere($col['field'], '=', '');

        $lines = $sql->count();
        for ($cur = 0; ($cur * 100) < $lines; $cur++) {
            $all_rows = $sql->offset($cur * 100)->limit(100)->get();
            foreach ($all_rows as $row) {
                $row = (array)$row;
                if ($col['f_type'] == 'Auto String' && empty($row[$col['field']])) {
                    $upd = [];
                    $upd[$col['field']] = $this->service->oneAutoString($table, $col['field'], $col['f_format'] ?? '');
                    (clone $tbQuery)->where('id', '=', $row['id'])->update($upd);
                }
                if ($col['f_type'] == 'Auto Number' && empty($row[$col['field']])) {
                    $upd = [];
                    $fld = $table->_fields()->where('field', $col['field'])->first();
                    $upd[$col['field']] = $this->service->oneAutoNumber($table, $fld, $upd);
                    (clone $tbQuery)->where('id', '=', $row['id'])->update($upd);
                }
            }
        }
    }

    /**
     * @param array $col
     * @param Blueprint $table
     * @param array $col_size
     * @return \Illuminate\Support\Fluent
     */
    public function defineColumnType(array $col, Blueprint $table, array $col_size)
    {
        //Strings
        if (in_array($col['f_type'], ['Attachment','Auto String','Address','User','Email','Phone Number'])) {
            $t = $table->string($col['field'], 128);
        }
        elseif (in_array($col['f_type'], ['String','App'])) {
            $t = $table->string($col['field'], $col['f_size'] > 0 ? (int)$col['f_size'] : 64);
        }
        elseif (in_array($col['f_type'], ['Color'])) {
            $t = $table->string($col['field'], $col['f_size'] > 0 ? (int)$col['f_size'] : 32);
        }
        //Texts
        elseif (in_array($col['f_type'], ['Text','Vote','HTML'])) {
            $t = $table->text($col['field']);
        }
        elseif (in_array($col['f_type'], ['Long Text'])) {
            $t = $table->longText($col['field']);
        }
        //Dates
        elseif ($col['f_type'] == 'Date') {
            $t = $table->date($col['field']);
        }
        elseif ($col['f_type'] == 'Date Time') {
            $t = $table->dateTime($col['field']);
        }
        //Numbers
        elseif (in_array($col['f_type'], ['Gradient Color'])) {
            $t = $table->float($col['field']);
        }
        elseif (in_array($col['f_type'], ['Decimal','Currency','Percentage','Progress Bar'])) {
            $t = $table->decimal($col['field'], $col_size[0], $col_size[1]);
        }
        elseif (in_array($col['f_type'], ['Auto Number','Integer','Rating','Progress Bar','Boolean','Duration','Connected Clouds'])) {
            $t = $table->integer($col['field']);
        }
        elseif (in_array($col['f_type'], ['Table','Table Field'])) {
            $t = $table->unsignedInteger($col['field']);
        }
        else {
            $t = $table->string($col['field']);
        }

        $t->default($this->service->isStringType($col['f_type']) ? "" : null);
        $t->nullable();

        return $t;
    }

    /**
     * Delete table from DataBase
     *
     * @param $db_name
     * @return string - error message
     */
    public function deleteTableInDb($db_name) {
        //delete table
        try {
            Schema::connection('mysql_data')->dropIfExists($db_name);
            return "";
        } catch (\Exception $e) {
            return "Seems that table with provided name already deleted!<br>".$e->getMessage();
        }
    }

    /**
     * @param Table $table
     * @param array $copyAvails - 'columns': [field1, field2, ...]; 'rows': [12, 34, ...]
     * @param string $db_name
     * @param bool $with_data
     */
    public function copyTableInDB(Table $table, array $copyAvails, string $db_name, bool $with_data)
    {
        //config connection
        $db = $this->service->getConnectionForTable($table);

        $columns = $copyAvails
            ? implode(',', $copyAvails['columns'])
            : '*';
        $arcolumns = $copyAvails ? ($copyAvails['columns'] ?: []) : [];

        if ($with_data) {
            $rows = $copyAvails
                ? '`id` IN (' . implode(',', $copyAvails['rows']) . ')'
                : 'true';
        } else {
            $rows = 'false';
        }

        //copy in DB_DATA
        $db->select('CREATE TABLE `' . $db_name . '` SELECT ' . $columns . ' FROM `' . $table->db_name . '` WHERE ' . $rows);
        //sync create/update columns
        if (array_intersect(['created_on', 'modified_on'], $arcolumns)) {
            $db->select('UPDATE `' . $db_name . '` SET `created_on` = now(), `modified_on` = now()');
        }
        //create 'id' as primary key
        if (!$arcolumns || in_array('id', $arcolumns)) {
            $db->select('ALTER TABLE `' . $db_name . '` CHANGE `id` `id` INT(10) AUTO_INCREMENT, add PRIMARY KEY (`id`)');
        } else {
            Log::alert($this->owner.' ImportRepository::copyTableInDB - No "ID"! ('.$db_name.')');
        }
    }
}
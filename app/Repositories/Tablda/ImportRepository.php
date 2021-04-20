<?php

namespace Vanguard\Repositories\Tablda;


use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Vanguard\Models\Table\Table;
use Vanguard\Watchers\RefCondTargetFieldWatcher;
use Vanguard\Services\Tablda\HelperService;

class ImportRepository
{
    protected $service;

    /**
     * ImportRepository constructor.
     */
    public function __construct()
    {
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
                $table->integer('row_order')->default(0);
                $table->integer('request_id')->default(0);
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
     * @param $data array $columns
     * [
     *  [
     *      status: string,
     *      field: string,
     *      f_size: float,
     *      f_type: string,
     *      f_default: string,
     *      f_required: int(0|1),
     *  ],
     *  ...
     * ]
     * @return string - error message
     */
    public function modifyTableColumns(Table $table, array $data) {
        $columns = $data['columns'];

        //remove if columns already deleted
        foreach ($columns as $idx => $col) {
            if ($col['status'] == 'del' && !Schema::connection('mysql_data')->hasColumn($table->db_name, $col['field'])) {
                unset($columns[$idx]);
            }
        }

        $this->prepareDataForConversion($table, $data);

        //modify table
        try {
            Schema::connection('mysql_data')->table($table->db_name, function (Blueprint $bp_table) use ($columns) {
                foreach ($columns as $col) {
                    $col_size = $this->get_col_size($col['f_size']);

                    //for deleting columns
                    if ($col['status'] == 'del' && !in_array($col['field'], $this->service->system_fields)) {
                        //del column
                        $bp_table->dropColumn($col['field']);
                    }

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
     * @param Table $table
     * @param string $formula_fld
     * @param int $size
     */
    public function IncFormulaSize(Table $table, string $formula_fld, int $size)
    {
        if (!$formula_fld || !$size) {
            return;
        }
        Schema::connection('mysql_data')->table($table->db_name, function (Blueprint $bp_table) use ($table, $formula_fld, $size) {
            $cursize = DB::connection('mysql_data')->getDoctrineColumn($table->db_name, $formula_fld)->getLength();
            if ($cursize < $size) {
                $bp_table->string($formula_fld, $size)->change();
            }
        });
    }

    /**
     * @param Table $table
     * @param array $columns
     */
    public function IncreaseColSize(Table $table, array $columns)
    {
        if (!$columns) {
            return;
        }
        Schema::connection('mysql_data')->table($table->db_name, function (Blueprint $bp_table) use ($columns) {
            foreach ($columns as $col) {
                $col_size = $this->get_col_size($col['f_size']);
                $t = $this->defineColumnType($col, $bp_table, $col_size);
                $t->change();
            }
        });
    }

    /**
     * @param string $f_type
     * @param string $f_size
     * @param $val
     * @return bool
     */
    public function notEnoughSize(string $f_type, string $f_size, $val)
    {
        if (in_array($f_type, ['Decimal','Currency','Percentage','Progress Bar'])) {
            $sizes = $this->get_col_size($f_size, true);
            $arrs = $this->get_col_size($val, true, true);
            return $sizes[0] < $arrs[0] || $sizes[1] < $arrs[1];
        } else {
            return floatval($f_size) <= strlen($val);
        }
    }

    /**
     * @param string $f_type
     * @param string $f_size
     * @param $val
     * @return float
     */
    public function increaseSize(string $f_type, string $f_size, $val)
    {
        if (in_array($f_type, ['Decimal','Currency','Percentage','Progress Bar'])) {
            $sizes = $this->get_col_size($f_size, true);
            $arrs = $this->get_col_size($val, true, true);
            $sizes[0] = $sizes[0] < $arrs[0] ? $arrs[0] : $sizes[0];
            $sizes[1] = $sizes[1] < $arrs[1] ? $arrs[1] : $sizes[1];
            return floatval(implode('.', $sizes));
        } else {
            return floatval($f_size) * 2;
        }
    }

    /**
     * @param $f_size
     * @param bool $nosum
     * @param bool $len
     * @return array
     */
    protected function get_col_size($f_size, bool $nosum = false, bool $len = false)
    {
        $col_size = $f_size > 0 ? explode('.', $f_size) : [];
        if ($len) {
            $col_size[0] = strlen($col_size[0] ?? '');
            $col_size[1] = strlen($col_size[1] ?? '');
        }

        if (!isset($col_size[0])) $col_size[0] = 8;
        if (!isset($col_size[1])) $col_size[1] = 2;

        if (!$nosum) {
            $col_size[0] += $col_size[1];
        }
        return $col_size;
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

        foreach ($columns as $idx => $col) {
            $oldCol = $table_fields->where('field', $col['field'])->first();

            //create helper 'formula column' for columns with type = 'Formula'
            /*if ($col['f_type'] == 'Formula') {
                if (
                    !Schema::connection('mysql_data')->hasColumn($table->db_name, $col['field'].'_formula')
                    ||
                    (DB::connection('mysql_data')->getDoctrineColumn($table->db_name, $col['field'].'_formula')->getDefault() != $col['f_default'])
                ) {
                    //drop old 'formula column'
                    if (Schema::connection('mysql_data')->hasColumn($table->db_name, $col['field'].'_formula')) {
                        Schema::connection('mysql_data')->table($table->db_name, function (Blueprint $table) use ($col) {
                            $table->dropColumn($col['field'] . '_formula');
                        });
                    }
                    //create new 'formula column'
                    Schema::connection('mysql_data')->table($table->db_name, function (Blueprint $table) use ($col) {
                        $table->string($col['field'] . '_formula', 255)
                            ->default(addslashes($col['f_default'] ?: ''))
                            ->nullable();
                    });
                }
            }*/

            if (!in_array($data['import_type'], ['csv', 'mysql', 'reference', 'paste'])) {
                //prepare data for edited columns
                if ($col['status'] == 'edit' && $col['field'] && !in_array($col['field'], $this->service->system_fields)) {

                    //Column renamed
                    if ($oldCol && $oldCol->name != $col['name']) {
                        (new RefCondTargetFieldWatcher())->watchRename($table->id, $oldCol->name, $col['name']);
                    }

                    //Formula type is changed to another type
                    /*if ($oldCol && $oldCol->f_type == 'Formula' && $col['f_type'] != 'Formula') {
                        $db->table($table->db_name)->update([
                            $col['field'] => DB::raw($col['field'].'_formula')
                        ]);
                    }
                    //Any type is changed to Formula type
                    if ($oldCol && $oldCol->f_type != 'Formula' && $col['f_type'] == 'Formula') {
                        $db->table($table->db_name)->update([
                            $col['field'].'_formula' => DB::raw($col['field'])
                        ]);
                        $db->table($table->db_name)->update([
                            $col['field'] => ''
                        ]);
                    }*/

                    //delete commas ',' as thousand dividers
                    if (in_array($col['f_type'], ['Integer','Decimal','Currency','Percentage'])) {
                        try {
                            $db->table($table->db_name)
                                ->where($col['field'], 'like' ,'%,%')
                                ->update([
                                    $col['field'] => DB::raw('REPLACE('.$col['field'].', ",", "")')
                                ]);
                        } catch (\Exception $e) {}
                    }
                }

                //set default values for 'edit' columns
                if (in_array($col['status'], ['edit']) && !empty($col['f_default'])) {
                    //if ($col['f_type'] != 'Formula') {
                        $db->table($table->db_name)
                            ->whereNull($col['field'])
                            ->orWhere($col['field'], '')
                            ->update([
                                $col['field'] => $col['f_default']
                            ]);
                    /*} else {
                        $db->table($table->db_name)
                            ->update([
                                $col['field'].'_formula' => $col['f_default']
                            ]);
                    }*/
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
    protected function defineColumnType(array $col, Blueprint $table, array $col_size)
    {
        //Strings
        if (in_array($col['f_type'], ['Attachment',/*'Formula',*/'Address','User'])) {
            $t = $table->string($col['field'], 128);
        }
        elseif (in_array($col['f_type'], ['String','App'])) {
            $t = $table->string($col['field'], $col['f_size'] > 0 ? (int)$col['f_size'] : 64);
        }
        elseif (in_array($col['f_type'], ['Color'])) {
            $t = $table->string($col['field'], $col['f_size'] > 0 ? (int)$col['f_size'] : 32);
        }
        //Texts
        elseif (in_array($col['f_type'], ['Text','Vote'])) {
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
        elseif (in_array($col['f_type'], ['Decimal','Currency','Percentage','Progress Bar'])) {
            $t = $table->decimal($col['field'], $col_size[0], $col_size[1]);
        }
        elseif (in_array($col['f_type'], ['Auto Number','Integer','Star Rating','Progress Bar','Boolean','Duration'])) {
            $t = $table->integer($col['field']);
        }
        else {
            $t = $table->string($col['field']);
        }

        $t->default(null);
        $t->nullable();

        return $t;
    }

    /**
     * Delete table from DataBase
     *
     * @param $db_name
     * @return string - error message
     */
    public function deleteTable($db_name) {
        //delete table
        try {
            Schema::connection('mysql_data')->drop($db_name);
            return "";
        } catch (\Exception $e) {
            return "Seems that table with provided name already deleted!<br>".$e->getMessage();
        }
    }
}
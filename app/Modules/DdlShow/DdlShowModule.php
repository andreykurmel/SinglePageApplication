<?php

namespace Vanguard\Modules\DdlShow;

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Ramsey\Uuid\Uuid;
use Vanguard\Classes\DropdownHelper;
use Vanguard\Models\Table\Table;
use Vanguard\Models\Table\TableField;
use Vanguard\Repositories\Tablda\DDLRepository;
use Vanguard\Repositories\Tablda\TableData\TableDataRepository;
use Vanguard\Repositories\Tablda\TableData\TableDataRowsRepository;
use Vanguard\Repositories\Tablda\TableRepository;

class DdlShowModule
{
    /**
     * @var Table
     */
    protected $table;
    /**
     * @var TableDataRepository
     */
    protected $ddl_repo;
    /**
     * @var TableDataRowsRepository
     */
    protected $data_repo;
    /**
     * @var TableDataRepository
     */
    protected $update_repo;

    /**
     * @param Table $table
     */
    public function __construct(Table $table)
    {
        $this->table = $table;
        $this->ddl_repo = new DDLRepository();
        $this->data_repo = new TableDataRowsRepository();
        $this->update_repo = new TableDataRepository();
    }

    /**
     * @param TableField $field
     * @return string
     */
    public static function syscol(TableField $field): string
    {
        return $field->field . '_ddlshow';
    }

    /**
     * @param Table $table
     * @param TableField $field
     * @return string
     */
    public static function hasSyscol(Table $table, TableField $field): string
    {
        if ($field->ddl_id && (new DDLRepository())->ddlIsIdShow($field->ddl_id)) {
            $syscol = self::syscol($field);
            return Schema::connection('mysql_data')->hasColumn($table->db_name, $syscol)
                ? $syscol
                : '';
        }
        return '';
    }

    /**
     * @param TableField $field
     * @return bool
     */
    public function columnForField(TableField $field): bool
    {
        $new_col = false;
        try {
            if ($field->ddl_id && $this->ddl_repo->ddlIsIdShow($field->ddl_id)) {
                if (!Schema::connection('mysql_data')->hasColumn($this->table->db_name, self::syscol($field))) {
                    Schema::connection('mysql_data')->table($this->table->db_name, function (Blueprint $bp_table) use ($field) {
                        $bp_table->string(self::syscol($field), 128)->nullable();
                    });
                    $new_col = true;
                }
            } else {
                if (Schema::connection('mysql_data')->hasColumn($this->table->db_name, self::syscol($field))) {
                    Schema::connection('mysql_data')->table($this->table->db_name, function (Blueprint $bp_table) use ($field) {
                        $bp_table->dropColumn(self::syscol($field));
                    });
                }
            }
        } catch (\Exception $e) {
            \Log::error("DdlShowModule::columnForField - ".$e->getMessage());
        }
        return $new_col;
    }

    /**
     * @param TableField|null $directfld
     * @param array|null $singlerow
     * @return array|null
     * @throws \Exception
     */
    public function fillShows(TableField $directfld = null, array $singlerow = null)
    {
        $update = [];
        try {
            $ddlidshows = $this->table->_fields()->whereNotNull('ddl_id')->get();
            $ddlidshows = $ddlidshows->filter(function ($fld) use ($directfld) {
                return (!$directfld || $directfld->id == $fld->id)
                    && $this->ddl_repo->ddlIsIdShow($fld->ddl_id);
            });

            if ($ddlidshows->count()) {
                $clauses = [];
                if ($singlerow) {
                    $clauses['row_id'] = $singlerow['id'];
                }

                //Chunk table
                $off = 0;
                do {
                    $rows = $this->data_repo->listRows($this->table, $clauses, $off);
                    $off += 100;

                    //Update rows
                    foreach ($rows as $row) {
                        $update = [];
                        foreach ($ddlidshows as $field) {
                            $show = DropdownHelper::valOrShow($field->field, $row);
                            $old = $row[self::syscol($field)] ?? '';
                            if ($show != $old) {
                                $update[self::syscol($field)] = $show;
                            }
                        }
                        if ($update) {
                            $this->update_repo->quickUpdate($this->table, $row['id'], $update, false);
                        }
                    }

                } while ($rows->count());

                $hash = $singlerow['row_hash'] ?? '';
                if ($hash != 'cf_temp') {
                    (new TableRepository())->onlyUpdateTable($this->table->id, ['version_hash' => Uuid::uuid4()]);
                }
            }
        } catch (\Exception $e) {
            //\Log::error("DdlShowModule::fillShows - ".$e->getMessage());
        }
        return $singlerow ? array_merge($singlerow, $update) : null;
    }
}
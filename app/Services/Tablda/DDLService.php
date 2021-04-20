<?php


namespace Vanguard\Services\Tablda;


use Vanguard\Models\DDL;
use Vanguard\Models\DDLItem;
use Vanguard\Models\DDLReference;
use Vanguard\Models\Table\Table;
use Vanguard\Models\Table\TableData;
use Vanguard\Repositories\Tablda\DDLRepository;
use Vanguard\Repositories\Tablda\FileRepository;
use Vanguard\Repositories\Tablda\TableFieldRepository;
use Vanguard\User;

class DDLService
{
    protected $fileRepo;
    protected $DDLRepository;
    protected $fieldRepository;
    protected $tableDataService;

    /**
     * DDLService constructor.
     */
    public function __construct()
    {
        $this->fileRepo = new FileRepository();
        $this->DDLRepository = new DDLRepository();
        $this->fieldRepository = new TableFieldRepository();
        $this->tableDataService = new TableDataService();
    }

    /**
     * Get DDL.
     *
     * @param $ddl_id
     * @return mixed
     */
    public function getDDL($ddl_id) {
        return $this->DDLRepository->getDDL($ddl_id);
    }

    /**
     * @param Table $table
     * @param int $ddl_id
     * @return mixed
     */
    public function tableDDL(Table $table, int $ddl_id) {
        return $table->_ddls()
            ->where('id', $ddl_id)
            ->first();
    }

    /**
     * @param Table $table
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function returnDDLS(Table $table)
    {
        return $table->_ddls()
            ->with('_items', '_references')
            ->get();
    }

    /**
     * Add DDL.
     *
     * @param Table $table
     * @param $data
     * [
     *  +table_id: int,
     *  +name: string,
     *  +type: string,
     *  -notes: string,
     * ]
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function addDDL(Table $table, $data)
    {
        $this->DDLRepository->addDDL( array_merge($data, ['table_id' => $table->id]) );
        return $this->returnDDLS($table);
    }

    /**
     * @param Table $table
     * @param $ddl_id
     * @param $data
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function updateDDL(Table $table, $ddl_id, $data)
    {
        $ddl = $this->tableDDL($table, $ddl_id);
        $this->DDLRepository->updateDDL($ddl->id, $data);
        return $this->returnDDLS($table);
    }

    /**
     * @param Table $table
     * @param $ddl_id
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function deleteDDL(Table $table, $ddl_id)
    {
        $this->DDLRepository->deleteDDL($ddl_id);
        return $this->returnDDLS($table);
    }

    /**
     * Fill DDL By Distinctive Values From Field.
     *
     * @param Table $table
     * @param $table_field_id
     * @param $ddl_id
     * @return mixed
     */
    public function fillDDL(Table $table, $table_field_id, $ddl_id)
    {
        $field = $this->fieldRepository->getField($table_field_id);
        $values = $this->tableDataService->getFieldValues($table, $field, $field->field);
        return $this->DDLRepository->fillDDL($ddl_id, $values);
    }

    /**
     * Fill DDL By Options from string.
     *
     * @param $options
     * @param $ddl_id
     * @return mixed
     */
    public function parseOptions($options, $ddl_id)
    {
        $values = preg_split('/\r\n|\r|\n|;|,/', $options);
        foreach ($values as $v) {
            $v = trim($v);
        }
        return $this->DDLRepository->fillDDL($ddl_id, $values);
    }

    /**
     * @param DDL $ddl
     * @return \Illuminate\Database\Eloquent\Collection
     */
    protected function returnDDLitems(DDL $ddl)
    {
        return $ddl->_items()->get();
    }

    /**
     * @param DDL $ddl
     * @param $data
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function addDDLItem(DDL $ddl, $data)
    {
        $this->DDLRepository->addDDLItem( array_merge($data, ['ddl_id' => $ddl->id]) );
        return $this->returnDDLitems($ddl);
    }

    /**
     * @param DDL $ddl
     * @param $ddl_item_id
     * @param $data
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function updateDDLItem(DDL $ddl, $ddl_item_id, $data)
    {
        $this->DDLRepository->updateDDLItem($ddl_item_id, $data);
        return $this->returnDDLitems($ddl);
    }

    /**
     * @param DDL $ddl
     * @param $ddl_item_id
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function deleteDDLItem(DDL $ddl, $ddl_item_id)
    {
        $this->DDLRepository->deleteDDLItem($ddl_item_id);
        return $this->returnDDLitems($ddl);
    }

    /**
     * @param DDL $ddl
     * @return \Illuminate\Database\Eloquent\Collection
     */
    protected function returnDDLref(DDL $ddl)
    {
        return $ddl->_references()->get();
    }

    /**
     * @param DDL $ddl
     * @param $data
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function addDDLReference(DDL $ddl, $data)
    {
        $this->DDLRepository->addDDLReference( array_merge($data, ['ddl_id' => $ddl->id]) );
        return $this->returnDDLref($ddl);
    }

    /**
     * @param DDL $ddl
     * @param $ddl_reference_id
     * @param $data
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function updateDDLReference(DDL $ddl, $ddl_reference_id, $data)
    {
        $this->DDLRepository->updateDDLReference($ddl_reference_id, $data);
        return $this->returnDDLref($ddl);
    }

    /**
     * @param DDL $ddl
     * @param $ddl_reference_id
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function deleteDDLReference(DDL $ddl, $ddl_reference_id)
    {
        $this->DDLRepository->deleteDDLReference($ddl_reference_id);
        return $this->returnDDLref($ddl);
    }

    /**
     * Add new option to Regular DDL.
     *
     * @param DDL $ddl
     * @param $val
     * @param array $extra_options
     * @return array
     */
    public function newRegularOption(DDL $ddl, $val, array $extra_options)
    {
        $user = auth()->user();
        $ddl->load('_table');
        $table = $ddl->_table ?? new Table();

        if ($user->can('insert', [TableData::class, $table])) {
            $this->addDDLItem( $ddl, array_merge($extra_options, ['option' => $val]) );
            return ['err' => '', 'val' => $val];
        } else {
            return ['err' => 'error', 'val' => ''];
        }
    }

    /**
     * Add new option to RefTable in DDL.
     *
     * @param DDL $ddl
     * @param $val
     * @param $ddl_ref_id
     * @param array $fields
     * @return array
     */
    public function newReferencingOption(DDL $ddl, $val, $ddl_ref_id, array $fields)
    {
        $user = auth()->user();

        $ddl_reference = $ddl->_references()
            ->where('id', $ddl_ref_id)
            ->with([
                '_ref_condition' => function($r) {
                    $r->with('_ref_table');
                },
                '_target_field',
                '_image_field',
                '_show_field',
            ])
            ->first();
        $table = $ddl_reference->_ref_condition->_ref_table ?? new Table();
        $field = $ddl_reference->_target_field->field ?? null;

        if ($user->can('insert', [TableData::class, $table])) {
            if ($field) {
                $fields = array_merge($fields, [$field => $val]);
            }

            return [
                'err' => '',
                'val' => $this->tableDataService->insertRow($table, $fields, $user->id)
            ];
        } else {
            return ['err' => 'error', 'val' => ''];
        }
    }
}
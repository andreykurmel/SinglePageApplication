<?php


namespace Vanguard\Services\Tablda;


use Exception;
use Illuminate\Support\Collection;
use Vanguard\Models\DDL;
use Vanguard\Models\DDLReference;
use Vanguard\Models\DDLReferenceColor;
use Vanguard\Models\Table\Table;
use Vanguard\Models\Table\TableData;
use Vanguard\Models\Table\TableField;
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
     * @param int $ddl_id
     * @return DDL
     */
    public function getDDL(int $ddl_id): DDL
    {
        return $this->DDLRepository->getDDL($ddl_id);
    }

    /**
     * @param Table $table
     * @param string $ddl_name
     * @param array $item_values
     * @return DDL
     */
    public function createDDLwithItems(Table $table, string $ddl_name, array $item_values): DDL
    {
        $ddl = $this->DDLRepository->addDDL(['table_id' => $table->id, 'name' => $ddl_name]);
        foreach ($item_values as $item_value) {
            $this->DDLRepository->addDDLItem(['ddl_id' => $ddl->id, 'option' => $item_value]);
        }
        return $ddl;
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
     * @return Collection
     */
    public function addDDL(Table $table, $data)
    {
        $this->DDLRepository->addDDL(array_merge($data, ['table_id' => $table->id]));
        return $this->returnDDLS($table);
    }

    /**
     * @param Table $table
     * @return Collection
     */
    public function returnDDLS(Table $table)
    {
        return $table->_ddls()
            ->with('_items', '_references')
            ->get();
    }

    /**
     * @param Table $table
     * @param $ddl_id
     * @param $data
     * @return Collection
     */
    public function updateDDL(Table $table, $ddl_id, $data)
    {
        $ddl = $this->tableDDL($table, $ddl_id);
        $this->DDLRepository->updateDDL($ddl->id, $data);
        return $this->returnDDLS($table);
    }

    /**
     * @param Table $table
     * @param int $ddl_id
     * @return mixed
     */
    public function tableDDL(Table $table, int $ddl_id)
    {
        return $table->_ddls()
            ->where('id', $ddl_id)
            ->first();
    }

    /**
     * @param Table $table
     * @param $ddl_id
     * @return Collection
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
        $values = $this->tableDataService->distinctiveFieldValues($table, $field);
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
     * @param $ddl_item_id
     * @param $data
     * @return Collection
     */
    public function updateDDLItem(DDL $ddl, $ddl_item_id, $data)
    {
        $this->DDLRepository->updateDDLItem($ddl_item_id, $data);
        return $this->returnDDLitems($ddl);
    }

    /**
     * @param DDL $ddl
     * @return Collection
     */
    protected function returnDDLitems(DDL $ddl)
    {
        return $ddl->_items()->get();
    }

    /**
     * @param DDL $ddl
     * @param $ddl_item_id
     * @return Collection
     */
    public function deleteDDLItem(DDL $ddl, $ddl_item_id)
    {
        $this->DDLRepository->deleteDDLItem($ddl_item_id);
        return $this->returnDDLitems($ddl);
    }

    /**
     * @param DDL $ddl
     * @param $data
     * @return Collection
     */
    public function addDDLReference(DDL $ddl, $data)
    {
        $this->DDLRepository->addDDLReference(array_merge($data, ['ddl_id' => $ddl->id]));
        return $this->returnDDLref($ddl);
    }

    /**
     * @param DDL $ddl
     * @return Collection
     */
    protected function returnDDLref(DDL $ddl)
    {
        return $ddl->_references()
            ->with('_reference_colors')
            ->get();
    }

    /**
     * @param DDL $ddl
     * @param $ddl_reference_id
     * @param $data
     * @return Collection
     */
    public function updateDDLReference(DDL $ddl, $ddl_reference_id, $data)
    {
        $this->DDLRepository->updateDDLReference($ddl_reference_id, $data);
        return $this->returnDDLref($ddl);
    }

    /**
     * @param DDL $ddl
     * @param $ddl_reference_id
     * @return Collection
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
        $user = auth()->user() ?: new User();
        $ddl->load('_table');
        $table = $ddl->_table ?? new Table();

        if ($user->can('insert', [TableData::class, $table])) {
            $this->addDDLItem($ddl, array_merge($extra_options, ['option' => $val]));
            return ['err' => '', 'val' => $val];
        } else {
            return ['err' => 'error', 'val' => ''];
        }
    }

    /**
     * @param DDL $ddl
     * @param $data
     * @return Collection
     */
    public function addDDLItem(DDL $ddl, $data)
    {
        $this->DDLRepository->addDDLItem(array_merge($data, ['ddl_id' => $ddl->id]));
        return $this->returnDDLitems($ddl);
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
        $user = auth()->user() ?: new User();

        $ddl_reference = $ddl->_references()
            ->where('id', $ddl_ref_id)
            ->with([
                '_ref_condition' => function ($r) {
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

    /**
     * @param int $id
     * @return DDLReference
     */
    public function getDdlRef(int $id): DDLReference
    {
        return $this->DDLRepository->getDdlRef($id);
    }

    /**
     * @param $ddl_id
     * @return DDLReferenceColor
     */
    public function getDdlRefColor($ddl_id): DDLReferenceColor
    {
        return $this->DDLRepository->getDdlRefColor($ddl_id);
    }

    /**
     * @param int $ddl_ref_id
     * @param array $data
     * @return Collection
     */
    public function addDDLReferenceColor(int $ddl_ref_id, array $data): Collection
    {
        $this->DDLRepository->addDDLReferenceColor($ddl_ref_id, $data);
        return $this->DDLRepository->allRefColors($ddl_ref_id);
    }

    /**
     * @param int $ref_color_id
     * @param array $data
     * @return DDLReferenceColor
     */
    public function updateDDLReferenceColor(int $ref_color_id, array $data): DDLReferenceColor
    {
        return $this->DDLRepository->updateDDLReferenceColor($ref_color_id, $data);
    }

    /**
     * @param int $ddl_ref_id
     * @param int $ref_color_id
     * @return Collection
     * @throws Exception
     */
    public function deleteDDLReferenceColor(int $ddl_ref_id, int $ref_color_id): Collection
    {
        $this->DDLRepository->deleteDDLReferenceColor($ref_color_id);
        return $this->DDLRepository->allRefColors($ddl_ref_id);
    }

    /**
     * @param DDL $ddl
     * @param DDLReference $reference
     * @param string $behavior
     * @return Collection
     * @throws Exception
     */
    public function createAndLoadRefColors(DDL $ddl, DDLReference $reference, string $behavior = 'create'): Collection
    {
        if ($behavior != 'create') {
            $this->DDLRepository->removeAllRefColors($reference);
            $colors = collect([]);
        } else {
            $colors = $this->DDLRepository->allRefColors($reference->id);
        }

        if (!$colors->count()) {
            $fill = $behavior == 'fill' ? 'auto' : '';
            $this->createRefColors($ddl, $reference, $fill);
            $colors = $this->DDLRepository->allRefColors($reference->id);
        }
        return $colors;
    }

    /**
     * @param DDL $ddl
     * @param DDLReference $reference
     * @param string $color
     */
    protected function createRefColors(DDL $ddl, DDLReference $reference, string $color = ''): void
    {
        $values = $this->tableDataService->getRowsFromDdlReference($ddl, $reference, [], '', 100);
        $values = array_pluck($values, 'show');
        $this->DDLRepository->massInsertRefColors($reference, $values, $color);
    }
}
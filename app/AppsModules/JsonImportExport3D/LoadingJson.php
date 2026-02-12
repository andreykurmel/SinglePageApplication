<?php

namespace Vanguard\AppsModules\JsonImportExport3D;

use Illuminate\Support\Arr;
use Tablda\DataReceiver\TabldaDataInterface;
use Vanguard\AppsModules\StimWid\StimSettingsRepository;
use Vanguard\Models\Table\Table;
use Vanguard\Models\Table\TableField;
use Vanguard\Modules\Settinger;
use Vanguard\Repositories\Tablda\FileRepository;
use Vanguard\Repositories\Tablda\TableData\TableDataRepository;
use Vanguard\Repositories\Tablda\TableRepository;
use Vanguard\Services\Tablda\FileService;

class LoadingJson
{
    /** @var StimSettingsRepository */
    protected $stimRepo;

    /** @var TableRepository */
    protected $tableRepo;

    /** @var TableDataRepository */
    protected $dataRepo;

    /** @var string */
    protected $loadingDB;

    /** @var Table */
    protected $tabldaLoading;

    /** @var TableField */
    protected $jsonField;
    protected $modelCorrField;
    protected $usergroupCorrField;

    /**
     *
     */
    public function __construct(string $code = 'stim_3d')
    {
        $this->stimRepo = new StimSettingsRepository($code);
        $this->tableRepo = new TableRepository();
        $this->dataRepo = new TableDataRepository();

        //Preparation
        $this->loadingDB = $this->stimRepo->findMasterTb('3d:loading', true)->data_table;
        $this->tabldaLoading = $this->tableRepo->getTableByDB($this->loadingDB);

        $jsonCorrField = $this->stimRepo->corrFieldsByOptions('is_export_import:file')
            ->where('data_table', '=', $this->loadingDB)
            ->first();
        $this->jsonField = $this->tabldaLoading->_fields()->where('field', '=', $jsonCorrField->data_field)->first();
        if (!$this->jsonField) {
            throw new \Exception('File field is empty! Please check "is_export_import:file" option in Correspondence Fields!', 1);
        }

        $this->modelCorrField = $this->stimRepo->getCorrFieldsBy('data_table', $this->loadingDB)
            ->where('app_field', '=', 'model')
            ->first();
        $this->usergroupCorrField = $this->stimRepo->getCorrFieldsBy('data_table', $this->loadingDB)
            ->where('app_field', '=', 'usergroup')
            ->first();
    }

    /**
     * @param string $dbname
     * @return string
     */
    protected function childrenKey(string $dbname): string
    {
        $table = $this->tableRepo->getTableByDB($dbname);
        return preg_replace('/[^\w\d]+/i', '_', $table->name) . '_' . $table->id;
    }

    /**
     * @param $loadingId
     * @return void
     * @throws \Exception
     */
    public function import($loadingId): string
    {
        $masterRow = $this->dataRepo->getDirectRow($this->tabldaLoading, $loadingId)->toArray();

        //Receive JSON
        $content = (new FileService())->getContent($this->tabldaLoading->id, $this->jsonField->id, $loadingId);
        if (!$content) {
            throw new \Exception('JSON for import was not found!', 1);
        }
        $json = json_decode($content, true);

        //Parse master row
        if ($json['Loading']['Model'] ?? '') {
            $model = $this->masterFromJson($json['Loading']['Model'], $masterRow);
            $this->createOrUpdate($this->tabldaLoading, $model, $masterRow);
        } else {
            throw new \Exception('JSON doesn`t have Master Row!', 1);
        }

        //Parse children rows
        foreach ($json['Loading']['Children'] ?? [] as $tableNameId => $tableData) {
            $tableId = Arr::last(explode('_', $tableNameId));
            $tabldaTable = $this->tableRepo->getTable($tableId);
            foreach ($tableData as $row) {
                $updates = $this->masterRowNaming($masterRow, $row, $tabldaTable->db_name);
                $this->createOrUpdate($tabldaTable, $updates, $masterRow);
            }
        }

        return 'Import completed!';
    }

    /**
     * @param $loadingId
     * @return void
     * @throws \Exception
     */
    public function export($loadingId): string
    {
        $masterRow = $this->dataRepo->getDirectRow($this->tabldaLoading, $loadingId)->toArray();

        //Receive JSON
        $content = (new FileService())->getContent($this->tabldaLoading->id, $this->jsonField->id, $loadingId);
        $present_json = $content ? json_decode($content, true) : [];
        $json_obj = JsonFormat3D::get($present_json);


        //Fill JSON
        //Set or update master row
        $json_obj['Loading']['Model'] = array_replace_recursive($json_obj['Loading']['Model'], $masterRow);

        $groupedCorrFields = $this->stimRepo->getCorrFieldsBy('link_table_db', $this->loadingDB)
            ->groupBy('data_table')
            ->toArray();
        foreach ($groupedCorrFields as $dbname => $corrFields) {
            $inheritedRows = $this->inheritedRows($dbname, $corrFields, $masterRow);

            //Set or update inherited rows
            $key = $this->childrenKey($dbname);
            $json_obj['Loading']['Children'][$key] = array_replace_recursive($json_obj['Loading']['Children'][$key] ?? [], $inheritedRows);
        }


        //Store JSON
        $json_string = json_encode($json_obj, JSON_PRETTY_PRINT);
        $json_file = $masterRow[$this->modelCorrField->data_field] . '.json';
        (new FileRepository())->insertFileAlias($this->tabldaLoading->id, $this->jsonField->id, $loadingId, $json_file, $json_string);

        return 'Export completed!';
    }

    /**
     * @param string $dbname
     * @param array $corrFields
     * @param array $masterRow
     * @return array
     */
    protected function inheritedRows(string $dbname, array $corrFields, array $masterRow): array
    {
        $tabldaInherited = $this->tableRepo->getTableByDB($dbname);
        $sql = $this->dataRepo->getTDQ($tabldaInherited);

        $applied = false;
        foreach ($corrFields as $corrField) {
            if ($corrField['link_field_db']) {
                $applied = true;
                $sql->where($corrField['data_field'], '=', $masterRow[$corrField['link_field_db']] ?? null);
            }
        }
        if (!$applied) {
            $sql->whereRaw('false');
        }

        return $sql->get()->toArray();
    }

    /**
     * @param Table $table
     * @param array $newRow
     * @return void
     * @throws \Exception
     */
    protected function createOrUpdate(Table $table, array $newRow, array $masterRow): void
    {
        $oldRow = $this->dataRepo->getDirectRow($table, $newRow['id']);
        $oldRow = $oldRow ? $oldRow->toArray() : [];

        if ($oldRow && $this->theSameAsMaster($oldRow, $masterRow, $table->db_name)) {
            $updates = array_replace_recursive($oldRow, $newRow);
            $this->dataRepo->updateRow($table, $newRow['id'], $updates, $table->user_id);
        } else {
            $this->dataRepo->insertRow($table, $newRow, $table->user_id);
        }
    }

    /**
     * @param array $masterRow
     * @param array $childRow
     * @param string $childDbName
     * @return array
     */
    protected function masterRowNaming(array $masterRow, array $childRow, string $childDbName): array
    {
        $corrFields = $this->stimRepo->getCorrFieldsBy('link_table_db', $this->loadingDB)
            ->where('data_table', '=', $childDbName);

        foreach ($corrFields as $corrField) {
            if ($corrField['link_field_db'] && $masterRow[$corrField['link_field_db']] ?? '') {
                $childRow[$corrField['data_field']] = $masterRow[$corrField['link_field_db']];
            }
        }

        return $childRow;
    }

    /**
     * @param array $childRow
     * @param array $masterRow
     * @param string $childDbName
     * @return bool
     */
    protected function theSameAsMaster(array $childRow, array $masterRow, string $childDbName): bool
    {
        $corrFields = $this->stimRepo->getCorrFieldsBy('link_table_db', $this->loadingDB)
            ->where('data_table', '=', $childDbName);

        $count = 0;
        $result = true;
        foreach ($corrFields as $corrField) {
            if ($corrField['link_field_db']) {
                $count++;
                $result = $result && ($masterRow[$corrField['link_field_db']] ?? '') == ($childRow[$corrField['data_field']]);
            }
        }

        return $result && $count;
    }

    /**
     * @param array $jsonMaster
     * @param array $currentMaster
     * @return array
     */
    protected function masterFromJson(array $jsonMaster, array $currentMaster): array
    {
        if ($currentMaster[$this->usergroupCorrField->data_field] ?? '') {
            $jsonMaster[$this->usergroupCorrField->data_field] = $currentMaster[$this->usergroupCorrField->data_field];
        }

        if ($currentMaster[$this->modelCorrField->data_field] ?? '') {
            $jsonMaster[$this->modelCorrField->data_field] = $currentMaster[$this->modelCorrField->data_field];
        }

        return $jsonMaster;
    }
}
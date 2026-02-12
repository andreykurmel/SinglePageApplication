<?php

namespace Vanguard\AppsModules\GeneralJson;


use Carbon\Carbon;
use Exception;
use Vanguard\Classes\RefConditionHelper;
use Vanguard\Models\DataSetPermissions\TableRefCondition;
use Vanguard\Models\Table\Table;
use Vanguard\Models\Table\TableFieldLink;
use Vanguard\Repositories\Tablda\FileRepository;
use Vanguard\Repositories\Tablda\TableFieldLinkRepository;
use Vanguard\Repositories\Tablda\TableFieldRepository;
use Vanguard\Services\Tablda\FileService;

class GeneralJsonImportExport
{
    use GeneralJsonTrait;

    /** @var array */
    protected $json;

    /** @var array */
    protected $jsonSys = [];

    /** @var array */
    protected $cachedRCs = [];

    /** @var array */
    protected $cachedPresents = [];

    /** @var array */
    protected $importMasterRow = [];

    /**
     * @param int $ref_cond_id
     * @return TableRefCondition
     */
    protected function getCachedRc(int $ref_cond_id): TableRefCondition
    {
        if (!empty($this->cachedRCs[$ref_cond_id])) {
            return $this->cachedRCs[$ref_cond_id];
        }

        $rc = TableRefCondition::where('id', '=', $ref_cond_id)
            ->with([
                '_items' => function ($i) {
                    $i->with([
                        '_field:id,table_id,field,f_type',
                        '_compared_field:id,table_id,field,f_type,input_type'
                    ]);
                }
            ])
            ->first();

        $this->cachedRCs[$ref_cond_id] = $rc;

        return $rc;
    }

    /**
     * @param int $row_id
     * @param int $file_field_id
     * @return string
     * @throws Exception
     */
    public function import(int $row_id, int $file_field_id): string
    {
        $this->importMasterRow = $this->dataService->getDirectRow($this->table, $row_id, ['none'])->toArray();

        //Receive JSON
        $content = (new FileService())->getContent($this->table->id, $file_field_id, $row_id);
        if (!$content) {
            throw new Exception('JSON for import was not found!', 1);
        }
        $this->json = json_decode($content, true) ?? [];

        //Parse rows
        foreach ($this->json as $tableName => $tableData) {
            if ($tableName != 'Model') {
                $this->importJsonRow($tableName, $tableData);
            }
        }

        return 'Import completed!';
    }

    /**
     * @param string $tableName
     * @param array $jsonRow
     * @param array $parentRow
     * @return void
     * @throws Exception
     */
    protected function importJsonRow(string $tableName, array $jsonRow, array $parentRow = []): void
    {
        $master = $jsonRow['Records'] ?? $jsonRow['Self'];//$jsonRow['Self'] - backward compatability
        $parentJson = $this->createOrUpdate($tableName, $master, $parentRow);

        foreach ($jsonRow['Links'] ?? [] as $subTableName => $arrayData) {
            foreach ($arrayData as $tableData) {
                $this->importJsonRow($subTableName, $tableData, $parentJson);
            }
        }
    }

    /**
     * @param string $tableName
     * @param array $newRow
     * @param array $parentRowDb
     * @return array
     * @throws Exception
     */
    protected function createOrUpdate(string $tableName, array $newRow, array $parentRowDb = []): array
    {
        $sys = $this->json['Model'][$tableName] ?? [];
        if (!$sys) {
            return [];
        }

        $table = $this->getCached($sys['id'], false);
        $newRowDb = $this->namesToDbFields($sys['fields'], $newRow);

        if ($parentRowDb) {
            //Children
            $rc = $this->getCachedRc($sys['linked_ref_cond_id']);
            $parentLinks = RefConditionHelper::getLinkParams($rc, $parentRowDb);

            $newRowDb = array_merge($newRowDb, $parentLinks);
            if (!$this->getAlreadyPresentRow($table, $newRowDb, $parentLinks)) {
                $this->dataService->tableDataRepository->insertRow($table, $newRowDb, $table->user_id);
            }
            return $newRowDb;
        } else {
            //Master Row (fill only empty fields)
            foreach ($this->importMasterRow as $field => $value) {
                if (!$value) {
                    $this->importMasterRow[$field] = $newRowDb[$field] ?? null;
                }
            }
            $this->dataService->tableDataRepository->updateRow($table, $this->importMasterRow['id'], $this->importMasterRow, $table->user_id);
            return $this->importMasterRow;
        }
    }

    /**
     * @param Table $table
     * @param array $jsonRow
     * @param array $parentLinks
     * @return bool
     */
    protected function getAlreadyPresentRow(Table $table, array $jsonRow, array $parentLinks): bool
    {
        if (empty($this->cachedPresents[$table->id])) {
            $sql = $this->dataService->tableDataRepository->getAllRowsSql($table, [], $table->user_id);
            foreach ($parentLinks as $field => $value) {
                $sql->where($field, '=', $value);
            }
            $this->cachedPresents[$table->id] = $sql->get();
        }

        $collection = $this->cachedPresents[$table->id];
        foreach ($jsonRow as $field => $value) {
            if ($value && !in_array($field, $this->service->system_fields)) {
                $collection = $collection->where($field, '=', $value);
            }
        }
        return $collection->count() > 0;
    }

    /**
     * @param array $conversions
     * @param array $row
     * @return array
     */
    protected function namesToDbFields(array $conversions, array $row): array
    {
        $dbRow = [];
        foreach ($conversions as $name => $db_field) {
            if (!empty($row[$db_field])) {
                continue;
            }

            $dbRow[$db_field] = $row[$name] ?? null;
            if (is_array($dbRow[$db_field])) {
                $dbRow[$db_field] = $dbRow[$db_field]['value'] ?? null;
            }
        }
        return $dbRow;
    }

    /**
     * @param int $row_id
     * @param int $file_field_id
     * @param int $link_id
     * @return string
     */
    public function export(int $row_id, int $file_field_id, int $link_id = 0): string
    {
        $this->jsonSys = [];
        $this->processedTables = [
            0 => [$this->table->id]
        ];
        $masterRow = $this->dataService->getDirectRow($this->table, $row_id, ['none'])->toArray();

        //Receive JSON
        try {
            $content = (new FileService())->getContent($this->table->id, $file_field_id, $row_id);
        } catch (Exception $e) {
            $content = null;
        }
        $json_obj = $content ? json_decode($content, true) : [];

        $tbn = $this->table->name;
        $json_obj[$tbn] = $this->getJsonRow($this->table, $masterRow, $json_obj[$tbn] ?? [], $this->link);
        $json_obj[$tbn]['Links'] = $this->getJsonChildren($this->table, $masterRow, $json_obj[$tbn]['Links'] ?? [], 1, $this->link);
        $json_obj['Model'] = $this->jsonSys;

        //Store JSON
        $json_string = json_encode($json_obj, JSON_PRETTY_PRINT);
        $json_file = $this->jsonFileName($link_id, $masterRow);
        (new FileRepository())->insertFileAlias($this->table->id, $file_field_id, $row_id, $json_file, $json_string);

        return 'Export completed!';
    }

    /**
     * @param int $link_id
     * @param $masterRow
     * @return string
     */
    protected function jsonFileName(int $link_id, $masterRow): string
    {
        $fieldRepo = new TableFieldRepository();
        $linkRepo = new TableFieldLinkRepository();

        $link = $linkRepo->getLink($link_id);
        if ($link) {
            $array = [];
            if ($link->json_export_filename_table) {
                $array[] = preg_replace('/[^\w\d]/i', '_', $this->table->name);
            }
            if ($link->json_export_filename_link) {
                $array[] = preg_replace('/[^\w\d]/i', '_', $link->name);
            }
            if ($link->json_export_filename_fields) {
                $fields = $link->json_export_filename_fields;
                $fields = is_array($fields) ? $fields : json_decode($fields, true);
                foreach ($fields as $fieldId) {
                    $fld = $fieldRepo->getField($fieldId);
                    if ($fld) {
                        $array[] = preg_replace('/[^\w\d]/i', '_', $masterRow[$fld->field] ?? $fld->name);
                    }
                }
            }
            if ($link->json_export_filename_year) {
                $array[] = Carbon::now()->format('Ymd');
            }
            if ($link->json_export_filename_time) {
                $array[] = Carbon::now()->format('His');
            }
            $name = implode('_', $array) . '.json';

        } else {
            $name = preg_replace('/[^\w\d]/i', '_', $this->table->name) . '_'
                . Carbon::now()->format('Ymd_His') . '.json';
        }
        return $name;
    }

    /**
     * @param Table $table
     * @param array $row
     * @param array $oldJson
     * @param TableFieldLink|null $link
     * @return array
     */
    protected function getJsonRow(Table $table, array $row, array $oldJson, TableFieldLink $link = null): array
    {
        if (empty($this->jsonSys[$table->name])) {
            $this->jsonSys[$table->name] = [
                'id' => $table->id,
                'fields' => $this->jsonFields($table, $link)
                    ->mapWithKeys(function ($field) {
                        return [$field->name => $field->field];
                    })
                    ->toArray(),
                'linked_ref_cond_id' => $link ? $link->table_ref_condition_id : null,
            ];
        }

        $mappedRow = $this->jsonFields($table, $link)
            ->mapWithKeys(function ($field) use ($row) {
                $array = ['value' => $row[$field->field] ?? null];
                if ($field->unit) {
                    $array['unit'] = $field->unit;
                }
                if ($field->tooltip) {
                    $array['description'] = $field->tooltip;
                }
                return [$field->name => $array];
            })
            ->toArray();

        return array_replace_recursive($oldJson, [
            'Records' => $mappedRow,
            'Links' => [],
        ]);
    }

    /**
     * @param Table $table
     * @param array $row
     * @param array $oldJson
     * @param int $lvl
     * @param TableFieldLink|null $parentLink
     * @return array
     */
    protected function getJsonChildren(Table $table, array $row, array $oldJson, int $lvl = 1, TableFieldLink $parentLink = null): array
    {
        $json = [];

        if ($lvl > $this->maxLvl) {
            return $json;
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
                    $json[$link_table->name] = [];

                    [$rows_count, $link_rows] = $this->dataService->getFieldRows($link_table, $link->toArray(), $row, 1, 250, [], ['none']);

                    foreach ($link_rows as $l_row) {
                        $oldRow = collect($oldJson)->where('id', '=', $l_row['id'])->first() ?: [];
                        $jsonRow = $this->getJsonRow($link_table, $l_row, $oldRow, $link);
                        $jsonRow['Links'] = $this->getJsonChildren($link_table, $l_row, $jsonRow['Links'], $lvl + 1, $link);
                        $json[$link_table->name][] = $jsonRow;
                    }

                }
            }
        }

        return $json;
    }
}
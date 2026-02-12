<?php

namespace Vanguard\AppsModules;


use Exception;
use Illuminate\Support\Arr;
use Vanguard\Models\Table\Table;
use Vanguard\Models\Table\TableFieldLink;
use Vanguard\Repositories\Tablda\UserConnRepository;
use Vanguard\Services\Tablda\FileService;
use Vanguard\Services\Tablda\TableDataService;

trait ParseAndStoreDaLoadings
{
    /** @var TableDataService */
    protected $dataService;

    /** @var string */
    protected $prefix = '';

    /** @var array */
    protected $masterRow = [];

    /** @var TableFieldLink */
    protected $link;

    /** @var Table */
    protected $table;

    /** @var Table */
    protected $outputTable;

    /**
     * @return void
     */
    protected function removeRecordsByInheritedValues(): void
    {
        if ($this->table->id == $this->outputTable->id) {
            return;
        }

        $conditions = [];
        foreach ($this->link->_link_app_correspondences as $daLoading) {
            if ($daLoading->is_active && $daLoading->_master_field && $daLoading->_field) {
                $conditions[$daLoading->_field->field] = $this->masterRow[$daLoading->_master_field->field] ?? '';
            }
        }

        if ($conditions) {
            $this->dataService->removeByParams($this->outputTable, $conditions);
        }
    }

    /**
     * @param array $content
     * @param int $row_id
     * @return void
     * @throws Exception
     */
    protected function parseContent(array $content, int $row_id): void
    {
        $rows = [];
        foreach ($this->link->_link_app_correspondences as $daLoading) {
            if ($daLoading->is_active && $daLoading->column_key && $daLoading->_field) {
                foreach ($content as $idx => $value) {
                    if (empty($rows[$idx])) {
                        $rows[$idx] = [];
                    }
                    $val = $value[$daLoading->column_key] ?? '';
                    $rows[$idx][$daLoading->_field->field] = is_array($val) ? Arr::first($val) : $val;
                }
            }
        }

        foreach ($rows as $idx => $row) {
            if (empty($row['request_id'])) {
                $rows[$idx]['request_id'] = $this->generalKey($row_id, $idx + 1);
            }
            foreach ($this->link->_link_app_correspondences as $daLoading) {
                if ($daLoading->is_active && $daLoading->_master_field && $daLoading->_field) {
                    $rows[$idx][$daLoading->_field->field] = $this->masterRow[$daLoading->_master_field->field] ?? '';
                }
            }
        }

        $this->storeRows($rows, $row_id);
    }

    /**
     * @param array $rows
     * @param int $row_id
     * @return void
     * @throws Exception
     */
    protected function storeRows(array $rows, int $row_id): void
    {
        $refTable = $this->outputTable;
        if (!$refTable) {
            throw new Exception('Output table was not found!', 1);
        }
        foreach ($rows as $idx => $row) {
            $eriKey = $this->generalKey($row_id, $idx + 1);
            $present = $this->dataService->getRowBy($refTable, 'request_id', $eriKey);

            if ($refTable->id == $this->table->id) {
                $present = $this->masterRow;
            }

            if ($present) {
                $this->dataService->updateRow($refTable, $present['id'], $row, $refTable->user_id);
            } else {
                $this->dataService->insertRow($refTable, $row);
            }
        }
    }

    /**
     * @param int $row_id
     * @param int $idx
     * @return string
     */
    protected function generalKey(int $row_id, int $idx): string
    {
        return $this->prefix
            . $this->link->id
            . '_'
            . $row_id
            . '_'
            . $idx;
    }

    /**
     * @param string $prompt
     * @param int $field_id
     * @param int $row_id
     * @param int|null $ai_id
     * @return array
     * @throws Exception
     */
    protected function parseDocumentByAI(string $prompt, int $field_id, int $row_id, int $ai_id = null): array
    {
        $repo = new UserConnRepository();
        $apiKey = $repo->getUserApi($ai_id ?: $this->table->openai_tb_key_id ?: 0, true);
        if (! $apiKey) {
            $apiKey = $repo->defaultAiAcc($this->table->user_id);
        }

        $service = new FileService();
        $doc = $service->getContent($this->table->id, $field_id, $row_id);
        if (!$doc) {
            throw new Exception('The document/image was not found!', 1);
        }

        try {
            $filename = $service->getFile($this->table->id, $field_id, $row_id)->filename;

            $content = $apiKey->AiInteface()->documentRecognition($prompt, $doc, $filename);
            $content = trim(preg_replace('/`/i','', $content));
            $content = trim(preg_replace('/^json/i','', $content));
            $array = json_decode($content, true);
        } catch (Exception $e) {
            throw new Exception($e->getMessage(), 1);
        }

        if (! $array) {
            throw new Exception("No data was found in the document/image! Response from the AI: $content", 1);
        }

        return $array;
    }
}
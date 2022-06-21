<?php

namespace Vanguard\Modules\Airtable;

use Carbon\Carbon;
use Exception;
use GuzzleHttp\Client;
use Vanguard\Helpers\DateHelper;

class AirtableApi
{
    /**
     * @var string
     */
    protected $api_key;

    /**
     * @var string
     */
    protected $base_key;

    /**
     * @var string
     */
    protected $fromtype;

    /**
     * @param string $api_key
     * @param string $base_key
     */
    public function __construct(string $api_key, string $base_key, string $fromtype = '')
    {
        $this->api_key = $api_key;
        $this->base_key = $base_key;
        $this->fromtype = $fromtype;
    }

    /**
     * @param string $table
     * @return array - ['Field' => 'Type', ...]
     * @throws Exception
     */
    public function tableFields(string $table): array
    {
        $rows = $this->loadRows($table);
        $headers = [];
        foreach ($rows['records'] as $row) {
            foreach ($row['fields'] as $hdr => $val) {
                if (empty($headers[$hdr]) || $headers[$hdr] == 'String') {
                    $headers[$hdr] = $this->guessType($val);
                }
            }
        }
        return $headers;
    }

    /**
     * @param string $tablda_type
     * @return string
     */
    public function typeConvertToAir(string $tablda_type): string
    {
        switch ($tablda_type) {
            case 'Attachment': return 'Attachment';
            case 'Date': return 'Date';
            case 'Date Time': return 'Date Time';
            default: return 'Single Line Text';
        }
    }

    /**
     * @param $val
     * @return string
     */
    protected function guessType($val): string
    {
        if (is_array($val) && is_array(array_first($val))) {
            return 'Attachment';
        }
        if (DateHelper::isDate($val)) {
            return 'Date Time';
        }
        return 'String';
    }

    /**
     * @param string $table
     * @param string $offset
     * @return array - ['records' => [], 'offset' => 'string']
     * @throws Exception
     */
    public function loadRows(string $table, string $offset = ''): array
    {
        $curl = new Client();
        try {
            $table = $offset ? $table.'?offset='.$offset : $table;
            $response = $curl->get($this->tableUrl($table), [
                'headers' => $this->headers(),
            ]);
        } catch (Exception $e) {
            if ($e->getCode() == 404) {
                switch ($this->fromtype) {
                    case 'folder/master': throw new Exception('The “master” table is not found.', 1);
                        break;
                    case 'folder/table': throw new Exception('No table with following name ('.$table.') is found. The importing will be skipped.', 1);
                        break;
                    default: throw new Exception('No table with entered table name is found.', 1);
                        break;
                }
            } else {
                throw new Exception($e->getMessage(), 1);
            }
        }
        return json_decode($response->getBody()->getContents(), true);
    }

    /**
     * @param string $table
     * @param string $column
     * @return array
     * @throws Exception
     */
    public function fetchColValues(string $table, string $column): array
    {
        $values = [];
        $rows = $this->loadRows($table);
        $values = array_merge($values, $this->extractColumn($rows['records'], $column));
        while ($rows['offset'] ?? '') {
            $rows = $this->loadRows($table, $rows['offset']);
            $values = array_merge($values, $this->extractColumn($rows['records'], $column));
        }
        return array_unique(array_filter($values));
    }

    /**
     * @param array $rows
     * @param string $column
     * @return array
     */
    protected function extractColumn(array $rows, string $column): array
    {
        return array_map(function ($item) use ($column) {
            return $item['fields'][$column] ?? '';
        }, $rows);
    }

    /**
     * @param string $table
     * @return string
     */
    protected function tableUrl(string $table): string
    {
        return 'https://api.airtable.com/v0/' . $this->base_key . '/' . $table;
    }

    /**
     * @return string[]
     */
    protected function headers(): array
    {
        return [
            'Authorization' => 'Bearer ' . $this->api_key,
        ];
    }
}
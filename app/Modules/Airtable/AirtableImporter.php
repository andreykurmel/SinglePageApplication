<?php

namespace Vanguard\Modules\Airtable;

use Carbon\Carbon;
use Illuminate\Support\Facades\Log;
use Vanguard\Helpers\DateHelper;
use Vanguard\Models\Table\Table;
use Vanguard\Repositories\Tablda\FileRepository;
use Vanguard\Repositories\Tablda\UserRepository;
use Vanguard\Services\Tablda\DDLService;
use Vanguard\Services\Tablda\TableFieldService;

class AirtableImporter
{
    /**
     * @var DDLService
     */
    protected $ddlService;
    /**
     * @var TableFieldService
     */
    protected $fieldService;
    /**
     * @var FileRepository
     */
    protected $fileRepository;
    /**
     * @var UserRepository
     */
    protected $userRepository;

    /**
     * @var array
     */
    protected $air_column_values = [];
    /**
     * @var array
     */
    protected $air_attachments = [];
    /**
     * @var array
     */
    protected $air_attachment_row = [];

    /**
     *
     */
    public function __construct()
    {
        $this->ddlService = new DDLService();
        $this->fieldService = new TableFieldService();
        $this->fileRepository = new FileRepository();
        $this->userRepository = new UserRepository();
    }

    /**
     * @param $value
     * @param array $col
     * @return string
     */
    public function parseAirtableVal($value, array $col): string
    {
        if (empty($col['_source_type'])) {
            return $this->airtableConverter($value, $col);
        } else {
            return $this->airtableWithTypes($value, $col);
        }
    }

    /**
     * @param $value
     * @param array $col
     * @return string
     */
    protected function airtableConverter($value, array $col): string
    {
        //Date
        if (DateHelper::isDate($value)) {
            return (new Carbon($value))->toDateTimeString();
        }

        //Multi-select
        if (is_array($value)) {
            $value = array_map(function ($item) use ($col) {
                if (is_array($item)) {
                    //Attachment
                    $this->saveAirAttachRow($col, $item);
                    return '';
                } else {
                    //M-Select
                    $this->setAirColVal($col, $item);
                    return $item;
                }
            }, $value);

            $value = array_filter($value);
            $value = $value ? json_encode($value) : '';
        }

        return (string)$value;
    }

    /**
     * @param array $col
     * @param $value
     */
    protected function saveAirAttachRow(array $col, array $value)
    {
        $field = $col['field'];
        if (empty($this->air_attachment_row[$field])) {
            $this->air_attachment_row[$field] = [];
        }
        $this->air_attachment_row[$field][] = ($value['url'] ?? '');
    }

    /**
     * @param array $col
     * @param $value
     * @param string $type
     */
    protected function setAirColVal(array $col, $value, string $type = 'S-Select')
    {
        $name = $col['name'];
        $field = $col['field'];
        if (empty($this->air_column_values[$field])) {
            $this->air_column_values[$field] = [
                'ddl_name' => $name,
                'ddl_values' => [$value],
                'ddl_type' => $type,
            ];
        } else {
            $this->air_column_values[$field]['ddl_values'][] = $value;
        }
    }

    /**
     * @param $value
     * @param array $col
     * @return string
     */
    protected function airtableWithTypes($value, array $col): string
    {
        //Convert 'value' to correct type.
        $array_types = ['Attachment','Barcode','Button','Collaborator','Created By','Last Modified By',
            'Multiple Select','Linked Record','Lookup'];
        if (in_array($col['_source_type'], $array_types)) {
            $value = is_array($value) ? $value : [$value];//Should be Array
        } else {
            $value = is_array($value) ? '' : $value;//Should be plain value
        }

        //Attachment
        if (in_array($col['_source_type'], ['Attachment'])) {
            foreach ($value as $item) {
                $this->saveAirAttachRow($col, is_array($item) ? $item : []);
            }
            $value = '';
        }

        //USER
        if (in_array($col['_source_type'], ['Collaborator','Created By','Last Modified By'])) {
            $value = $value['email'] ?? '';
            $user = $this->userRepository->getByEmails([$value], true);
            $value = $user ? $user->id : $value;
        }

        //SELECTS
        if (in_array($col['_source_type'], ['Single Select'])) {
            $this->setAirColVal($col, $value, 'S-Select');
            $value = strval($value);
        }
        if (in_array($col['_source_type'], ['Multiple Select','Linked Record','Lookup'])) {
            $value = array_map(function ($item) use ($col) {
                $this->setAirColVal($col, $item, 'M-Select');
                return $item;
            }, $value);
            $value = array_filter($value);
            $value = $value ? json_encode($value) : '';
        }

        //Bool
        if (in_array($col['_source_type'], ['Checkbox'])) {
            $value = $value ? '1' : '';
        }

        //INT
        if (in_array($col['_source_type'], ['Auto Number','Duration','Rating','Count'])) {
            $value = intval($value);
        }

        //FLOAT
        if (in_array($col['_source_type'], ['Currency','Number','Percent'])) {
            $value = floatval($value);
        }

        //STRING
        if (in_array($col['_source_type'], ['Email','Long Text','Phone Number','Single Line Text','Formula','URL'])) {
            $value = strval($value);
        }
        if (in_array($col['_source_type'], ['Barcode'])) {
            $value = $value['text'] ?? '';
        }
        if (in_array($col['_source_type'], ['Button'])) {
            $value = $value['label'] ?? '';
        }

        //DATE
        if (in_array($col['_source_type'], ['Date'])) {
            $value = (new Carbon($value))->toDateString();
        }

        //DATE TIME
        if (in_array($col['_source_type'], ['Date Time','Created Time','Last Modified Time'])) {
            $value = (new Carbon($value))->toDateTimeString();
        }

        return (string)$value;
    }

    /**
     * @param int $row_id
     */
    public function moveAttachRowToArray(int $row_id)
    {
        if ($this->air_attachment_row) {
            $this->air_attachments[$row_id] = $this->air_attachment_row;
            $this->air_attachment_row = [];
        }
    }

    /**
     *
     */
    public function saveAttachmentsFiles(Table $table)
    {
        foreach ($this->air_attachments as $row_id => $attachments) {
            foreach ($attachments as $db_field => $attach_array) {
                $fld = $table->_fields->where('field', '=', $db_field)->first();
                foreach ($attach_array as $attach) {
                    $this->fileRepository->insertFileLink($table->id, $fld->id, $row_id, $attach);
                }
            }
        }
    }

    /**
     * @param Table $table
     */
    public function createDDLs(Table $table)
    {
        foreach ($this->air_column_values as $ddl_field => $array) {
            $ddl = $this->ddlService->createDDLwithItems($table, $array['ddl_name'], array_filter($array['ddl_values']));
            $this->fieldService->applyDDLtoField($table, $ddl_field, $ddl, $array['ddl_type']);
        }
    }
}
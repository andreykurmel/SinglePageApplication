<?php

namespace Vanguard\Modules;

use Exception;
use Illuminate\Support\Collection;
use Vanguard\Classes\DropdownHelper;
use Vanguard\Models\AppSetting;
use Vanguard\Models\DataSetPermissions\TableRefCondition;
use Vanguard\Models\Table\TableField;
use Vanguard\Repositories\Tablda\TableData\TableDataQuery;
use Vanguard\Repositories\Tablda\TableData\TableDataRepository;
use Vanguard\Repositories\Tablda\TableData\TableDataRowsRepository;
use Vanguard\Services\Tablda\TableDataService;
use Vanguard\Support\FrontNotificator;

class MirrorModule
{
    /**
     * @var TableDataRepository
     */
    protected $repo;

    /**
     * @var TableDataRowsRepository
     */
    protected $rowRepo;

    /**
     * @var int|mixed|string
     */
    protected $limitMirrors;

    /**
     * @var false
     */
    protected $limitReached;

    /** @var array */
    protected array $mirrorTheSame = [];

    /**
     *
     */
    public function __construct()
    {
        $this->repo = new TableDataRepository();
        $this->rowRepo = new TableDataRowsRepository();

        //set global limit
        $limit = AppSetting::where('key', '=', 'app_max_mirror_records')->first();
        $this->limitMirrors = $limit && $limit->val ? $limit->val : PHP_INT_MAX;
        $this->limitReached = false;
    }

    /**
     * @param TableField $field
     * @throws Exception
     */
    public function fill(TableField $field)
    {
        if ($field && $field->_mirror_rc && $field->_mirror_field) {
            $sql = new TableDataQuery($field->_table);
            $this->chunkedFill($sql, $field);
        }
    }

    /**
     * @param TableField $field
     * @throws Exception
     */
    public function clear(TableField $field)
    {
        if (!$field->_mirror_rc || !$field->_mirror_field) {
            $sql = new TableDataQuery($field->_table);
            $sql->getQuery()->update([
                $field->field => null,
            ]);
            (new TableDataService())->newTableVersion($field->_table);
        }
    }

    /**
     * @param int $table_id
     * @throws Exception
     */
    public function reFillAll(int $table_id)
    {
        foreach ($this->mirrorFields($table_id, 'self') as $field) {
            $this->fill($field, $field->_mirror_rc);
        }
    }

    /**
     * @param TableDataQuery $sql
     * @param TableField $field
     * @throws Exception
     */
    protected function chunkedFill(TableDataQuery $sql, TableField $field)
    {
        $lines = $sql->getQuery()->count();
        $chunk = 100;

        for ($cur = 0; ($cur * $chunk) < $lines; $cur++) {

            $offset = $cur * $chunk;
            $all_rows = $sql->getQuery()
                ->offset($offset)
                ->limit($chunk)
                ->get()
                ->toArray();

            foreach ($all_rows as $i => $row) {
                $this->fillMirrorRow($field, $row);
            }
        }

        if ($this->limitReached) {
            FrontNotificator::sendFromJob($field->table_id, 'The number of records meeting mirroring criteria exceeds the set mirroring limit: '.$this->limitMirrors.'. Only '.$this->limitMirrors.' records are mirrored.');
        }

        (new TableDataService())->newTableVersion($field->_table);
    }

    /**
     * @param TableField $field
     * @param array $row
     * @return void
     */
    protected function fillMirrorRow(TableField $field, array $row): void
    {
        if (!$field->_mirror_field || !$field->_mirror_rc) {
            return;
        }

        //Get referenced rows for mirroring
        $referenced = $this->repo->getReferencedRows($field->_mirror_rc, $row, ['*'], 10);
        $refTb = $field->_mirror_rc->_ref_table;
        $this->rowRepo->attachSpecialFields($referenced, $refTb, $refTb->user_id, ['refs']);

        //Get updated Mirrors
        $fields = [];
        if (! empty($this->mirrorTheSame[$field->id])) {
            foreach ($this->mirrorTheSame[$field->id] as $mirrorField) {
                $fields = $this->getMirroredValue($mirrorField, $row, $referenced, $fields);
            }
        } else {
            $fields = $this->getMirroredValue($field, $row, $referenced, $fields);
        }

        //Save mirrored values
        if ($fields) {
            $this->repo->quickUpdate($field->_table, $row['id'], $fields, false);
        }
    }

    /**
     * @param TableField $field
     * @param array $row
     * @param Collection $referenced
     * @param array $results
     * @return array
     */
    protected function getMirroredValue(TableField $field, array $row, Collection $referenced, array $results): array
    {
        $fields = $this->getUpdateArray($field, $referenced);
        $presentVal = ($row[$field->field] ?? '');
        $presentValChanged = $field->mirror_editable ? ($row[$field->field.'_mirror'] ?? '') : '';
        if ($presentVal != $fields[$field->field] && !$presentValChanged) {
            $results[$field->field] = $fields[$field->field];
        }
        return $results;
    }

    /**
     * @param TableField $field
     * @param Collection $referenced
     * @return array
     */
    protected function getUpdateArray(TableField $field, Collection $referenced): array
    {
        //apply table limit
        if ($field->_table && $field->_table->max_mirrors_in_one_row) {
            $this->limitMirrors = $field->_table->max_mirrors_in_one_row;
        }

        $fields = [];
        $fields[$field->field] = null;
        if ($referenced->count() == 1 || $field->mirror_one_value) {
            $ref_row = $referenced->first();
            $fields[$field->field] = $this->valOrddl($field, $ref_row);
        } else
        if ($referenced->count() > 1) {
            $ref_datas = [];
            foreach ($referenced as $idx => $ref_row) {
                if ($idx >= $this->limitMirrors) {
                    $this->limitReached = true;
                    continue;
                }
                $ref_datas[] = $this->valOrddl($field, $ref_row);
            }
            $fields[$field->field] = json_encode($ref_datas);
        }

        if (strlen($fields[$field->field]) > 64) {
            $fields = $this->repo->setSpecialValues($field->_table, $fields);
        }

        return $fields;
    }

    /**
     * @param TableField $field
     * @param $ref_row
     * @return mixed
     */
    protected function valOrddl(TableField $field, $ref_row)
    {
        $mirfield = $field->_mirror_field->field;
        switch ($field->mirror_part) {
            case 'show':
                $val = DropdownHelper::valOrShow($mirfield, $ref_row, 'show_val'); break;
            case 'value':
                $val = $ref_row[$mirfield] ?? $ref_row['id'] ?? ''; break;
            default:
                $val = $ref_row['id'] ?? ''; break;
        }
        return $val;
    }

    /**
     * @param int $table_id
     * @param array $watchrow
     * @throws Exception
     */
    public function watch(int $table_id, array $watchrow)
    {
        $hash = $watchrow['row_hash'] ?? '';
        //the other tables
        if ($hash != 'cf_temp') {
            foreach ($this->mirrorFields($table_id) as $field) {
                $this->affectedRows($field, $watchrow);
            }
        }

        //self table
        $updated = 0;
        foreach ($this->mirrorFields($table_id, 'self') as $field) {
            if (strlen($watchrow['id'] ?? '')) {
                $this->fillMirrorRow($field, $watchrow);
                $updated++;
            }
        }
        if ($updated && $hash != 'cf_temp') {
            (new TableDataService())->newTableVersion($field->_table);
        }
    }

    /**
     * @param int $table_id
     * @param string $self
     * @return TableField[]
     */
    protected function mirrorFields(int $table_id, string $self = '')
    {
        $collection = TableField::whereNotNull('mirror_rc_id')
            ->whereHas('_mirror_rc', function ($ref) use ($table_id, $self) {
                if ($self) {
                    $ref->where('table_id', '=', $table_id);
                } else {
                    $ref->where('ref_table_id', '=', $table_id);
                }
            })
            ->with('_table', '_mirror_rc')
            ->get();

        $same = $collection->first()?->mirror_rc_id;
        foreach ($collection as $field) {
            $same = $same == $field->mirror_rc_id ? $same : '';
        }
        if ($same) {
            $this->mirrorTheSame[$collection->first()->id] = clone $collection;
            $collection = $collection->slice(0, 1);
        }

        return $collection;
    }

    /**
     * @param TableField $field
     * @param array $watchrow
     * @throws Exception
     */
    protected function affectedRows(TableField $field, array $watchrow)
    {
        if ($watchrow) {
            $sql = new TableDataQuery($field->_mirror_rc->_table);
            $sql->getAffectedRows(clone $field->_mirror_rc, $watchrow);
        } else {
            $sql = new TableDataQuery($field->_table);
        }
        $this->chunkedFill($sql, $field);
    }
}
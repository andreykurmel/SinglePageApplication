<?php

namespace Vanguard\Modules;

use Exception;
use Illuminate\Support\Collection;
use Vanguard\Classes\DropdownHelper;
use Vanguard\Models\DataSetPermissions\TableRefCondition;
use Vanguard\Models\MirrorDatas;
use Vanguard\Models\Table\TableField;
use Vanguard\Repositories\Tablda\TableData\TableDataQuery;
use Vanguard\Repositories\Tablda\TableData\TableDataRepository;
use Vanguard\Repositories\Tablda\TableData\TableDataRowsRepository;
use Vanguard\Services\Tablda\TableDataService;

class RemoteFilesModule
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
     *
     */
    public function __construct()
    {
        $this->repo = new TableDataRepository();
        $this->rowRepo = new TableDataRowsRepository();
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

            $all_rows = $sql->getQuery()
                ->offset($cur * $chunk)
                ->limit($chunk)
                ->get()
                ->toArray();

            foreach ($all_rows as $i => $row) {
                $this->fillMirrorRow($field, $row);
            }
        }

        (new TableDataService())->newTableVersion($field->_table);
    }

    /**
     * @param TableField $field
     * @param $row
     * @throws Exception
     */
    protected function fillMirrorRow(TableField $field, $row)
    {
        if (!$field->_mirror_field || !$field->_mirror_rc) {
            return;
        }

        $referenced = $this->repo->getReferencedRows($field->_mirror_rc, $row, ['*'], 10);
        $refTb = $field->_mirror_rc->_ref_table;
        $this->rowRepo->attachSpecialFields($referenced, $refTb, $refTb->user_id, ['refs']);

        //Save mirrored row ids
        $referenced_ids = $referenced->pluck('id')->toArray();
        $condition = [
            'table_id' => $field->_table->id,
            'table_field_id' => $field->id,
            'row_id' => $row['id'],
        ];
        MirrorDatas::updateOrCreate(
            $condition,
            array_merge($condition, ['mirror_row_ids' => ($referenced_ids ? json_encode($referenced_ids) : null)])
        );

        //Mirror value save into row
        $fields = $this->getUpdateArray($field, $referenced);
        if (($row[$field->field] ?? '') != $fields[$field->field]) {
            $this->repo->quickUpdate($field->_table, $row['id'], $fields, false);
        }
    }

    /**
     * @param TableField $field
     * @param Collection $referenced
     * @return array
     */
    protected function getUpdateArray(TableField $field, Collection $referenced): array
    {
        $fields = [];
        $fields[$field->field] = null;
        if ($referenced->count() == 1) {
            $ref_row = $referenced->first();
            $fields[$field->field] = $this->valOrddl($field, $ref_row);
        } else
        if ($referenced->count() > 1) {
            $ref_datas = [];
            foreach ($referenced as $ref_row) {
                $ref_datas[] = $this->valOrddl($field, $ref_row);
            }
            $fields[$field->field] = json_encode($ref_datas);
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
        //the other tables
        foreach ($this->mirrorFields($table_id) as $field) {
            $this->affectedRows($field, $watchrow);
        }

        //self table
        foreach ($this->mirrorFields($table_id, 'self') as $field) {
            if (!empty($watchrow['id'])) {
                $this->fillMirrorRow($field, $watchrow);
                (new TableDataService())->newTableVersion($field->_table);
            }
        }
    }

    /**
     * @param int $table_id
     * @param string $self
     * @return TableField[]
     */
    protected function mirrorFields(int $table_id, string $self = '')
    {
        return TableField::whereNotNull('mirror_rc_id')
            ->whereHas('_mirror_rc', function ($ref) use ($table_id, $self) {
                if ($self) {
                    $ref->where('table_id', '=', $table_id);
                } else {
                    $ref->where('ref_table_id', '=', $table_id);
                }
            })
            ->with('_table', '_mirror_rc')
            ->get();
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
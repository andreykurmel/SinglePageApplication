<?php

namespace Vanguard\Repositories\Tablda\Permissions;


use Ramsey\Uuid\Uuid;
use Vanguard\Models\Dcr\TableDataRequest;
use Vanguard\Models\Dcr\TableDataRequest2Fields;
use Vanguard\Services\Tablda\HelperService;

class CopyDataRequestRepository
{
    /**
     * @var HelperService
     */
    protected $service;
    /**
     * @var TableDataRequestRepository
     */
    protected $dataReqRepo;

    /**
     * TableDataRequestRepository constructor.
     */
    public function __construct()
    {
        $this->service = new HelperService();
        $this->dataReqRepo = new TableDataRequestRepository();
    }

    /**
     * @param TableDataRequest $from
     * @param TableDataRequest $to
     * @param array $permis_fields
     * @param bool $full_copy
     * @return bool
     * @throws \Exception
     */
    public function copyDataRequest(TableDataRequest $from, TableDataRequest $to, array $permis_fields = [], bool $full_copy = false)
    {
        if ($from->id == $to->id) {
            return false;
        }
        $arr = [
            'id' => $to->id,
            'name' => $to->name,
            'is_template' => $to->is_template,
            'link_hash' => Uuid::uuid4(),
            'dcr_hash' => Uuid::uuid4(),
        ];
        $updates = $permis_fields
            ? $this->service->filter_keys($from->toArray(), $permis_fields)
            : $from->toArray();
        $updates = array_merge($this->service->delSystemFields($updates), $arr);
        $res = TableDataRequest::where('id', '=', $to->id)->update($updates);

        if ($from->table_id == $to->table_id && $full_copy) {
            $this->copyDefaults($from, $to);
            $this->copyColumnGroups($from, $to);
            $this->copyLinkedTables($from, $to);
            $this->copyFieldsPivot($from, $to);
        }

        return $res;
    }

    /**
     * @param TableDataRequest $from
     * @param TableDataRequest $to
     */
    protected function copyDefaults(TableDataRequest $from, TableDataRequest $to): void
    {
        $to->_default_fields()->delete();
        foreach ($from->_default_fields as $default_field) {
            $this->dataReqRepo->insertDefField(array_merge(
                $default_field->toArray(),
                ['table_data_requests_id' => $to->id]
            ));
        }
    }

    /**
     * @param TableDataRequest $from
     * @param TableDataRequest $to
     */
    protected function copyColumnGroups(TableDataRequest $from, TableDataRequest $to): void
    {
        $to->_data_request_columns()->delete();
        foreach ($from->_data_request_columns as $request_column) {
            $this->dataReqRepo->updateTableColDataRequest(
                $to->id,
                $request_column->table_column_group_id,
                $request_column->view,
                $request_column->edit
            );
        }
    }

    /**
     * @param TableDataRequest $from
     * @param TableDataRequest $to
     */
    protected function copyFieldsPivot(TableDataRequest $from, TableDataRequest $to): void
    {
        $to->_fields_pivot()->delete();
        foreach ($from->_fields_pivot as $pivot) {
            TableDataRequest2Fields::insert(array_merge($pivot->getAttributes(), [
                'id' => null,
                'table_data_requests_id' => $to->id
            ]));
        }
    }

    /**
     * @param TableDataRequest $from
     * @param TableDataRequest $to
     */
    protected function copyLinkedTables(TableDataRequest $from, TableDataRequest $to): void
    {
        $to->_dcr_linked_tables()->delete();
        foreach ($from->_dcr_linked_tables as $linked_table) {
            $this->dataReqRepo->insertLinkedTable($to->id, $linked_table->toArray());
        }
    }
}
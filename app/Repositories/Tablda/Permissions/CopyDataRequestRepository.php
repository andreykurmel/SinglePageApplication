<?php

namespace Vanguard\Repositories\Tablda\Permissions;


use Ramsey\Uuid\Uuid;
use Vanguard\Models\Dcr\TableDataRequest;
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
     * @param bool $as_template
     * @param array $permis_fields
     * @return mixed
     */
    public function copyDataRequest(TableDataRequest $from, TableDataRequest $to, bool $as_template = false, array $permis_fields = [])
    {
        $arr = [
            'name' => $to->name,
            'is_template' => $as_template ? 1 : $to->is_template,
            'dcr_hash' => Uuid::uuid4(),
        ];
        $updates = $permis_fields
            ? $this->service->filter_keys($from->toArray(), $permis_fields)
            : $from->toArray();
        $res = TableDataRequest::where('id', '=', $to->id)->update(array_merge($this->service->delSystemFields($updates), $arr));

        $this->copyDefaults($from, $to);
        $this->copyColumnGroups($from, $to);
        $this->copyLinkedTables($from, $to);

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
    protected function copyLinkedTables(TableDataRequest $from, TableDataRequest $to): void
    {
        $to->_dcr_linked_tables()->delete();
        foreach ($from->_dcr_linked_tables as $linked_table) {
            $this->dataReqRepo->insertLinkedTable($to->id, $linked_table->toArray());
        }
    }
}
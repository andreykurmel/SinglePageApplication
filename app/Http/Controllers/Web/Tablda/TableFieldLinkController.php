<?php

namespace Vanguard\Http\Controllers\Web\Tablda;


use Vanguard\Http\Controllers\Controller;
use Vanguard\Http\Requests\Tablda\TableFieldLink\TableFieldLinkAddRequest;
use Vanguard\Http\Requests\Tablda\TableFieldLink\TableFieldLinkDeleteRequest;
use Vanguard\Http\Requests\Tablda\TableFieldLink\TableFieldLinkParamAddRequest;
use Vanguard\Http\Requests\Tablda\TableFieldLink\TableFieldLinkParamDeleteRequest;
use Vanguard\Http\Requests\Tablda\TableFieldLink\TableFieldLinkParamUpdateRequest;
use Vanguard\Http\Requests\Tablda\TableFieldLink\TableFieldLinkToDcrAddRequest;
use Vanguard\Http\Requests\Tablda\TableFieldLink\TableFieldLinkToDcrDeleteRequest;
use Vanguard\Http\Requests\Tablda\TableFieldLink\TableFieldLinkToDcrUpdateRequest;
use Vanguard\Http\Requests\Tablda\TableFieldLink\TableFieldLinkUpdateRequest;
use Vanguard\Models\Table\TableData;
use Vanguard\Models\Table\TableFieldLink;
use Vanguard\Models\Table\TableFieldLinkParam;
use Vanguard\Models\Table\TableFieldLinkToDcr;
use Vanguard\Repositories\Tablda\TableFieldLinkRepository;
use Vanguard\Services\Tablda\TableService;

class TableFieldLinkController extends Controller
{
    private $tableService;
    private $linkRepository;

    /**
     * TableLinkController constructor.
     * 
     * @param TableService $tableService
     * @param TableFieldLinkRepository $linkRepository
     */
    public function __construct(TableService $tableService, TableFieldLinkRepository $linkRepository)
    {
        $this->tableService = $tableService;
        $this->linkRepository = $linkRepository;
    }

    /**
     * Add Row Group
     *
     * @param TableFieldLinkAddRequest $request
     * @return TableFieldLink
     */
    public function insertLink(TableFieldLinkAddRequest $request){
        $table = $this->tableService->getTableByField($request->table_field_id);

        $this->authorize('isOwner', [TableData::class, $table]);

        $link = $this->linkRepository->addLink(
            array_merge($request->fields, [
                'table_field_id' => $request->table_field_id,
                'icon' => ($request->fields['icon'] ?? 'L')
            ])
        );
        return $this->tableService->getLinkSrc($table, $link);
    }

    /**
     * Update Row Group
     *
     * @param TableFieldLinkUpdateRequest $request
     * @return TableFieldLink
     */
    public function updateLink(TableFieldLinkUpdateRequest $request){
        $field_link = $this->linkRepository->getLink($request->table_link_id);
        $table = $this->tableService->getTableByField($field_link->table_field_id);

        $this->authorize('isOwner', [TableData::class, $table]);

        $new_link = $this->linkRepository->updateLink($field_link, $request->fields);
        return $this->tableService->getLinkSrc($table, $new_link);
    }

    /**
     * Delete Row Group
     *
     * @param TableFieldLinkDeleteRequest $request
     * @return mixed
     */
    public function deleteLink(TableFieldLinkDeleteRequest $request){
        $field_link = $this->linkRepository->getLink($request->table_link_id);
        $table = $this->tableService->getTableByField($field_link->table_field_id);
        $this->authorize('isOwner', [TableData::class, $table]);

        return $this->linkRepository->deleteLink($field_link->id);
    }

    /**
     * Add Link Param.
     *
     * @param TableFieldLinkParamAddRequest $request
     * @return TableFieldLinkParam
     */
    public function insertLinkParam(TableFieldLinkParamAddRequest $request)
    {
        $field_link = $this->linkRepository->getLink($request->table_field_link_id);
        $table = $this->tableService->getTableByField($field_link->table_field_id);
        $this->authorize('isOwner', [TableData::class, $table]);

        return $this->linkRepository->addLinkParam(
            array_merge($request->fields, [
                'table_field_link_id' => $request->table_field_link_id,
            ])
        );
    }

    /**
     * Update Link Param.
     *
     * @param TableFieldLinkParamUpdateRequest $request
     * @return array
     */
    public function updateLinkParam(TableFieldLinkParamUpdateRequest $request)
    {
        $field_link = $this->linkRepository->getLinkByParam($request->table_field_link_param_id);
        $table = $this->tableService->getTableByField($field_link->table_field_id);
        $this->authorize('isOwner', [TableData::class, $table]);

        return [
            'status' => $this->linkRepository->updateLinkParam($request->table_field_link_param_id, $request->fields)
        ];
    }

    /**
     * Delete Link Param.
     *
     * @param TableFieldLinkParamDeleteRequest $request
     * @return mixed
     */
    public function deleteLinkParam(TableFieldLinkParamDeleteRequest $request)
    {
        $field_link = $this->linkRepository->getLinkByParam($request->table_field_link_param_id);
        $table = $this->tableService->getTableByField($field_link->table_field_id);
        $this->authorize('isOwner', [TableData::class, $table]);

        return [
            'status' => $this->linkRepository->deleteLinkParam($request->table_field_link_param_id)
        ];
    }

    /**
     * Add Link To Dcr.
     *
     * @param TableFieldLinkToDcrAddRequest $request
     * @return TableFieldLinkToDcr
     */
    public function insertLinkToDcr(TableFieldLinkToDcrAddRequest $request)
    {
        $field_link = $this->linkRepository->getLink($request->table_field_link_id);
        $table = $this->tableService->getTableByField($field_link->table_field_id);
        $this->authorize('isOwner', [TableData::class, $table]);

        return $this->linkRepository->addLinkToDcr(
            array_merge($request->fields, [
                'table_field_link_id' => $request->table_field_link_id,
                'table_dcr_id' => $request->table_dcr_id,
            ])
        );
    }

    /**
     * Update Link To Dcr.
     *
     * @param TableFieldLinkToDcrUpdateRequest $request
     * @return array
     */
    public function updateLinkToDcr(TableFieldLinkToDcrUpdateRequest $request)
    {
        $field_link = $this->linkRepository->getLinkByToDcr($request->table_field_link_dcr_id);
        $table = $this->tableService->getTableByField($field_link->table_field_id);
        $this->authorize('isOwner', [TableData::class, $table]);

        return [
            'status' => $this->linkRepository->updateLinkToDcr($request->table_field_link_dcr_id, $request->fields)
        ];
    }

    /**
     * Delete Link To Dcr.
     *
     * @param TableFieldLinkToDcrDeleteRequest $request
     * @return mixed
     */
    public function deleteLinkToDcr(TableFieldLinkToDcrDeleteRequest $request)
    {
        $field_link = $this->linkRepository->getLinkByToDcr($request->table_field_link_dcr_id);
        $table = $this->tableService->getTableByField($field_link->table_field_id);
        $this->authorize('isOwner', [TableData::class, $table]);

        return [
            'status' => $this->linkRepository->deleteLinkToDcr($request->table_field_link_dcr_id)
        ];
    }
}

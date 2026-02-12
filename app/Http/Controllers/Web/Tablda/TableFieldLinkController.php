<?php

namespace Vanguard\Http\Controllers\Web\Tablda;


use Illuminate\Http\Request;
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
use Vanguard\Models\Table\TableFieldLinkColumn;
use Vanguard\Models\Table\TableFieldLinkEriTable;
use Vanguard\Models\Table\TableFieldLinkParam;
use Vanguard\Models\Table\TableFieldLinkToDcr;
use Vanguard\Repositories\Tablda\Permissions\TableRefConditionRepository;
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
    public function addReverseLink(Request $request)
    {
        $refConditionRepository = new TableRefConditionRepository();

        $field_link = $this->linkRepository->getLink($request->link_id);
        $this->authorize('isOwner', [TableData::class, $field_link->_ref_condition->_ref_table]);

        $reversedCondition = $refConditionRepository->addReverseRefCond($field_link->_ref_condition);
        return $this->linkRepository->addLink(
            $field_link->_ref_condition->_ref_table,
            $this->linkRepository->reversedLink($field_link, $reversedCondition)
        );
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
            $table,
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

        if ($table->is_system != 2) {
            $this->authorize('isOwner', [TableData::class, $table]);
        }

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
     * @param TableFieldLinkDeleteRequest $request
     * @return mixed
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function reloadLink(TableFieldLinkDeleteRequest $request){
        $field_link = $this->linkRepository->getLink($request->table_link_id);
        $table = $this->tableService->getTableByField($field_link->table_field_id);
        $this->authorize('isOwner', [TableData::class, $table]);

        return $this->linkRepository->loadLink([$field_link->id]);
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

    /**
     * @param Request $request
     * @return \Illuminate\Database\Eloquent\Collection
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function createOrUpdateLinkColumn(Request $request)
    {
        $field_link = $this->linkRepository->getLink($request->table_link_id);
        $table = $this->tableService->getTableByField($field_link->table_field_id);
        $this->authorize('isOwner', [TableData::class, $table]);

        $this->linkRepository->createOrUpdateLinkColumn(
            $request->table_link_id,
            $request->field_id,
            $request->field_db,
            $request->in_popup,
            $request->in_inline
        );
        return $field_link->_columns()->get();
    }

    /**
     * @param Request $request
     * @return \Illuminate\Database\Eloquent\Collection
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function setLinkColumns(Request $request)
    {
        $field_link = $this->linkRepository->getLink($request->table_link_id);
        $table = $this->tableService->getTableByField($field_link->table_field_id);
        $this->authorize('isOwner', [TableData::class, $table]);

        $this->linkRepository->setLinkColumns($field_link->id, $request->fields_objects);
        return $field_link->_columns()->get();
    }

    /**
     * @param Request $request
     * @return \Illuminate\Database\Eloquent\Collection
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function massSyncLinkColumns(Request $request)
    {
        $field_link = $this->linkRepository->getLink($request->table_link_id);
        $table = $this->tableService->getTableByField($field_link->table_field_id);
        $this->authorize('isOwner', [TableData::class, $table]);

        $this->linkRepository->massSyncLinkColumns($field_link, $request->field_key);
        return $field_link->_columns()->get();
    }

    /**
     * @param TableFieldLinkParamAddRequest $request
     * @return \Illuminate\Database\Eloquent\Model|TableFieldLinkEriTable
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function insertLinkEriTable(TableFieldLinkParamAddRequest $request)
    {
        $field_link = $this->linkRepository->getLink($request->table_field_link_id);
        $table = $this->tableService->getTableByField($field_link->table_field_id);
        $this->authorize('isOwner', [TableData::class, $table]);

        return $this->linkRepository->addLinkEriTable(
            array_merge($request->fields, [
                'table_link_id' => $request->table_field_link_id,
            ])
        );
    }

    /**
     * @param Request $request
     * @return array
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function updateLinkEriTable(Request $request)
    {
        $field_link = $this->linkRepository->getLinkByEriTable($request->link_eri_table_id);
        $table = $this->tableService->getTableByField($field_link->table_field_id);
        $this->authorize('isOwner', [TableData::class, $table]);

        return [
            'status' => $this->linkRepository->updateLinkEriTable($request->link_eri_table_id, $request->fields)
        ];
    }

    /**
     * @param Request $request
     * @return array
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function deleteLinkEriTable(Request $request)
    {
        $field_link = $this->linkRepository->getLinkByEriTable($request->link_eri_table_id);
        $table = $this->tableService->getTableByField($field_link->table_field_id);
        $this->authorize('isOwner', [TableData::class, $table]);

        return [
            'status' => $this->linkRepository->deleteLinkEriTable($request->link_eri_table_id)
        ];
    }

    /**
     * @param TableFieldLinkParamAddRequest $request
     * @return \Illuminate\Database\Eloquent\Model|TableFieldLinkEriTable
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function insertLinkEriField(Request $request)
    {
        $field_link = $this->linkRepository->getLinkByEriTable($request->link_eri_table_id);
        $table = $this->tableService->getTableByField($field_link->table_field_id);
        $this->authorize('isOwner', [TableData::class, $table]);

        return $this->linkRepository->addLinkEriField(
            array_merge($request->fields, [
                'table_link_eri_id' => $request->link_eri_table_id,
            ])
        );
    }

    /**
     * @param Request $request
     * @return array
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function updateLinkEriField(Request $request)
    {
        $eriTb = $this->linkRepository->getTableLinkEriField($request->link_eri_field_id);
        $field_link = $this->linkRepository->getLinkByEriTable($eriTb->table_link_eri_id);
        $table = $this->tableService->getTableByField($field_link->table_field_id);
        $this->authorize('isOwner', [TableData::class, $table]);

        return [
            'status' => $this->linkRepository->updateLinkEriField($request->link_eri_field_id, $request->fields)
        ];
    }

    /**
     * @param Request $request
     * @return array
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function deleteLinkEriField(Request $request)
    {
        $eriTb = $this->linkRepository->getTableLinkEriField($request->link_eri_field_id);
        $field_link = $this->linkRepository->getLinkByEriTable($eriTb->table_link_eri_id);
        $table = $this->tableService->getTableByField($field_link->table_field_id);
        $this->authorize('isOwner', [TableData::class, $table]);

        return [
            'status' => $this->linkRepository->deleteLinkEriField($request->link_eri_field_id)
        ];
    }

    /**
     * @param TableFieldLinkParamAddRequest $request
     * @return \Illuminate\Database\Eloquent\Model|TableFieldLinkEriTable
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function insertLinkEriFieldConversion(Request $request)
    {
        $eriField = $this->linkRepository->getTableLinkEriField($request->link_eri_field_id);
        $field_link = $this->linkRepository->getLinkByEriTable($eriField->table_link_eri_id);
        $table = $this->tableService->getTableByField($field_link->table_field_id);
        $this->authorize('isOwner', [TableData::class, $table]);

        return $this->linkRepository->addLinkEriFieldConversion(
            array_merge($request->fields, [
                'table_link_eri_field_id' => $request->link_eri_field_id,
            ])
        );
    }

    /**
     * @param Request $request
     * @return array
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function updateLinkEriFieldConversion(Request $request)
    {
        $eriConversion = $this->linkRepository->getTableLinkEriFieldConversion($request->link_eri_field_conv_id);
        $fieldLink = $this->linkRepository->getLinkByEriTable($eriConversion->_eri_field->table_link_eri_id);
        $table = $this->tableService->getTableByField($fieldLink->table_field_id);
        $this->authorize('isOwner', [TableData::class, $table]);

        return [
            'status' => $this->linkRepository->updateLinkEriFieldConversion($request->link_eri_field_conv_id, $request->fields)
        ];
    }

    /**
     * @param Request $request
     * @return array
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function deleteLinkEriFieldConversion(Request $request)
    {
        $eriConversion = $this->linkRepository->getTableLinkEriFieldConversion($request->link_eri_field_conv_id);
        $fieldLink = $this->linkRepository->getLinkByEriTable($eriConversion->_eri_field->table_link_eri_id);
        $table = $this->tableService->getTableByField($fieldLink->table_field_id);
        $this->authorize('isOwner', [TableData::class, $table]);

        return [
            'status' => $this->linkRepository->deleteLinkEriFieldConversion($request->link_eri_field_conv_id)
        ];
    }

    /**
     * @param TableFieldLinkParamAddRequest $request
     * @return \Illuminate\Database\Eloquent\Model|TableFieldLinkEriTable
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function insertLinkEriPart(TableFieldLinkParamAddRequest $request)
    {
        $field_link = $this->linkRepository->getLink($request->table_field_link_id);
        $table = $this->tableService->getTableByField($field_link->table_field_id);
        $this->authorize('isOwner', [TableData::class, $table]);

        return $this->linkRepository->addLinkEriPart(
            array_merge($request->fields, [
                'table_link_id' => $request->table_field_link_id,
            ])
        );
    }

    /**
     * @param Request $request
     * @return array
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function updateLinkEriPart(Request $request)
    {
        $field_link = $this->linkRepository->getLinkByEriPart($request->link_eri_table_id);
        $table = $this->tableService->getTableByField($field_link->table_field_id);
        $this->authorize('isOwner', [TableData::class, $table]);

        return [
            'status' => $this->linkRepository->updateLinkEriPart($request->link_eri_table_id, $request->fields)
        ];
    }

    /**
     * @param Request $request
     * @return array
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function deleteLinkEriPart(Request $request)
    {
        $field_link = $this->linkRepository->getLinkByEriPart($request->link_eri_table_id);
        $table = $this->tableService->getTableByField($field_link->table_field_id);
        $this->authorize('isOwner', [TableData::class, $table]);

        return [
            'status' => $this->linkRepository->deleteLinkEriPart($request->link_eri_table_id)
        ];
    }

    /**
     * @param Request $request
     * @return \Vanguard\Models\Table\TableFieldLinkEriPartVariable[]
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function fillAllEriPartVariables(Request $request)
    {
        $part = $this->linkRepository->getTableLinkEriPart($request->link_eri_part_id);
        $table = $this->tableService->getTableByField($part->_link->table_field_id);
        $this->authorize('isOwner', [TableData::class, $table]);

        return $this->linkRepository->fillAllEriPartVariables($part);
    }

    /**
     * @param Request $request
     * @return \Vanguard\Models\Table\TableFieldLinkEriPartVariable[]
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function pasteEriPartVariables(Request $request)
    {
        $part = $this->linkRepository->getTableLinkEriPart($request->link_eri_part_id);
        $table = $this->tableService->getTableByField($part->_link->table_field_id);
        $this->authorize('isOwner', [TableData::class, $table]);

        return $this->linkRepository->pasteEriPartVariables($part, $request->variables);
    }

    /**
     * @param TableFieldLinkParamAddRequest $request
     * @return \Illuminate\Database\Eloquent\Model|TableFieldLinkEriTable
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function insertLinkEriPartVariable(Request $request)
    {
        $field_link = $this->linkRepository->getLinkByEriPart($request->link_eri_part_id);
        $table = $this->tableService->getTableByField($field_link->table_field_id);
        $this->authorize('isOwner', [TableData::class, $table]);

        return $this->linkRepository->addLinkEriPartVariable(
            array_merge($request->fields, [
                'table_link_eri_part_id' => $request->link_eri_part_id,
            ])
        );
    }

    /**
     * @param Request $request
     * @return array
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function updateLinkEriPartVariable(Request $request)
    {
        $eriTb = $this->linkRepository->getTableLinkEriPartVariable($request->link_eri_part_var_id);
        $field_link = $this->linkRepository->getLinkByEriPart($eriTb->table_link_eri_part_id);
        $table = $this->tableService->getTableByField($field_link->table_field_id);
        $this->authorize('isOwner', [TableData::class, $table]);

        return [
            'status' => $this->linkRepository->updateLinkEriPartVariable($request->link_eri_part_var_id, $request->fields)
        ];
    }

    /**
     * @param Request $request
     * @return array
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function deleteLinkEriPartVariable(Request $request)
    {
        $eriTb = $this->linkRepository->getTableLinkEriPartVariable($request->link_eri_part_var_id);
        $field_link = $this->linkRepository->getLinkByEriPart($eriTb->table_link_eri_part_id);
        $table = $this->tableService->getTableByField($field_link->table_field_id);
        $this->authorize('isOwner', [TableData::class, $table]);

        return [
            'status' => $this->linkRepository->deleteLinkEriPartVariable($request->link_eri_part_var_id)
        ];
    }

    /**
     * @param TableFieldLinkParamAddRequest $request
     * @return \Illuminate\Database\Eloquent\Model|TableFieldLinkEriTable
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function insertLinkDaLoading(Request $request)
    {
        $field_link = $this->linkRepository->getLink($request->table_link_id);
        $table = $this->tableService->getTableByField($field_link->table_field_id);
        $this->authorize('isOwner', [TableData::class, $table]);

        return $this->linkRepository->addLinkDaLoading(
            array_merge($request->fields, [
                'table_link_id' => $request->table_link_id,
            ])
        );
    }

    /**
     * @param Request $request
     * @return array
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function updateLinkDaLoading(Request $request)
    {
        $daLoading = $this->linkRepository->getTableLinkDaLoading($request->link_da_loading_id);
        $table = $this->tableService->getTableByField($daLoading->_link->table_field_id);
        $this->authorize('isOwner', [TableData::class, $table]);

        return [
            'status' => $this->linkRepository->updateLinkDaLoading($request->link_da_loading_id, $request->fields)
        ];
    }

    /**
     * @param Request $request
     * @return array
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function deleteLinkDaLoading(Request $request)
    {
        $daLoading = $this->linkRepository->getTableLinkDaLoading($request->link_da_loading_id);
        $table = $this->tableService->getTableByField($daLoading?->_link?->table_field_id);
        $this->authorize('isOwner', [TableData::class, $table]);

        return [
            'status' => $this->linkRepository->deleteLinkDaLoading($request->link_da_loading_id)
        ];
    }
}

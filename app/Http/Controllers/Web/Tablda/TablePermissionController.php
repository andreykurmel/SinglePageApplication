<?php

namespace Vanguard\Http\Controllers\Web\Tablda;


use Ramsey\Uuid\Uuid;
use Vanguard\Http\Controllers\Controller;
use Vanguard\Http\Requests\Tablda\TablePermission\AddonRightRequest;
use Vanguard\Http\Requests\Tablda\TablePermission\AddonRightUpdateRequest;
use Vanguard\Http\Requests\Tablda\TablePermission\TablePermission2UserGroup;
use Vanguard\Http\Requests\Tablda\TablePermission\TablePermissionAddRequest;
use Vanguard\Http\Requests\Tablda\TablePermission\TablePermissionChangeRequest;
use Vanguard\Http\Requests\Tablda\TablePermission\TablePermissionCopyRequest;
use Vanguard\Http\Requests\Tablda\TablePermission\TablePermissionDefaultFieldRequest;
use Vanguard\Http\Requests\Tablda\TablePermission\TablePermissionDeleteRequest;
use Vanguard\Http\Requests\Tablda\TablePermission\TablePermission2ColumnGroupsRequest;
use Vanguard\Http\Requests\Tablda\TablePermission\TablePermission2RowGroupsRequest;
use Vanguard\Http\Requests\Tablda\TablePermission\TablePermissionFileRequest;
use Vanguard\Http\Requests\Tablda\TablePermission\TablePermissionForbidRequest;
use Vanguard\Http\Requests\Tablda\TablePermission\TablePermissionUpdateColumnGroupsRequest;
use Vanguard\Http\Requests\Tablda\TablePermission\TablePermissionUpdateRowGroupsRequest;
use Vanguard\Models\DataSetPermissions\TablePermission;
use Vanguard\Models\DataSetPermissions\TablePermissionColumn;
use Vanguard\Models\DataSetPermissions\TablePermissionForbidSettings;
use Vanguard\Models\Table\TableData;
use Vanguard\Models\User\Addon;
use Vanguard\Repositories\Tablda\Permissions\TableColGroupRepository;
use Vanguard\Repositories\Tablda\Permissions\TablePermissionForbidRepository;
use Vanguard\Repositories\Tablda\Permissions\TableRowGroupRepository;
use Vanguard\Repositories\Tablda\Permissions\TablePermissionRepository;
use Vanguard\Services\Tablda\Permissions\TablePermissionService;
use Vanguard\Services\Tablda\TableService;
use Vanguard\User;

class TablePermissionController extends Controller
{
    private $tableService;
    private $tablePermissionRepository;
    private $tablePermissionService;
    private $colGroupRepository;
    private $rowGroupRepository;

    /**
     * TablePermissionController constructor.
     *
     * @param TableService $tableService
     * @param TablePermissionRepository $tablePermissionRepository
     * @param TablePermissionService $tablePermissionService
     * @param TableColGroupRepository $colGroupRepository
     * @param TableRowGroupRepository $rowGroupRepository
     */
    public function __construct(
        TableService $tableService,
        TablePermissionRepository $tablePermissionRepository,
        TablePermissionService $tablePermissionService,
        TableColGroupRepository $colGroupRepository,
        TableRowGroupRepository $rowGroupRepository
    )
    {
        $this->tableService = $tableService;
        $this->tablePermissionRepository = $tablePermissionRepository;
        $this->tablePermissionService = $tablePermissionService;
        $this->colGroupRepository = $colGroupRepository;
        $this->rowGroupRepository = $rowGroupRepository;
    }

    /**
     * @param TablePermissionDeleteRequest $request
     * @return mixed
     */
    public function checkPermis(TablePermissionDeleteRequest $request)
    {
        $permission = $this->tablePermissionRepository->getPermission($request->table_permission_id);
        $table = $this->tableService->getTable($permission->table_id);

        //$this->authorize('isOwner', [TableData::class, $table]);

        return $permission;
    }

    /**
     * @param TablePermissionCopyRequest $request
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function copyPermis(TablePermissionCopyRequest $request)
    {
        $from_permission = $this->tablePermissionRepository->getPermission($request->from_permis_id);
        $to_permission = $this->tablePermissionRepository->getPermission($request->to_permis_id);
        $table_from = $this->tableService->getTable($from_permission->table_id);
        $table_to = $this->tableService->getTable($to_permission->table_id);

        //$this->authorize('isOwner', [TableData::class, $table_from]);
        $this->authorize('isOwner', [TableData::class, $table_to]);

        $fields = $request->as_template ? (new TablePermission())->design_tab : [];
        $this->tablePermissionRepository->copyPermission($from_permission, $to_permission, !!$request->as_template, $fields);
        $table_to->_is_owner = true;
        if ($request->as_template) {
            return (new TableService())->getTbPermissions($table_to, $to_permission->id);
        } else {
            return (new TableService())->getTbPermissions($table_to)->where('is_request', '=', 0)->values();
        }
    }

    /**
     * Add Table Permission
     *
     * @param TablePermissionAddRequest $request
     * @return mixed
     */
    public function insertTablePermission(TablePermissionAddRequest $request) {
        $table = $this->tableService->getTable($request->table_id);

        $this->authorize('isOwner', [TableData::class, $table]);

        if (empty($request->fields['is_request'])) {//insert standard TablePermission
            $arr = array_merge($request->fields, [
                'table_id' => $request->table_id
            ]);
        } else {//insert Data Request
            $arr = array_merge($request->fields, [
                'table_id' => $request->table_id,
                'can_add' => 1,
                'link_hash' => Uuid::uuid4(),
            ]);
        }

        if (!empty($request->fields['user_link']) && $this->tablePermissionRepository->checkAddress($table->name, $request->fields['user_link'])) {
            return response('Address taken! Enter a different one.', 400);
        } else {
            return $this->tablePermissionRepository->addPermission($arr);
        }
    }

    /**
     * Update Table Permission
     *
     * @param TablePermissionChangeRequest $request
     * @return array
     */
    public function updateTablePermission(TablePermissionChangeRequest $request) {
        $permission = $this->tablePermissionRepository->getPermission($request->table_permission_id);
        $table = $this->tableService->getTable($permission->table_id);

        $this->authorize('isOwner', [TableData::class, $table]);

        if (!empty($request->fields['user_link']) && $this->tablePermissionRepository->checkAddress($table->name, $request->fields['user_link'], $permission->id)) {
            return response('Address taken! Enter a different one.', 400);
        } else {
            return $this->tablePermissionRepository->updatePermission($permission->id, $request->fields);
        }
    }

    /**
     * Delete Table Permission
     *
     * @param TablePermissionDeleteRequest $request
     * @return mixed
     */
    public function deleteTablePermission(TablePermissionDeleteRequest $request) {
        $permission = $this->tablePermissionRepository->getPermission($request->table_permission_id);
        $table = $this->tableService->getTable($permission->table_id);

        $this->authorize('isOwner', [TableData::class, $table]);

        return $this->tablePermissionRepository->deletePermission($permission->id, $permission->table_id);
    }

    /**
     * Check pass for Table Permission
     *
     * @param TablePermissionDeleteRequest $request
     * @return mixed
     */
    public function checkPass(TablePermissionDeleteRequest $request) {
        $permission = $this->tablePermissionRepository->getPermission($request->table_permission_id);

        return ['status' => $permission->pass == $request->pass];
    }

    /**
     * Update Column in Table Permission
     *
     * @param TablePermissionUpdateColumnGroupsRequest $request
     * @return mixed
     */
    public function updateColumnInTablePermission(TablePermissionUpdateColumnGroupsRequest $request) {
        $permission = $this->tablePermissionRepository->getPermission($request->table_permission_id);
        $table = $this->tableService->getTable($permission->table_id);

        $this->authorize('isOwner', [TableData::class, $table]);

        return $this->tablePermissionRepository->updateTableColPermission(
            $request->table_permission_id,
            $request->table_column_group_id,
            $request->view,
            $request->edit,
            $request->shared
        );
    }

    /**
     * Add UserGroup to Table Permission
     *
     * @param TablePermission2UserGroup $request
     * @return mixed
     */
    public function addUserGroupToTablePermission(TablePermission2UserGroup $request) {
        $permission = $this->tablePermissionRepository->getPermission($request->table_permission_id);
        $table = $this->tableService->getTable($permission->table_id);

        $this->authorize('isOwner', [TableData::class, $table]);

        return $this->tablePermissionRepository->attachUserGroupPermission($permission, $request->user_group_id, $request->is_active);
    }

    /**
     * Delete UserGroup from Table Permission
     *
     * @param TablePermission2UserGroup $request
     * @return mixed
     */
    public function updateUserGroupFromTablePermission(TablePermission2UserGroup $request) {
        $permission = $this->tablePermissionRepository->getPermission($request->table_permission_id);
        $table = $this->tableService->getTable($permission->table_id);

        $this->authorize('isOwner', [TableData::class, $table]);

        return ['status' => $this->tablePermissionRepository->updateUserGroupPermission($permission, $request->user_group_id, $request->is_active)];
    }

    /**
     * Delete UserGroup from Table Permission
     *
     * @param TablePermission2UserGroup $request
     * @return mixed
     */
    public function deleteUserGroupFromTablePermission(TablePermission2UserGroup $request) {
        $permission = $this->tablePermissionRepository->getPermission($request->table_permission_id);
        $table = $this->tableService->getTable($permission->table_id);

        $this->authorize('isOwner', [TableData::class, $table]);

        return $this->tablePermissionRepository->detachUserGroupPermission($permission, $request->user_group_id);
    }

    /**
     * Update Row in Table Permission
     *
     * @param TablePermissionUpdateRowGroupsRequest $request
     * @return mixed
     */
    public function updateRowInTablePermission(TablePermissionUpdateRowGroupsRequest $request) {
        $permission = $this->tablePermissionRepository->getPermission($request->table_permission_id);
        $table = $this->tableService->getTable($permission->table_id);

        $this->authorize('isOwner', [TableData::class, $table]);

        return $this->tablePermissionRepository->updateTableRowPermission(
            $request->table_permission_id,
            $request->table_row_group_id,
            $request->view,
            $request->edit,
            $request->del,
            $request->shared
        );
    }

    /**
     * Change Default Value for Field for provided Table Permission.
     *
     * @param TablePermissionDefaultFieldRequest $request
     * @return mixed
     */
    public function defaultField(TablePermissionDefaultFieldRequest $request) {
        $permission = $this->tablePermissionRepository->getPermission($request->table_permission_id);
        $table = $this->tableService->getTable($permission->table_id);

        $this->authorize('isOwner', [TableData::class, $table]);

        return $this->tablePermissionService->defaultField($permission->id, $request->user_group_id, $request->table_field_id, $request->default_val);
    }

    /**
     * Insert Addon Right.
     *
     * @param AddonRightRequest $request
     * @return mixed
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function insertAddonRight(AddonRightRequest $request) {
        $permission = $this->tablePermissionRepository->getPermission($request->table_permission_id);
        $table = $this->tableService->getTable($permission->table_id);

        $this->authorize('isOwner', [TableData::class, $table]);

        return $this->tablePermissionRepository->insertAddonRight($permission, $request->addon_id, $request->type);
    }

    /**
     * Update Addon Right.
     *
     * @param AddonRightUpdateRequest $request
     * @return mixed
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function updateAddonRight(AddonRightUpdateRequest $request) {
        $permission = $this->tablePermissionRepository->getPermission($request->table_permission_id);
        $table = $this->tableService->getTable($permission->table_id);

        $this->authorize('isOwner', [TableData::class, $table]);

        return $this->tablePermissionRepository->updateAddonRight($permission, $request->addon_id, $request->fld, $request->val);
    }

    /**
     * Delete Addon Right.
     *
     * @param AddonRightRequest $request
     * @return mixed
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function deleteAddonRight(AddonRightRequest $request) {
        $permission = $this->tablePermissionRepository->getPermission($request->table_permission_id);
        $table = $this->tableService->getTable($permission->table_id);

        $this->authorize('isOwner', [TableData::class, $table]);

        return $this->tablePermissionRepository->deleteAddonRight($permission, $request->addon_id, $request->type);
    }

    /**
     * Add DCR File.
     *
     * @param TablePermissionFileRequest $request
     * @return mixed
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function addDcrFile(TablePermissionFileRequest $request) {
        $permission = $this->tablePermissionRepository->getPermission($request->table_permission_id);
        $table = $this->tableService->getTable($permission->table_id);

        $this->authorize('isOwner', [TableData::class, $table]);

        return [
            'filepath' => $this->tablePermissionRepository->insertDCRFile($permission, $request->field, $request->u_file)
        ];
    }

    /**
     * Delete DCR File.
     *
     * @param TablePermissionFileRequest $request
     * @return mixed
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function deleteDcrFile(TablePermissionFileRequest $request) {
        $permission = $this->tablePermissionRepository->getPermission($request->table_permission_id);
        $table = $this->tableService->getTable($permission->table_id);

        $this->authorize('isOwner', [TableData::class, $table]);

        return ['status' => $this->tablePermissionRepository->deleteDCRFile($permission, $request->field)];
    }

    /**
     * addForbidSetting
     *
     * @param TablePermissionForbidRequest $request
     * @return mixed
     */
    public function addForbidSetting(TablePermissionForbidRequest $request) {
        $permission = $this->tablePermissionRepository->getPermission($request->table_permission_id);
        $table = $this->tableService->getTable($permission->table_id);

        $this->authorize('isOwner', [TableData::class, $table]);

        if ($request->db_col_name) {
            (new TablePermissionForbidRepository())->addForbidSetting($permission->id, $request->db_col_name);
        } else {
            (new TablePermissionForbidRepository())->addAllForbidSetting($permission->id);
        }

        return $permission->_forbid_settings()->get();
    }

    /**
     * deleteForbidSetting
     *
     * @param TablePermissionForbidRequest $request
     * @return mixed
     */
    public function deleteForbidSetting(TablePermissionForbidRequest $request) {
        $permission = $this->tablePermissionRepository->getPermission($request->table_permission_id);
        $table = $this->tableService->getTable($permission->table_id);

        $this->authorize('isOwner', [TableData::class, $table]);

        if ($request->db_col_name) {
            (new TablePermissionForbidRepository())->deleteForbidSetting($permission->id, $request->db_col_name);
        } else {
            (new TablePermissionForbidRepository())->deleteAllForbidSetting($permission->id);
        }

        return $permission->_forbid_settings()->get();
    }
}

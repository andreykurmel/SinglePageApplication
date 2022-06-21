<?php

namespace Vanguard\Http\Controllers\Web\Tablda;


use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Support\Collection;
use Vanguard\Http\Controllers\Controller;
use Vanguard\Http\Requests\Tablda\TablePermission\AddonRightRequest;
use Vanguard\Http\Requests\Tablda\TablePermission\AddonRightUpdateRequest;
use Vanguard\Http\Requests\Tablda\TablePermission\TablePermission2UserGroup;
use Vanguard\Http\Requests\Tablda\TablePermission\TablePermissionAddRequest;
use Vanguard\Http\Requests\Tablda\TablePermission\TablePermissionChangeRequest;
use Vanguard\Http\Requests\Tablda\TablePermission\TablePermissionCopyRequest;
use Vanguard\Http\Requests\Tablda\TablePermission\TablePermissionDefaultFieldRequest;
use Vanguard\Http\Requests\Tablda\TablePermission\TablePermissionDeleteRequest;
use Vanguard\Http\Requests\Tablda\TablePermission\TablePermissionForbidRequest;
use Vanguard\Http\Requests\Tablda\TablePermission\TablePermissionUpdateColumnGroupsRequest;
use Vanguard\Http\Requests\Tablda\TablePermission\TablePermissionUpdateRowGroupsRequest;
use Vanguard\Models\Table\TableData;
use Vanguard\Repositories\Tablda\Permissions\TablePermissionForbidRepository;
use Vanguard\Repositories\Tablda\Permissions\TablePermissionRepository;
use Vanguard\Services\Tablda\Permissions\TablePermissionService;
use Vanguard\Services\Tablda\TableService;

class TablePermissionController extends Controller
{
    private $tableService;
    private $tablePermissionRepository;
    private $tablePermissionService;

    /**
     * TablePermissionController constructor.
     *
     * @param TableService $tableService
     * @param TablePermissionRepository $tablePermissionRepository
     * @param TablePermissionService $tablePermissionService
     */
    public function __construct(
        TableService              $tableService,
        TablePermissionRepository $tablePermissionRepository,
        TablePermissionService    $tablePermissionService
    )
    {
        $this->tableService = $tableService;
        $this->tablePermissionRepository = $tablePermissionRepository;
        $this->tablePermissionService = $tablePermissionService;
    }

    /**
     * @param TablePermissionCopyRequest $request
     * @return Collection
     */
    public function copyPermis(TablePermissionCopyRequest $request)
    {
        $from_permission = $this->tablePermissionRepository->getPermission($request->from_permis_id);
        $to_permission = $this->tablePermissionRepository->getPermission($request->to_permis_id);
        $table_from = $this->tableService->getTable($from_permission->table_id);
        $table_to = $this->tableService->getTable($to_permission->table_id);

        //$this->authorize('isOwner', [TableData::class, $table_from]);
        $this->authorize('isOwner', [TableData::class, $table_to]);

        $this->tablePermissionRepository->copyPermission($from_permission, $to_permission);
        $table_to->_is_owner = true;
        return (new TableService())->getTbPermissions($table_to)->values();
    }

    /**
     * Add Table Permission
     *
     * @param TablePermissionAddRequest $request
     * @return mixed
     */
    public function insertTablePermission(TablePermissionAddRequest $request)
    {
        $table = $this->tableService->getTable($request->table_id);

        $this->authorize('isOwner', [TableData::class, $table]);

        $arr = array_merge($request->fields, [
            'table_id' => $request->table_id
        ]);

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
    public function updateTablePermission(TablePermissionChangeRequest $request)
    {
        $permission = $this->tablePermissionRepository->getPermission($request->table_permission_id);
        $table = $this->tableService->getTable($permission->table_id);

        $this->authorize('isOwner', [TableData::class, $table]);

        if (!empty($request->fields['user_link']) && $this->tablePermissionRepository->checkAddress($table->name, $request->fields['user_link'], $permission->id)) {
            return response('Address taken! Enter a different one.', 400);
        } else {
            return $this->tablePermissionRepository->updatePermission($permission, $request->fields);
        }
    }

    /**
     * Delete Table Permission
     *
     * @param TablePermissionDeleteRequest $request
     * @return mixed
     */
    public function deleteTablePermission(TablePermissionDeleteRequest $request)
    {
        $permission = $this->tablePermissionRepository->getPermission($request->table_permission_id);
        $table = $this->tableService->getTable($permission->table_id);

        $this->authorize('isOwner', [TableData::class, $table]);

        return $this->tablePermissionRepository->deletePermission($permission, $permission->table_id);
    }

    /**
     * Check pass for Table Permission
     *
     * @param TablePermissionDeleteRequest $request
     * @return mixed
     */
    public function checkPass(TablePermissionDeleteRequest $request)
    {
        $permission = $this->tablePermissionRepository->getPermission($request->table_permission_id);

        return ['status' => $permission->pass == $request->pass];
    }

    /**
     * Update Column in Table Permission
     *
     * @param TablePermissionUpdateColumnGroupsRequest $request
     * @return mixed
     */
    public function updateColumnInTablePermission(TablePermissionUpdateColumnGroupsRequest $request)
    {
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
    public function addUserGroupToTablePermission(TablePermission2UserGroup $request)
    {
        $permission = $this->tablePermissionRepository->getPermission($request->table_permission_id);
        $table = $this->tableService->getTable($permission->table_id);

        $this->authorize('isOwner', [TableData::class, $table]);

        return $this->tablePermissionService->attachUserGroupPermission($permission, $request->user_group_id, $request->is_active);
    }

    /**
     * Delete UserGroup from Table Permission
     *
     * @param TablePermission2UserGroup $request
     * @return mixed
     */
    public function updateUserGroupFromTablePermission(TablePermission2UserGroup $request)
    {
        $permission = $this->tablePermissionRepository->getPermission($request->table_permission_id);
        $table = $this->tableService->getTable($permission->table_id);

        $this->authorize('isOwner', [TableData::class, $table]);

        return ['status' => $this->tablePermissionService->updateUserGroupPermission($permission, $request->user_group_id, $request->is_active)];
    }

    /**
     * Delete UserGroup from Table Permission
     *
     * @param TablePermission2UserGroup $request
     * @return mixed
     */
    public function deleteUserGroupFromTablePermission(TablePermission2UserGroup $request)
    {
        $permission = $this->tablePermissionRepository->getPermission($request->table_permission_id);
        $table = $this->tableService->getTable($permission->table_id);

        $this->authorize('isOwner', [TableData::class, $table]);

        return $this->tablePermissionService->detachUserGroupPermission($permission, $request->user_group_id);
    }

    /**
     * Update Row in Table Permission
     *
     * @param TablePermissionUpdateRowGroupsRequest $request
     * @return mixed
     */
    public function updateRowInTablePermission(TablePermissionUpdateRowGroupsRequest $request)
    {
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
    public function defaultField(TablePermissionDefaultFieldRequest $request)
    {
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
     * @throws AuthorizationException
     */
    public function insertAddonRight(AddonRightRequest $request)
    {
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
     * @throws AuthorizationException
     */
    public function updateAddonRight(AddonRightUpdateRequest $request)
    {
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
     * @throws AuthorizationException
     */
    public function deleteAddonRight(AddonRightRequest $request)
    {
        $permission = $this->tablePermissionRepository->getPermission($request->table_permission_id);
        $table = $this->tableService->getTable($permission->table_id);

        $this->authorize('isOwner', [TableData::class, $table]);

        return $this->tablePermissionRepository->deleteAddonRight($permission, $request->addon_id, $request->type);
    }

    /**
     * addForbidSetting
     *
     * @param TablePermissionForbidRequest $request
     * @return mixed
     */
    public function addForbidSetting(TablePermissionForbidRequest $request)
    {
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
    public function deleteForbidSetting(TablePermissionForbidRequest $request)
    {
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

<?php


namespace Vanguard\Modules\Permissions;


use Cache;
use Vanguard\Models\Table\Table;
use Vanguard\Repositories\Tablda\FolderRepository;
use Vanguard\Repositories\Tablda\Permissions\TableDataRequestRepository;
use Vanguard\Repositories\Tablda\Permissions\TablePermissionRepository;
use Vanguard\Repositories\Tablda\TableViewRepository;
use Vanguard\Services\Tablda\HelperService;

class TableRights
{
    /**
     * @param int $table_id
     */
    public static function forgetCache(int $table_id)
    {
        Cache::store('array')->forget(self::key($table_id));
    }

    /**
     * @param int $table_id
     * @return string
     */
    protected static function key(int $table_id)
    {
        return 'permission_right_' . $table_id . '_' . (auth()->id() ?: '');
    }

    /**
     * @param Table $table
     * @return PermissionObject
     */
    public static function permissions(Table $table)
    {
        return Cache::store('array')->rememberForever(self::key($table->id), function () use ($table) {
            return self::loadPermis($table);
        });
    }

    /**
     * @param Table $table
     * @return PermissionObject
     */
    protected static function loadPermis(Table $table)
    {
        $service = new HelperService();
        $user_id = auth()->id();
        $user_id = $service->forceGuestForPublic($user_id);

        $specials = request('special_params') ?? [];

        //Linked Table in Data Request
        if ($specials['dcr_linked_id'] ?? '') {
            return self::dcrLinkedPermission($table, $specials['dcr_linked_id'], $user_id);
        } //Data Request
        elseif ($specials['dcr_hash'] ?? '') {
            return self::dcrPermission($table, $specials['dcr_hash'], $user_id);
        } //Collaborator or View
        else {
            //loaded from Folder View
            if (!empty($specials['is_folder_view'])) {
                $specials['view_hash'] = (new FolderRepository())->getAssignedView($specials['is_folder_view'], $table->id);
            }

            $view_permission_id = null;
            //user see TableView (MultipleRecordView)
            $view = (new TableViewRepository())->getByHash($specials['view_hash'] ?? '') ?? [];
            if ($view) {
                //apply custom permission only for 'main table' from View, not for 'linked tables'.
                $view_permission_id = $view->table_id == $table->id ? $view->access_permission_id : null;
            }
            //user see RecordView (SingleRecordView)
            if ($specials['srv_hash'] ?? '') {
                $view_permission_id = $table->single_view_permission_id;
            }

            $user_id = $view_permission_id ? null : $user_id;
            return self::tablePermission($table, $user_id, $view_permission_id);
        }
    }

    /**
     * @param Table $table
     * @param string $dcr_hash
     * @param int|null $user_id
     * @return PermissionDcr
     */
    protected static function dcrPermission(Table $table, string $dcr_hash, int $user_id = null)
    {
        $result = new PermissionDcr($user_id);
        self::loadVisitorPermissions($result, $table->id);

        //set DCR permission
        $dcr = (new TableDataRequestRepository())->dcrRelation($dcr_hash);
        $result->setTableDcr($dcr);
        $result->clearNotUniques();

        return $result;
    }

    /**
     * @param PermissionObject $permissionObject
     * @param int $table_id
     */
    protected static function loadVisitorPermissions(PermissionObject $permissionObject, int $table_id)
    {
        //get Visitor permissions
        $_table_permissions = (new TablePermissionRepository())->tablePermissions($table_id, null, null, true);
        foreach ($_table_permissions as $_permission) {
            $permissionObject->setTablePermis($_permission);
        }
    }

    /**
     * @param Table $table
     * @param int $linked_id
     * @param int|null $user_id
     * @return PermissionLinkedDcr
     */
    protected static function dcrLinkedPermission(Table $table, int $linked_id, int $user_id = null)
    {
        $result = new PermissionLinkedDcr($user_id);
        $dcr = (new TableDataRequestRepository())->getLinkedAsDcr($linked_id);
        $result->setTableLinked($dcr);
        $result->clearNotUniques();

        return $result;
    }

    /**
     * @param Table $table
     * @param int|null $user_id
     * @param int|null $permission_id
     * @return PermissionObject
     */
    protected static function tablePermission(Table $table, int $user_id = null, int $permission_id = null)
    {
        $service = new HelperService();
        $visitor_scope = $user_id ? $service->use_visitor_scope : true;
        //All system tables
        if ($table->is_system) {
            $visitor_scope = true;
        }

        $result = new PermissionObject($user_id, $table->user_id == $user_id ? $user_id : 0);

        if (!$result->is_owner) {
            $_table_permissions = (new TablePermissionRepository())->tablePermissions($table->id, $user_id, $permission_id, true);
            foreach ($_table_permissions as $_permission) {
                $result->setTablePermis($_permission);
            }
            $result->clearNotUniques();
        }
        return $result;
    }
}
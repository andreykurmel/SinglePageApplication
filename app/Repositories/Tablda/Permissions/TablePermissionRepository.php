<?php

namespace Vanguard\Repositories\Tablda\Permissions;


use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Ramsey\Uuid\Uuid;
use Vanguard\Models\DataSetPermissions\TablePermission;
use Vanguard\Models\DataSetPermissions\TablePermissionColumn;
use Vanguard\Models\DataSetPermissions\TablePermissionDefaultField;
use Vanguard\Models\DataSetPermissions\TablePermissionRow;
use Vanguard\Models\DDLReference;
use Vanguard\Models\Table\Table;
use Vanguard\Models\Table\TableStatuse;
use Vanguard\Models\User\UserGroup;
use Vanguard\Models\User\UserGroup2TablePermission;
use Vanguard\Repositories\Tablda\FileRepository;
use Vanguard\Services\Tablda\HelperService;
use Vanguard\Services\Tablda\TableService;
use Vanguard\Singletones\AuthUserSingleton;
use Vanguard\User;

class TablePermissionRepository
{
    protected $service;

    /**
     * TablePermissionRepository constructor.
     */
    public function __construct()
    {
        $this->service = new HelperService();
    }

    /**
     * Get Permission.
     *
     * @param $table_permission_id
     * @return null|TablePermission
     */
    public function getPermission($table_permission_id)
    {
        return TablePermission::where('id', '=', $table_permission_id)->first();
    }

    /**
     * @return Collection
     */
    public function getTemplates()
    {
        $ids = array_merge(HelperService::adminIds(), [auth()->id()]);//get from admin and current user.
        return TablePermission::where('is_request', '=', 2)
            ->whereHas('_table', function ($tb) use ($ids) {
                $tb->whereIn('user_id', $ids);
            })
            ->with('_table:id,user_id,db_name,name')
            ->get();
    }

    /**
     * @param TablePermission $from
     * @param TablePermission $to
     * @param bool $as_template
     * @param array $permis_fields
     * @return mixed
     */
    public function copyPermission(TablePermission $from, TablePermission $to, bool $as_template = false, array $permis_fields = [])
    {
        $arr = [
            'name' => $to->name,
            'is_request' => $as_template ? 1 : $to->is_request,
            'dcr_hash' => Uuid::uuid4(),
            'is_system' => 0,
        ];
        $updates = $permis_fields
            ? $this->service->filter_keys($from->toArray(), $permis_fields)
            : $from->toArray();
        $res = TablePermission::where('id', '=', $to->id)->update( array_merge( $this->service->delSystemFields($updates), $arr) );

        if ($as_template) {
            return $res;
        }

        $to->_addons()->detach();
        foreach ($from->_addons as $elem) {
            $to->_addons()->attach( $elem->id, [
                'type' => $elem->_link->type,
            ] );
        }

        $to->_column_groups()->detach();
        foreach ($from->_column_groups as $elem) {
            $to->_column_groups()->attach( $elem->id, [
                'view' => $elem->_link->view,
                'edit' => $elem->_link->edit,
                'delete' => $elem->_link->delete,
                'shared' => $elem->_link->shared,
            ] );
        }

        $to->_row_groups()->detach();
        foreach ($from->_row_groups as $elem) {
            $to->_row_groups()->attach( $elem->id, [
                'view' => $elem->_link->view,
                'edit' => $elem->_link->edit,
                'delete' => $elem->_link->delete,
                'shared' => $elem->_link->shared,
            ] );
        }

        $to->_cond_formats()->detach();
        foreach ($from->_cond_formats as $elem) {
            $to->_cond_formats()->attach( $elem->id, [
                'always_on' => $elem->_pivot->always_on,
                'visible_shared' => $elem->_pivot->visible_shared,
            ] );
        }

        $to->_charts()->detach();
        foreach ($from->_charts as $elem) {
            $to->_charts()->attach( $elem->id, [
                'can_edit' => $elem->_pivot->can_edit,
            ] );
        }

        $to->_views()->detach();
        foreach ($from->_views as $elem) {
            $to->_views()->attach( $elem->id );
        }

        $to->_forbid_settings()->delete();
        foreach ($from->_forbid_settings as $elem) {
            $to->_forbid_settings()->insert( array_merge(
                $this->service->delSystemFields($elem->toArray()),
                ['permission_id' => $to->id]
            ) );
        }

        $to->_link_limits()->delete();
        foreach ($from->_link_limits as $elem) {
            $to->_link_limits()->insert( array_merge(
                $this->service->delSystemFields($elem->toArray()),
                ['table_permission_id' => $to->id]
            ) );
        }

        //Assigning permissions
        /*$to->_user_groups()->detach();
        foreach ($from->_user_groups as $elem) {
            $to->_user_groups()->attach( $elem->id, [
                'is_active' => $elem->pivot->is_active,
                'is_app' => $elem->pivot->is_app,
            ] );
        }
        $to->_default_fields()->delete();
        foreach ($from->_default_fields as $elem) {
            $to->_default_fields()->insert( array_merge(
                $this->service->delSystemFields($elem->toArray()),
                ['table_permission_id' => $to->id]
            ) );
        }*/

        return $res;
    }

    /**
     * Get Visitor Permission.
     *
     * @param $table_id
     * @param int $type - [1 = Visitor; 2 = ViaFolder]
     * @return mixed
     */
    public function getSysPermission($table_id, int $type)
    {
        return TablePermission::where('table_id', '=', $table_id)
            ->where('is_system', $type)
            ->first();
    }

    /**
     * Check availability of request address.
     *
     * @param $table_name
     * @param $link
     * @param $id
     * @return mixed
     */
    public function checkAddress($table_name, $link, $id = 0) {
        return TablePermission::where('id', '!=', $id)
            ->whereHas('_table', function ($t) use ($table_name) {
                $t->where('name', $table_name);
            })
            ->where('user_link', $link)
            ->count();
    }

    /**
     * Add Permission.
     *
     * @param $data
     * [
     *  +user_group_id: int,
     *  +table_id: int,
     * ]
     * @return mixed
     */
    public function addPermission($data)
    {
        if (!empty($data['user_group_id'])) {
            $ug = UserGroup::where('id', $data['user_group_id'])->first();
            $ug->_tables()->attach($data['table_id']);
        }
        if (!empty($data['is_request'])) {
            $data['active'] = 1;
            $data['enforced_theme'] = 1;
            $data['dcr_hash'] = Uuid::uuid4();
        }

        foreach ($data as $key => $val) {
            if (in_array($key, ['active','one_per_submission','dcr_form_shadow','dcr_form_transparency'])) {
                $data[$key] = 0;
            }
            if (in_array($key, ['dcr_title_line_top','dcr_title_line_bot','dcr_form_line_thick','dcr_sec_line_thick'])) {
                $data[$key] = 1;
            }
            if (in_array($key, ['dcr_form_line_radius'])) {
                $data[$key] = 10;
            }
            if (in_array($key, ['dcr_email_format','dcr_save_email_format','dcr_upd_email_format'])) {
                switch ($val) {
                    case 'list': $data[$key] = 'list'; break;
                    case 'vertical': $data[$key] = 'vertical'; break;
                    default: $data[$key] = 'table'; break;
                }
            }
            if (in_array($key, ['dcr_sec_bg_img_fit','dcr_title_bg_fit'])) {
                $data[$key] = 'Width';
            }
            if (in_array($key, ['dcr_form_line_type'])) {
                $data[$key] = 'line';
            }
            if (in_array($key, ['dcr_form_shadow_dir'])) {
                $data[$key] = 'BR';
            }
            if (in_array($key, ['dcr_sec_scroll_style'])) {
                $data[$key] = 'scroll';
            }
            if (in_array($key, ['dcr_sec_background_by','dcr_title_background_by'])) {
                $data[$key] = 'color';
            }
            if (in_array($key, ['dcr_form_width', 'dcr_title_width'])) {
                $data[$key] = 600;
            }
            if (in_array($key, ['dcr_confirm_msg'])) {
                $data[$key] = 'Thanks for your submission.';
            }
            if (in_array($key, ['dcr_save_confirm_msg'])) {
                $data[$key] = 'You may save the URL for future access of the saved record if permission is granted.';
            }
            if (in_array($key, ['dcr_upd_confirm_msg'])) {
                $data[$key] = 'Your submission has been updated.';
            }
            if (in_array($key, ['dcr_title_bg_color','dcr_form_bg_color'])) {
                $data[$key] = '#ffffff';
            }
        }

        $created = TablePermission::create( $this->service->delSystemFields($data) );
        $table_group = $this->getPermission($created->id); //because TablePermission::create returns not all fields
        $table_group->_column_groups = [];
        $table_group->_row_groups = [];
        $table_group->_default_fields = [];
        $table_group->_permission_rows = [];
        $table_group->_permission_columns = [];
        $table_group->_forbid_settings = [];
        return $table_group;
    }

    /**
     * Update Permission
     *
     * @param int $table_permissions_id
     * @param $data
     * @return array
     */
    public function updatePermission($table_permissions_id, $data)
    {
        foreach ($data as $key => $val) {
            if (in_array($key, ['dcr_email_format','dcr_save_email_format','dcr_upd_email_format'])) {
                switch ($val) {
                    case 'list': $data[$key] = 'list'; break;
                    case 'vertical': $data[$key] = 'vertical'; break;
                    default: $data[$key] = 'table'; break;
                }
            }
        }
        return TablePermission::where('id', $table_permissions_id)
            ->update( $this->service->delSystemFields($data) );
    }

    /**
     * Delete Permission
     *
     * @param int $table_permissions_id
     * @return mixed
     */
    public function deletePermission($table_permissions_id, $table_id)
    {
        return TablePermission::where('id', $table_permissions_id)->delete();
    }

    /**
     * Get link to Table Column Permission in Table Permission.
     *
     * @param $table_permission_id
     * @param $col_group_id
     * @return TablePermissionColumn
     */
    public function getTableColInPermission($table_permission_id, $col_group_id) {
        return TablePermissionColumn::where('table_permission_id', $table_permission_id)
            ->where('table_column_group_id', $col_group_id)
            ->first();
    }

    /**
     * @param $table_permission_id
     */
    public function clearStatusForNewColumns($table_permission_id) {
        $tp = TablePermission::where('id', $table_permission_id)
            ->with([
                '_user_groups' => function ($ug) {
                    $ug->with([
                        '_individuals_all' => function ($ia) {
                            $ia->select('users.id');
                        }
                    ]);
                    $ug->select('user_groups.id');
                }
            ])
            ->first();

        $user_ids = $tp->_user_groups ? $tp->_user_groups->pluck('_individuals_all') : null;
        $user_ids = $user_ids ? $user_ids->flatten()->pluck('id')->toArray() : null;

        TableStatuse::where('table_id', $tp->table_id)
            ->whereIn('user_id', $user_ids)
            ->delete();
    }

    /**
     * Update or Create Table Column Permission in Table Permission.
     *
     * @param $table_permission_id
     * @param $col_group_id
     * @param $view
     * @param $edit
     * @param $shared
     * @return int
     */
    public function updateTableColPermission($table_permission_id, $col_group_id, $view = 0, $edit = 0, $shared = 0) {
        $this->clearStatusForNewColumns($table_permission_id);

        $permis = TablePermissionColumn::where('table_permission_id', $table_permission_id)
            ->where('table_column_group_id', $col_group_id)
            ->first();

        if (!$permis) {
            $permis = TablePermissionColumn::create([
                'table_permission_id' => $table_permission_id,
                'table_column_group_id' => $col_group_id
            ]);
        }

        $permis->update( ['view' => $view, 'edit' => $edit, 'shared' => $shared] );

        return $permis;
    }

    /**
     * Get link to Table Row Permission in Table Permission.
     *
     * @param $table_permission_id
     * @param $row_group_id
     * @return TablePermissionRow
     */
    public function getTableRowInPermission($table_permission_id, $row_group_id) {
        return TablePermissionRow::where('table_permission_id', $table_permission_id)
            ->where('table_row_group_id', $row_group_id)
            ->first();
    }

    /**
     * Attach User Group to Table Permission.
     *
     * @param TablePermission $tablePermission
     * @param $user_group_id
     * @param $active
     * @return bool
     */
    public function attachUserGroupPermission(TablePermission $tablePermission, $user_group_id, $active)
    {
        $tablePermission->_user_groups()->attach($user_group_id, [
            'is_active' => $active ? 1 : 0,
            'table_id' => $tablePermission->table_id,
        ]);

        return UserGroup2TablePermission::where('table_permission_id', $tablePermission->id)
            ->where('user_group_id', $user_group_id)
            ->first();
    }

    /**
     * Update User Group in Table Permission.
     *
     * @param TablePermission $tablePermission
     * @param $user_group_id
     * @param $active
     * @return bool
     */
    public function updateUserGroupPermission(TablePermission $tablePermission, $user_group_id, $active)
    {
        if ($active && $tablePermission->active != 1) {
            $tablePermission->update(['active' => 1]);
        }

        return UserGroup2TablePermission::where('table_permission_id', $tablePermission->id)
            ->where('user_group_id', $user_group_id)
            ->update( ['is_active' => $active ? 1 : 0] );
    }

    /**
     * Detach User Group to Table Permission.
     *
     * @param TablePermission $tablePermission
     * @param $user_group_id
     * @return bool
     */
    public function detachUserGroupPermission(TablePermission $tablePermission, $user_group_id)
    {
        return $tablePermission->_user_groups()->detach($user_group_id);
    }

    /**
     * Update or Create Table Row Permission in Table Permission.
     *
     * @param $table_permission_id
     * @param $row_group_id
     * @param $view
     * @param $edit
     * @param $del
     * @param $shared
     * @return TablePermissionRow
     */
    public function updateTableRowPermission($table_permission_id, $row_group_id, $view = 0, $edit = 0, $del = 0, $shared = 0)
    {
        $permis = TablePermissionRow::where('table_permission_id', $table_permission_id)
            ->where('table_row_group_id', $row_group_id)
            ->first();

        if (!$permis) {
            $permis = TablePermissionRow::create([
                'table_permission_id' => $table_permission_id,
                'table_row_group_id' => $row_group_id
            ]);
        }

        $permis->update( ['view' => $view, 'edit' => $edit, 'delete' => $del, 'shared' => $shared] );

        return $permis;
    }

    /**
     * Get Default Field for provided TablePermission.
     *
     * @param Int $table_permission_id
     * @param Int $user_group_id (nullable)
     * @param Int $table_field_id
     * @return mixed
     */
    public function getDefField(Int $table_permission_id, $user_group_id, Int $table_field_id) {
        return TablePermissionDefaultField::where('table_permission_id', '=', $table_permission_id)
            ->where('user_group_id', '=', $user_group_id)
            ->where('table_field_id', '=', $table_field_id)
            ->first();
    }

    /**
     * Insert Default Field for provided TablePermission.
     *
     * @param $data
     * [
     *  +$table_permission_id: int,
     *  +$table_field_id: int,
     *  +default: string,
     * ]
     * @return mixed
     */
    public function insertDefField($data)
    {
        return TablePermissionDefaultField::create( $this->service->delSystemFields($data) );
    }

    /**
     * Update Default Field for provided TablePermission.
     *
     * @param Int $table_permission_id
     * @param Int $user_group_id (nullable)
     * @param Int $table_field_id
     * @param $default
     * @return mixed
     */
    public function updateDefField(Int $table_permission_id, $user_group_id, Int $table_field_id, $default)
    {
        return TablePermissionDefaultField::where('table_permission_id', '=', $table_permission_id)
            ->where('user_group_id', '=', $user_group_id)
            ->where('table_field_id', '=', $table_field_id)
            ->update( ['default' => $default] );
    }

    /**
     * Insert Addon Right to TablePermission.
     *
     * @param TablePermission $tablePermission
     * @param $addon_id
     * @param $type
     * @return Collection
     */
    public function insertAddonRight(TablePermission $tablePermission, $addon_id, $type) {
        if (
            ! $tablePermission->_addons()
                ->where('addons.id', $addon_id)
                ->where('table_permissions_2_addons.type', $type)
                ->count()
        ) {
            $tablePermission->_addons()->attach($addon_id, ['type' => $type]);
        }
        return $tablePermission->_addons()->get();
    }

    /**
     * @param TablePermission $tablePermission
     * @param int $addon_id
     * @param string $fld
     * @param $val
     * @return mixed
     */
    public function updateAddonRight(TablePermission $tablePermission, int $addon_id, string $fld, $val) {
        DB::table('table_permissions_2_addons')
            ->where('table_permission_id', $tablePermission->id)
            ->where('addon_id', $addon_id)
            ->update([$fld => $val]);

        return $tablePermission->_addons()->get();
    }

    /**
     * Delete Addon Right to TablePermission.
     *
     * @param TablePermission $tablePermission
     * @param $addon_id
     * @return Collection
     */
    public function deleteAddonRight(TablePermission $tablePermission, $addon_id, $type) {
        DB::table('table_permissions_2_addons')
            ->where('table_permission_id', $tablePermission->id)
            ->where('addon_id', $addon_id)
            ->where('type', $type)
            ->delete();

        return $tablePermission->_addons()->get();
    }

    /**
     * Insert DCR File to TablePermission.
     *
     * @param TablePermission $tablePermission
     * @param string $field
     * @param UploadedFile $upload_file
     * @return string
     */
    public function insertDCRFile(TablePermission $tablePermission, string $field, UploadedFile $upload_file) {
        if ($tablePermission->{$field}) {
            Storage::delete($tablePermission->{$field});
        }

        $fileRepo = app()->make(FileRepository::class);
        $filePath = $fileRepo->getStorageTable($tablePermission->_table) . '/';
        $fileName = 'dcr_'.Uuid::uuid4();
        $upload_file->storeAs('public/'.$filePath, $fileName);

        $tablePermission->{$field} = $filePath.$fileName;
        $tablePermission->save();
        return $tablePermission->{$field};
    }

    /**
     * Delete DCR File to TablePermission.
     *
     * @param TablePermission $tablePermission
     * @param string $field
     * @return bool
     */
    public function deleteDCRFile(TablePermission $tablePermission, string $field) {
        Storage::delete($tablePermission->{$field});

        return $tablePermission->update([
            $field => null
        ]);
    }

    /**
     * @param int $table_id
     * @param $user_id
     * @return mixed
     */
    public function canReference(int $table_id, int $user_id = null)
    {
        if ($user_id) {
            //for selected user
            return TablePermission::where('table_id', $table_id)
                ->where('can_reference', 1)
                ->where('referencing_shared', 1)
                ->isActiveForSelectedUser($user_id)
                ->count();
        } else {
            //for current user
            return TablePermission::where('table_id', $table_id)
                ->where('can_reference', 1)
                ->applyIsActiveForUserOrPermission()
                ->count();
        }
    }
}
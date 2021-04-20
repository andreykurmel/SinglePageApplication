<?php

namespace Vanguard\Repositories\Tablda\Permissions;


use Illuminate\Support\Facades\DB;
use Vanguard\Models\DataSetPermissions\CondFormat;
use Vanguard\Models\DataSetPermissions\CondFormatUserSetting;
use Vanguard\Models\DataSetPermissions\TableRefCondition;
use Vanguard\Models\DataSetPermissions\TableRefConditionItem;
use Vanguard\Models\User\UserGroup;
use Vanguard\Services\Tablda\HelperService;
use Vanguard\Singletones\AuthUserModule;
use Vanguard\Singletones\AuthUserSingleton;
use Vanguard\Singletones\OtherUserModule;
use Vanguard\User;

class CondFormatsRepository
{
    protected $service;

    /**
     * UserGroupRepository constructor.
     */
    public function __construct()
    {
        $this->service = new HelperService();
    }

    /**
     * Prepare Relations and Fields.
     *
     * @param CondFormat $format
     * @param int|null $user_id
     * @return CondFormat
     */
    public function prepareCondFormatFields(CondFormat $format, int $user_id = null) {
        $format->loadMissing('_user_settings', '_table_permissions');

        if ($format->user_id != $user_id) {
            $setting = $format->_user_settings
                ->where('user_id', $user_id)
                ->first();
            $format->status = $setting ? $setting->status : $format->status;
        }

        //set 'always on' statuses
        $format->_special_statuses = $format->_table_permissions->pluck('_link')->flatten();

        //set that collaborator cannot edit
        $format->_always_on = $this->flattenPermission($format, 'always_on', $user_id);
        if ($format->_always_on) {
            $format->status = 1;
        }

        //set that collaborator cannot view CF
        $format->_visible_shared = $this->flattenPermission($format, 'visible_shared', $user_id);

        //remove unnecessary data
        unset($format->_table_permissions);

        return $format;
    }

    /**
     * @param CondFormat $format
     * @param $field
     * @param int|null $user_id
     * @return mixed
     */
    private function flattenPermission(CondFormat $format, $field, int $user_id = null)
    {
        $auth = new OtherUserModule($user_id);

        if (is_null($user_id)) {
            $res = $format->_table_permissions->where('is_system', 1);
        } else {
            $res = $format->_table_permissions->whereIn('id', $auth->getTablePermissionIdsMember());
        }

        return $res->pluck('_link')
            ->flatten()
            ->where($field, 1)
            ->count();
    }

    /**
     * Get Conditional Formatting.
     *
     * @param $format_id
     * @return mixed
     */
    public function getFormat($format_id)
    {
        return CondFormat::where('id', '=', $format_id)->first();
    }

    /**
     * Add Conditional Formatting.
     *
     * @param $data
     * [
     *  +table_id: int,
     *  +user_id: int,
     *  +name: string,
     *  -table_column_group_id: int,
     *  -table_row_group_id: int,
     *  -object: string,
     *  -font: string,
     *  -color: string,
     *  -activity: string,
     *  -status: int,
     * ]
     * @return CondFormat
     */
    public function addFormat($data)
    {
        if (empty($data['status'])) {
            $data['status'] = 0;
        }
        if (empty($data['show_table_data'])) {
            $data['show_table_data'] = 1;
        }
        if (empty($data['show_form_data'])) {
            $data['show_form_data'] = 1;
        }

        $format = CondFormat::create( array_merge($this->service->delSystemFields($data), $this->service->getModified(), $this->service->getCreated()) );
        $format->row_order = $format->id;
        $format->save();

        return $this->prepareCondFormatFields($format, auth()->id());
    }

    /**
     * Update Conditional Formatting.
     *
     * @param int $group_id
     * @param $data
     * [
     *  -table_id: int,
     *  -user_id: int,
     *  -name: string,
     *  -table_column_group_id: int,
     *  -table_row_group_id: int,
     *  -object: string,
     *  -font: string,
     *  -color: string,
     *  -activity: string,
     *  -status: int,
     * ]
     * @return CondFormat
     */
    public function updateFormat($group_id, $data)
    {
        CondFormat::where('id', $group_id)
            ->update( array_merge($this->service->delSystemFields($data), $this->service->getModified()) );

        $format = $this->getFormat($group_id);
        return $this->prepareCondFormatFields($format, auth()->id());
    }

    /**
     * Delete Conditional Formatting.
     *
     * @param int $group_id
     * @return mixed
     */
    public function deleteFormat($group_id)
    {
        return CondFormat::where('id', $group_id)->delete();
    }

    /**
     * Update Conditional Format Settings.
     *
     * @param int $cond_format_id
     * @param int $user_id
     * @param $data
     * [
     *  +status: int,
     * ]
     * @return mixed
     */
    public function updateFormatSettings($cond_format_id, $user_id, $data)
    {
        return CondFormatUserSetting::updateOrCreate(
            ['cond_format_id' => $cond_format_id, 'user_id' => $user_id],
            $this->service->delSystemFields($data)
        );
    }

    /**
     * Add right on CondFormat to UserGroup.
     *
     * @param CondFormat $condFormat
     * @param int $table_permission_id
     * @return CondFormat
     */
    public function addRightForCondFormat(CondFormat $condFormat, int $table_permission_id)
    {
        $condFormat->_table_permissions()->attach($table_permission_id);
        return $this->prepareCondFormatFields($condFormat, auth()->id());
    }

    /**
     * @param CondFormat $condFormat
     * @param int $table_permission_id
     * @param $always_on
     * @param $visible_shared
     * @return CondFormat
     */
    public function updateRightForCondFormat(CondFormat $condFormat, int $table_permission_id, $always_on, $visible_shared)
    {
        DB::table('table_permissions_2_cond_formats')
            ->where('table_permission_id', $table_permission_id)
            ->where('cond_format_id', $condFormat->id)
            ->update([
                'always_on' => $always_on ? 1 : 0,
                'visible_shared' => $visible_shared ? 1 : 0,
            ]);

        return $this->prepareCondFormatFields($condFormat, auth()->id());
    }

    /**
     * Delete right on CondFormat from UserGroup.
     *
     * @param CondFormat $condFormat
     * @param int $table_permission_id
     * @return CondFormat
     */
    public function deleteRightFromCondFormat(CondFormat $condFormat, int $table_permission_id)
    {
        $condFormat->_table_permissions()->detach($table_permission_id);
        return $this->prepareCondFormatFields($condFormat, auth()->id());
    }
}
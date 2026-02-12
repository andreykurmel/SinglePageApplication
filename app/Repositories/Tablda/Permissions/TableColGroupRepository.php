<?php

namespace Vanguard\Repositories\Tablda\Permissions;


use Illuminate\Database\Eloquent\Collection;
use Vanguard\Models\DataSetPermissions\TableColumnGroup;
use Vanguard\Models\User\UserGroup;
use Vanguard\Models\DataSetPermissions\TablePermissionColumn;
use Vanguard\Repositories\Tablda\TableGroupingRepository;
use Vanguard\Services\Tablda\HelperService;
use Vanguard\User;

class TableColGroupRepository
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
     * Get Group of Columns.
     *
     * @param $group_id
     * @return mixed
     */
    public function getColGroup($group_id)
    {
        return TableColumnGroup::where('id', '=', $group_id)->first();
    }

    /**
     * @param int $table_id
     * @return mixed
     */
    public function getSys(int $table_id)
    {
        return TableColumnGroup::where('is_system', '=', 1)
            ->where('table_id', '=', $table_id)
            ->first();
    }

    /**
     * Get Group of Columns with Fields.
     *
     * @param $group_id
     * @return mixed
     */
    public function getColGroupWithFields($group_id)
    {
        return TableColumnGroup::with('_fields')->where('id', '=', $group_id)->first();
    }

    /**
     * Add Group of Columns.
     *
     * @param $data
     * [
     *  +table_id: int,
     *  +name: string,
     *  -notes: string,
     * ]
     * @return mixed
     */
    public function addColGroup($data)
    {
        $col_group = TableColumnGroup::create( array_merge($this->service->delSystemFields($data), $this->service->getModified(), $this->service->getCreated()) );
        $col_group->_fields = [];
        return $col_group;
    }

    /**
     * Update Group of Columns.
     *
     * @param int $group_id
     * @param $data
     * [
     *  -table_id: int,
     *  -name: string,
     *  -notes: string,
     * ]
     * @return array
     */
    public function updateColGroup($group_id, $data)
    {
        return TableColumnGroup::where('id', $group_id)
            ->update( array_merge($this->service->delSystemFields($data), $this->service->getModified()) );
    }

    /**
     * Delete Group of Columns.
     *
     * @param int $group_id
     * @return mixed
     */
    public function deleteColGroup($group_id)
    {
        return TableColumnGroup::where('id', $group_id)->delete();
    }

    /**
     * Add all table fields to Group of Columns.
     *
     * @param TableColumnGroup $columnGroup
     * @param Collection $table_fields
     * @param array $except_columns
     * @return bool
     */
    public function checkAllColFields(TableColumnGroup $columnGroup, Collection $table_fields, Array $except_columns)
    {
        $arr = [];
        foreach ($table_fields as $tableField) {
            if (!$except_columns || !in_array($tableField->field, $except_columns)) {
                $arr[$tableField->id] = array_merge(['checked' => true], $this->service->getModified(), $this->service->getCreated());
            }
        }
        $columnGroup->_fields()->attach($arr);
        (new TableGroupingRepository())->syncRelatedFields([], [$columnGroup->id]);
        return 1;
    }

    /**
     * Add table field to Group of Columns.
     *
     * @param TableColumnGroup $columnGroup
     * @param array $table_field_ids
     * @return int
     */
    public function addColFieldToGroup(TableColumnGroup $columnGroup, Array $table_field_ids)
    {
        $columnGroup->_fields()->attach(
            $table_field_ids,
            array_merge($this->service->getModified(), $this->service->getCreated())
        );
        (new TableGroupingRepository())->syncRelatedFields([], [$columnGroup->id]);
        return 1;
    }

    /**
     * Delete table field from Group of Columns.
     *
     * @param TableColumnGroup $columnGroup
     * @param array $table_field_ids
     * @return int
     */
    public function deleteColFieldFromGroup(TableColumnGroup $columnGroup, Array $table_field_ids)
    {
        $res = $columnGroup->_fields()->detach($table_field_ids);
        (new TableGroupingRepository())->syncRelatedFields([], [$columnGroup->id]);
        return $res;
    }
}
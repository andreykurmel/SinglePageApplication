<?php

namespace Vanguard\Repositories\Tablda\Permissions;


use Vanguard\Models\DataSetPermissions\TableRowGroup;
use Vanguard\Models\DataSetPermissions\TableRowGroupCondition;
use Vanguard\Models\DataSetPermissions\TableRowGroupRegular;
use Vanguard\Models\Table\Table;
use Vanguard\Models\User\UserGroup;
use Vanguard\Repositories\Tablda\TableData\TableDataQuery;
use Vanguard\Services\Tablda\HelperService;
use Vanguard\User;

class TableRowGroupRepository
{
    protected $service;

    /**
     * UserGroupRepository constructor.
     *
     */
    public function __construct()
    {
        $this->service = new HelperService();
    }

    /**
     * Get Group of Rows.
     *
     * @param $group_id
     * @return mixed
     */
    public function getRowGroup($group_id)
    {
        return TableRowGroup::where('id', '=', $group_id)->first();
    }

    /**
     * @param $groups_ids
     * @return mixed
     */
    public function getRelaredRefConds($groups_ids)
    {
        return TableRowGroup::whereIn('id', $groups_ids)
            ->get()
            ->pluck('row_ref_condition_id')
            ->toArray();
    }

    /**
     * When changed row in Table -> also update Row Json In Group.
     *
     * @param Table $table
     * @param $row_id
     * @param $updated_row
     */
    public function updateRowJsonInGroup(Table $table, $row_id, $updated_row) {
        TableRowGroupRegular::where('list_field', 'id')
            ->where('field_value', $row_id)
            ->whereHas('_row_group', function ($q) use ($table) {
                $q->where('table_id', $table->id);
            })
            ->update([
                'row_json' => $updated_row
            ]);
    }

    /**
     * Get Available Row Groups.
     *
     * @param $table_id
     * @param null $table_permission_id
     * @param null $row_group_edit
     * @return mixed
     */
    public function getAvailableRowGroups($table_id, $table_permission_id = null, $row_group_edit = null)
    {
        return TableRowGroup::where('table_id', $table_id)
            ->whereHas('_table_permission_rows', function ($tpc) use ($table_permission_id, $row_group_edit) {
                $tpc->where($row_group_edit ? 'edit' : 'view', 1);
                $tpc->whereHas('_table_permission', function ($tp) use ($table_permission_id) {
                    $tp->applyIsActiveForUserOrPermission($table_permission_id, true);
                });
            })
            ->get();
    }

    /**
     * Get Group of Rows By Condition ID.
     *
     * @param $condition_id
     * @return mixed
     */
    public function getRowGroupByCondId($condition_id)
    {
        $tb_row = TableRowGroupCondition::where('id', '=', $condition_id)->first();
        return TableRowGroup::where('id', '=', $tb_row->table_row_group_id)->first();
    }

    /**
     * Get Group of Rows By Regular ID.
     *
     * @param $regular_id
     * @return mixed
     */
    public function getRowGroupByRegId($regular_id)
    {
        $tb_row = TableRowGroupRegular::where('id', '=', $regular_id)->first();
        return TableRowGroup::where('id', '=', $tb_row->table_row_group_id)->first();
    }

    /**
     * Add Group of Rows.
     *
     * @param $data
     * [
     *  +table_id: int,
     *  +name: string,
     *  -notes: string,
     * ]
     * @return mixed
     */
    public function addRowGroup($data)
    {
        $row_group = TableRowGroup::create( array_merge($this->service->delSystemFields($data), $this->service->getModified(), $this->service->getCreated()) );
        $row_group->is_showed = true;
        $row_group->_conditions = [];
        $row_group->_regulars = [];
        return $row_group;
    }

    /**
     * Update Group of Rows.
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
    public function updateRowGroup($group_id, $data)
    {
        $rg = TableRowGroup::where('id', $group_id)->first();
        //delete all Regulars if changed ListingField in parent row.
        /*if ($rg && !empty($data['listing_field']) && $rg->listing_field != $data['listing_field']) {
            TableRowGroupRegular::where('table_row_group_id', $group_id)->delete();
        }*/

        TableRowGroup::where('id', $group_id)
            ->update( array_merge($this->service->delSystemFields($data), $this->service->getModified()) );

        return $rg->_regulars()->get();
    }

    /**
     * Delete Group of Rows.
     *
     * @param int $group_id
     * @return mixed
     */
    public function deleteRowGroup($group_id)
    {
        return TableRowGroup::where('id', $group_id)->delete();
    }

    /**
     * Add Row Group Condition.
     *
     * @param $data
     * [
     *  +table_row_group_id: int,
     *  +table_field_id: int,
     *  +compared_operator: string,
     *  +compared_value: string,
     *  -logic_operator: string,
     * ]
     * @return mixed
     */
    public function addRowGroupCondition($data)
    {
        $rg_cond = TableRowGroupCondition::create( array_merge($this->service->delSystemFields($data), $this->service->getModified(), $this->service->getCreated()) );
        return TableRowGroupCondition::where('id', $rg_cond->id)
            ->with('_field')
            ->first();
    }

    /**
     * Update Row Group Condition.
     *
     * @param int $group_id
     * @param $data
     * [
     *  -table_row_group_id: int,
     *  -table_field_id: int,
     *  -compared_operator: string,
     *  -compared_value: string,
     *  -logic_operator: string,
     * ]
     * @return array
     */
    public function updateRowGroupCondition($group_id, $data)
    {
        TableRowGroupCondition::where('id', $group_id)
            ->update( array_merge($this->service->delSystemFields($data), $this->service->getModified()) );

        return TableRowGroupCondition::where('id', $group_id)
            ->with('_field')
            ->first();
    }

    /**
     * Delete Row Group Condition.
     *
     * @param int $group_id
     * @return mixed
     */
    public function deleteRowGroupCondition($group_id)
    {
        return TableRowGroupCondition::where('id', $group_id)->delete();
    }

    /**
     * Add a lot of Row Group Regular.
     *
     * @param Table $table
     * @param int $rg_id
     * @param array $rows_ids
     * @return mixed
     */
    public function addRowGroupRegularMass(Table $table, int $rg_id, array $rows_ids)
    {
        $present_regulars = TableRowGroupRegular::where('table_row_group_id', $rg_id)->get();
        $rows_objects = (new TableDataQuery($table))
            ->getQuery()
            ->whereIn('id', $rows_ids)
            ->get();

        $data = [];
        foreach ($rows_ids as $row_id) {
            $present = $present_regulars->where('list_field', 'id')
                ->where('field_value', $row_id)
                ->first();

            if (!$present) {
                $json_row = $rows_objects->where('id', $row_id)
                    ->first()
                    ->toJson();

                $data[] = [
                    'table_row_group_id' => $rg_id,
                    'list_field' => 'id',
                    'field_value' => $row_id,
                    'row_json' => $json_row,
                ];
            }
        }

        TableRowGroupRegular::insert($data);

        return TableRowGroupRegular::where('table_row_group_id', $rg_id)->get();
    }

    /**
     * Add all rows to RowGroupRegulars.
     *
     * @param Table $table
     * @param int $rg_id
     * @param array $request_params
     * @param int|null $user_id
     * @return mixed
     */
    public function addRowGroupAllToRegular(Table $table, int $rg_id, array $request_params, int $user_id = null)
    {
        $sql = new TableDataQuery($table, true, $user_id);
        $sql->applyWhereClause($request_params, $user_id);

        $rows_ids = $sql->getQuery()
            ->select('id')
            ->get()
            ->pluck('id')
            ->toArray();

        return $this->addRowGroupRegularMass($table, $rg_id, $rows_ids);
    }

    /**
     * Add Row Group Regular.
     *
     * @param $data
     * [
     *  +table_row_group_id: int,
     *  -field_value: string,
     * ]
     * @return mixed
     */
    public function addRowGroupRegular($data)
    {
        $rg_cond = TableRowGroupRegular::create( $this->service->delSystemFields($data) );
        return TableRowGroupRegular::where('id', $rg_cond->id)
            ->first();
    }

    /**
     * Update Row Group Regular.
     *
     * @param int $group_id
     * @param $data
     * [
     *  -table_row_group_id: int,
     *  -field_value: string,
     * ]
     * @return array
     */
    public function updateRowGroupRegular($group_id, $data)
    {
        TableRowGroupRegular::where('id', $group_id)
            ->update( $this->service->delSystemFields($data) );

        return TableRowGroupRegular::where('id', $group_id)
            ->first();
    }

    /**
     * Delete Row Group Regular.
     *
     * @param int $group_id
     * @return mixed
     */
    public function deleteRowGroupRegular($group_id)
    {
        return TableRowGroupRegular::where('id', $group_id)->delete();
    }

    /**
     * @param $ref_cond_id
     * @return mixed
     */
    public function syncRefCond($ref_cond_id)
    {
        return TableRowGroup::where('row_ref_condition_id', '=', $ref_cond_id)->update([
            'row_ref_condition_id' => null,
        ]);
    }
}
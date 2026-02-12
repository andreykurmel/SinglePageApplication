<?php

namespace Vanguard\Http\Controllers\Web\Tablda;


use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Http\Request;
use Vanguard\Http\Controllers\Controller;
use Vanguard\Models\Table\TableData;
use Vanguard\Models\Table\TableGrouping;
use Vanguard\Models\Table\TableGroupingRight;
use Vanguard\Repositories\Tablda\TableRepository;
use Vanguard\Repositories\Tablda\TableGroupingRepository;

class TableGroupingController extends Controller
{
    use CanEditAddon;

    protected $tableRepository;
    protected $repoGrouping;

    /**
     * TableBackupsController constructor.
     *
     * @param TableRepository $tableRepository
     */
    public function __construct(TableRepository $tableRepository)
    {
        $this->tableRepository = new TableRepository();
        $this->repoGrouping = new TableGroupingRepository();
    }

    /**
     * @param Request $request
     * @return mixed
     * @throws AuthorizationException
     */
    public function insert(Request $request)
    {
        $table = $this->tableRepository->getTable($request->table_id);
        $this->canEditAddon($table, 'grouping');
        $this->repoGrouping->addTableGrouping($table->id, $request->fields);
        $this->repoGrouping->loadForTable($table, auth()->id());
        return $table->_groupings;
    }

    /**
     * @param Request $request
     * @return mixed
     * @throws AuthorizationException
     */
    public function update(Request $request)
    {
        $table_grouping = $this->repoGrouping->getTableGrouping($request->table_grouping_id);
        $table = $table_grouping->_table;
        $this->canEditAddonItem($table_grouping->_table, $table_grouping->_grouping_rights());
        $this->repoGrouping->updateTableGrouping($table_grouping->id, array_merge($request->fields, ['table_id' => $table->id]));
        $this->repoGrouping->loadForTable($table, auth()->id());
        return $table->_groupings;
    }

    /**
     * @param Request $request
     * @return mixed
     * @throws AuthorizationException
     */
    public function delete(Request $request)
    {
        $table_grouping = $this->repoGrouping->getTableGrouping($request->table_grouping_id);
        $table = $table_grouping->_table;
        $this->canEditAddonItem($table_grouping->_table, $table_grouping->_grouping_rights());
        $this->repoGrouping->deleteTableGrouping($table_grouping->id);
        $this->repoGrouping->loadForTable($table, auth()->id());
        return $table->_groupings;
    }

    /**
     * @param Request $request
     * @return mixed
     * @throws AuthorizationException
     */
    public function getFields(Request $request)
    {
        $table_grouping = $this->repoGrouping->getTableGrouping($request->table_grouping_id);
        $table = $table_grouping->_table;
        $this->repoGrouping->loadForTable($table, auth()->id());
        return $table->_groupings;
    }

    /**
     * @param Request $request
     * @return mixed
     * @throws AuthorizationException
     */
    public function insertField(Request $request)
    {
        $table_grouping = $this->repoGrouping->getTableGrouping($request->table_grouping_id);
        $table = $table_grouping->_table;
        $this->canEditAddonItem($table_grouping->_table, $table_grouping->_grouping_rights());
        $this->repoGrouping->addTableGroupingField($table_grouping->id, $request->fields);
        $this->repoGrouping->loadForTable($table, auth()->id());
        return $table->_groupings;
    }

    /**
     * @param Request $request
     * @return mixed
     * @throws AuthorizationException
     */
    public function updateField(Request $request)
    {
        $table_grouping_field = $this->repoGrouping->getTableGroupingField($request->table_grouping_field_id);
        $table_grouping = $table_grouping_field->_grouping;
        $table = $table_grouping->_table;
        $this->canEditAddonItem($table_grouping->_table, $table_grouping->_grouping_rights());
        $this->repoGrouping->updateTableGroupingField($table_grouping_field->id, array_merge($request->fields, ['grouping_id' => $table_grouping->id]));
        $this->repoGrouping->loadForTable($table, auth()->id());
        return $table->_groupings;
    }

    /**
     * @param Request $request
     * @return mixed
     * @throws AuthorizationException
     */
    public function deleteField(Request $request)
    {
        $table_grouping_field = $this->repoGrouping->getTableGroupingField($request->table_grouping_field_id);
        $table_grouping = $table_grouping_field->_grouping;
        $table = $table_grouping->_table;
        $this->canEditAddonItem($table_grouping->_table, $table_grouping->_grouping_rights());
        $this->repoGrouping->deleteTableGroupingField($table_grouping_field->id);
        $this->repoGrouping->loadForTable($table, auth()->id());
        return $table->_groupings;
    }

    /**
     * @param Request $request
     * @return mixed
     * @throws AuthorizationException
     */
    public function insertStat(Request $request)
    {
        if ($request->type == 'global') {
            $table_grouping = $this->repoGrouping->getTableGrouping($request->parent_id);
        } else {
            $table_grouping_field = $this->repoGrouping->getTableGroupingField($request->parent_id);
            $table_grouping = $table_grouping_field->_grouping;
        }
        $table = $table_grouping->_table;
        $this->canEditAddonItem($table_grouping->_table, $table_grouping->_grouping_rights());
        $this->repoGrouping->addTableGroupingStat($request->parent_id, $request->fields, $request->type);
        $this->repoGrouping->loadForTable($table, auth()->id());
        return $table->_groupings;
    }

    /**
     * @param Request $request
     * @return mixed
     * @throws AuthorizationException
     */
    public function updateStat(Request $request)
    {
        if ($request->type == 'global') {
            $table_grouping_stat = $this->repoGrouping->getTableGroupingStat($request->stat_id);
            $table_grouping = $table_grouping_stat->_grouping;
        } else {
            $table_grouping_stat = $this->repoGrouping->getTableGroupingFieldStat($request->stat_id);
            $table_grouping_field = $table_grouping_stat->_grouping_field;
            $table_grouping = $table_grouping_field->_grouping;
        }
        $table = $table_grouping->_table;
        $this->canEditAddonItem($table_grouping->_table, $table_grouping->_grouping_rights());
        $this->repoGrouping->updateTableGroupingStat($request->stat_id, $request->fields, $request->type);
        $this->repoGrouping->loadForTable($table, auth()->id());
        return $table->_groupings;
    }

    /**
     * @param Request $request
     * @return mixed
     * @throws AuthorizationException
     */
    public function deleteStat(Request $request)
    {
        if ($request->type == 'global') {
            $table_grouping_stat = $this->repoGrouping->getTableGroupingStat($request->stat_id);
            $table_grouping = $table_grouping_stat->_grouping;
        } else {
            $table_grouping_stat = $this->repoGrouping->getTableGroupingFieldStat($request->stat_id);
            $table_grouping_field = $table_grouping_stat->_grouping_field;
            $table_grouping = $table_grouping_field->_grouping;
        }
        $table = $table_grouping->_table;
        $this->canEditAddonItem($table_grouping->_table, $table_grouping->_grouping_rights());
        $this->repoGrouping->deleteTableGroupingStat($request->stat_id, $request->type);
        $this->repoGrouping->loadForTable($table, auth()->id());
        return $table->_groupings;
    }

    /**
     * @param Request $request
     * @return TableGroupingRight
     * @throws AuthorizationException
     */
    public function toggleGroupingRight(Request $request)
    {
        $grouping = $this->repoGrouping->getTableGrouping($request->grouping_id);
        $this->authorizeForUser(auth()->user(), 'isOwner', [TableData::class, $grouping->_table]);
        return $this->repoGrouping->toggleGroupingRight($grouping, $request->permission_id, $request->can_edit);
    }

    /**
     * @param Request $request
     * @return mixed
     * @throws AuthorizationException
     */
    public function delGroupingRight(Request $request)
    {
        $grouping = $this->repoGrouping->getTableGrouping($request->grouping_id);
        $this->authorizeForUser(auth()->user(), 'isOwner', [TableData::class, $grouping->_table]);
        return $this->repoGrouping->deleteGroupingRight($grouping, $request->permission_id);
    }

    /**
     * @param Request $request
     * @return string
     */
    public function updateRelatedFields(Request $request)
    {
        $table_grouping = $this->repoGrouping->getTableGrouping($request->table_grouping_id);
        $table = $table_grouping->_table;
        $this->canEditAddonItem($table_grouping->_table, $table_grouping->_grouping_rights());
        $this->repoGrouping->updateRelatedFields($request->table_grouping_id, $request->related_ids, $request->visibility);
        return 'Updated';
    }

    /**
     * @param Request $request
     * @return \Illuminate\Support\Collection|TableGrouping[]
     */
    public function reorderRelatedFields(Request $request)
    {
        $table_grouping = $this->repoGrouping->getTableGrouping($request->table_grouping_id);
        $table = $table_grouping->_table;
        $this->canEditAddonItem($table_grouping->_table, $table_grouping->_grouping_rights());
        $this->repoGrouping->reorderRelatedField($request->table_grouping_id, $request->before_id, $request->after_id);
        $this->repoGrouping->loadForTable($table, auth()->id());
        return $table->_groupings;
    }
}

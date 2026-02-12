<?php

namespace Vanguard\Modules;

use Exception;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Vanguard\Models\DataSetPermissions\TableRefCondition;
use Vanguard\Models\DDLReference;
use Vanguard\Models\Table\Table;
use Vanguard\Models\Table\TableField;
use Vanguard\Repositories\Tablda\TableData\TableDataQuery;
use Vanguard\Repositories\Tablda\TableRepository;
use Vanguard\Services\Tablda\TableDataService;
use Vanguard\Services\Tablda\TableService;
use Vanguard\Support\PrefixSufixHelper;

class InheritColumnModule
{
    /**
     * @var int[]
     */
    public static $processed_table_ids = [
        0 => [0],
        1 => [0],
        2 => [0],
        3 => [0],
    ];
    public static $fields_shared_ddls = [];
    /**
     * @var int
     */
    protected static $level = 1;

    /**
     * @var Table
     */
    protected $table;
    /**
     * @var TableDataService
     */
    protected $td_service;
    /**
     * @var TableService
     */
    protected $t_service;

    /**
     *
     */
    public function __construct(Table $table)
    {
        $this->table = $table;
        $this->t_service = new TableService();
        $this->td_service = new TableDataService();
    }

    /**
     * @param array $tb_ids
     * @param int $lvl
     * @return array
     */
    public static function childrenTables(array $tb_ids, int $lvl = 1): array
    {
        $result = [];
        $tables = (new TableRepository())->getTables($tb_ids);
        foreach ($tables as $tb) {
            if ($lvl < 3) {
                $children_ids = self::queryChildrenForTable($tb->id)
                    ->get()
                    ->pluck('table_id')
                    ->unique()
                    ->toArray();
            } else {
                $children_ids = [];
            }

            $result[] = [
                'id' => $tb->id,
                'name' => $tb->name,
                'checked' => 1,
                'children' => $children_ids ? self::childrenTables($children_ids, $lvl+1) : [],
            ];
        }
        return $result;
    }

    /**
     * @param int $master_id
     * @return array
     */
    public static function inheritedRefCondIds(int $master_id): array
    {
        return self::queryChildrenForTable($master_id)
            ->get()
            ->pluck('id')
            ->unique()
            ->toArray();
    }

    /**
     * @param array $table_row
     * @param int $lvl
     * @return void
     */
    public function checkForCopy(array $table_row, int $lvl = 0)
    {
        if ($this->checkLvl($lvl)) {
            foreach ($this->getInheritances($lvl) as $table_id => $group) {
                self::$processed_table_ids[$lvl][] = $table_id;

                foreach ($this->getArrayOfRelatedIds($table_id, $group, $table_row) as $item) {
                    $refTb = $item['refTable'];
                    $relations = $item['relations'];
                    $related_rows = $item['relatedRows'];

                    if ($related_rows->count()) {
                        PrefixSufixHelper::cacheFromMaster($this->table, $refTb, $relations, $table_row);
                        $this->td_service->massCopy($refTb, $related_rows->toArray(), [], [], [], $lvl+1);
                    }
                }

            }
        }
    }

    /**
     * @param int $curLevel
     * @return bool
     */
    protected function checkLvl(int $curLevel): bool
    {
        return $curLevel <= 3;
    }

    /**
     * @param int $lvl
     * @return Collection
     */
    protected function getInheritances(int $lvl): Collection
    {
        $table_id = $this->table->id;
        $references = self::queryChildrenForTable($table_id, $lvl)
            ->with([
                '_table' => function ($q) {
                    $q->with('_fields');
                },
                '_applied_ddlrefs' => function ($ref) use ($table_id) {
                    $ref->with([
                        '_target_field:id,field,name',
                        '_applied_fields' => function ($fld) use ($table_id) {
                            $fld->where('is_inherited_tree', '=', 1);
                            $fld->select(['id','field','name','ddl_id','table_id']);
                        },
                    ]);
                },
            ])
            ->get();
        return $references->groupBy('table_id');
    }

    /**
     * @param int $table_id
     * @param int $lvl
     * @return Builder
     */
    protected static function queryChildrenForTable(int $table_id, int $lvl = 1): Builder
    {
        $fieldsWithSharedDDL = self::fieldsSharedDDLs($table_id);
        $directSharedRcids = $fieldsWithSharedDDL->pluck('__ref_rcs_ids')->flatten()->push(0);

        $processed = [];
        for ($i = 0; $i < $lvl; $i++) {
            $processed = array_merge($processed, self::$processed_table_ids[$i]);
        }
        return TableRefCondition::whereIn('id', $directSharedRcids)
            ->orWhere(function ($query) use ($table_id, $processed) {
                $query->where('ref_table_id', $table_id)
                    ->where('table_id', '!=', $table_id)
                    ->whereNotIn('table_id', $processed)
                    ->whereHas('_applied_ddlrefs', function ($ref) use ($table_id) {
                        $ref->whereHas('_applied_fields', function ($fld) use ($table_id) {
                            $fld->where('is_inherited_tree', '=', 1);
                        });
                    });
            });
    }

    /**
     * @param int $table_id
     * @return Collection
     */
    protected static function fieldsSharedDDLs(int $table_id): Collection
    {
        if (self::$fields_shared_ddls[$table_id] ?? null) {
            return self::$fields_shared_ddls[$table_id];
        }

        $sharedDDLRefs = DDLReference::whereHas('_ref_condition', function ($q) use ($table_id) {
            $q->where('ref_table_id', $table_id);
        })->get(['id']);

        $fieldsWithSharedDDL = TableField::where('is_inherited_tree', '=', 1)
            ->whereNotNull('shared_ddlref_ids')
            ->where(function ($q) use ($sharedDDLRefs) {
                foreach ($sharedDDLRefs as $ref) {
                    $q->orWhere('shared_ddlref_ids', 'like', '%"'.($ref->id).'"%');
                }
            })
            ->get(['id','field','table_id','shared_ddlref_ids']);

        if ($fieldsWithSharedDDL->count() > 0) {
            foreach ($fieldsWithSharedDDL as $item) {
                $item->__ddlrefs = DDLReference::whereIn('id', self::sharedDdlrefIds($item))
                    ->with('_target_field:id,field,name')
                    ->get();
                $item->__ref_rcs_ids = $item->__ddlrefs->pluck('table_ref_condition_id');
            }
        }

        self::$fields_shared_ddls[$table_id] = $fieldsWithSharedDDL;
        return self::$fields_shared_ddls[$table_id];
    }

    /**
     * @param $item
     * @return array|mixed
     */
    protected static function sharedDdlrefIds($item)
    {
        return is_array($item->shared_ddlref_ids) ? $item->shared_ddlref_ids : json_decode($item->shared_ddlref_ids);
    }

    /**
     * @param Collection $refCondGroup
     * @param array $table_row
     * @return Builder
     */
    protected function rowsQuery(Collection $refCondGroup, array $table_row): Builder
    {
        $refTb = $refCondGroup->first()->_table;
        $sql = (new TableDataQuery($refTb))->getQuery();
        foreach ($this->fieldsRelations($refCondGroup) as $current => $master) {
            $sql->where($current, '=', $table_row[$master]);
        }
        return $sql;
    }

    /**
     * @param Collection $refCondGroup
     * @param int|null $shared_table_id
     * @return array
     */
    protected function fieldsRelations(Collection $refCondGroup, int $shared_table_id = null): array
    {
        $relations = [];
        foreach ($refCondGroup as $refCond) {
            $current = $this->currentFld($refCond, $shared_table_id);
            $master = $this->masterFld($refCond);
            if ($current && $master) {
                $relations[$current] = $master;
            }
        }
        return $relations;
    }

    /**
     * @param TableRefCondition $refCondition
     * @param int|null $shared_table_id
     * @return string
     */
    protected function currentFld(TableRefCondition $refCondition, int $shared_table_id = null): string
    {
        $ddlRef = $this->findAppliedDdlref($refCondition);
        $curFld = $ddlRef ? $ddlRef->_applied_fields->first() : null;

        $fieldsWithSharedDDL = self::fieldsSharedDDLs($refCondition->ref_table_id);
        $sharedFld = $shared_table_id
            ? $fieldsWithSharedDDL
                ->filter(function ($item) use ($ddlRef) {
                    return $ddlRef && in_array($ddlRef->id, self::sharedDdlrefIds($item));
                })
                ->first()
            : null;

        return $sharedFld
            ? $sharedFld->field
            : ($curFld ? $curFld->field : '');
    }

    /**
     * @param TableRefCondition $refCondition
     * @return string
     */
    protected function masterFld(TableRefCondition $refCondition): string
    {
        $ddlRef = $this->findAppliedDdlref($refCondition);
        $mastFld = $ddlRef ? $ddlRef->_target_field : null;
        return $mastFld ? $mastFld->field : 'id';
    }

    /**
     * @param TableRefCondition $refCondition
     * @return DDLReference|null
     */
    protected function findAppliedDdlref(TableRefCondition $refCondition)
    {
        $ddlRef = $refCondition->_applied_ddlrefs
            ->filter(function ($ref) use ($refCondition) {
                return $ref->_applied_fields->where('table_id', $refCondition->table_id)->count();
            })
            ->first();

        return $ddlRef ?: $refCondition->_applied_ddlrefs->first();
    }

    /**
     * @param array $table_row
     * @param int $lvl
     * @return void
     */
    public function checkForDelete(array $table_row, int $lvl)
    {
        if ($this->checkLvl($lvl)) {
            foreach ($this->getInheritances($lvl) as $table_id => $group) {
                self::$processed_table_ids[] = $table_id;

                foreach ($this->getArrayOfRelatedIds($table_id, $group, $table_row) as $item) {
                    $refTb = $item['refTable'];
                    $related_rows = $item['relatedRows'];

                    if ($related_rows->count()) {
                        $this->td_service->deleteSelectedRows($refTb, $related_rows->toArray(), false, $lvl+1);
                    }
                }

            }
        }
    }

    /**
     * @param array $old_row
     * @param array $new_row
     * @param int $lvl
     * @return void
     * @throws Exception
     */
    public function checkForUpdate(array $old_row, array $new_row, int $lvl)
    {
        if ($this->checkLvl($lvl)) {
            foreach ($this->getInheritances($lvl) as $table_id => $group) {
                self::$processed_table_ids[] = $table_id;

                foreach ($this->getArrayOfRelatedRows($table_id, $group, $new_row, $old_row) as $item) {
                    $refTb = $item['refTable'];
                    $relations = $item['relations'];
                    $related_rows = $item['relatedRows'];

                    if ($related_rows->count()) {
                        foreach ($related_rows as $related_row) {
                            $rel_row_arr = $related_row->toArray();
                            foreach ($relations as $current => $master) {
                                $rel_row_arr[$current] = $new_row[$master];
                                if ($new_row['_changed_field'] == $master) {
                                    $rel_row_arr['_changed_field'] = $current;
                                }
                            }
                            $this->td_service->updateRow($refTb, $rel_row_arr['id'], $rel_row_arr, $refTb->user_id, [], $lvl+1);
                        }
                    }
                }

            }
        }
    }

    /**
     * @param int $table_id
     * @param Collection $refCondGroup
     * @param array $new_row
     * @param array $old_row
     * @return array
     */
    protected function getArrayOfRelatedRows(int $table_id, Collection $refCondGroup, array $new_row, array $old_row): array
    {
        $result = [];

        $relations = $this->fieldsRelations($refCondGroup);
        if (in_array($new_row['_changed_field'] ?? '', $relations)) {
            try {
                $refTb = $refCondGroup->first()->_table;
                $result[] = [
                    'refTable' => $refTb,
                    'relatedRows' => $this->applyRelations($refTb, $relations, $old_row),
                    'relations' => $relations,
                ];
            } catch (\Exception $e) {}
        }

        $fieldsWithSharedDDL = self::fieldsSharedDDLs($table_id);
        foreach ($fieldsWithSharedDDL->pluck('table_id')->unique() as $ref_table_id) {

            $relations = $this->fieldsRelations($refCondGroup, $ref_table_id);
            if (in_array($new_row['_changed_field'] ?? '', $relations)) {
                try {
                    $refTb = Table::find($ref_table_id);
                    $result[] = [
                        'refTable' => $refTb,
                        'relatedRows' => $this->applyRelations($refTb, $relations, $old_row),
                        'relations' => $relations,
                    ];
                } catch (\Exception $e) {}
            }

        }

        return $result;
    }

    /**
     * @param int $table_id
     * @param Collection $refCondGroup
     * @param array $table_row
     * @return array
     */
    protected function getArrayOfRelatedIds(int $table_id, Collection $refCondGroup, array $table_row): array
    {
        $result = [];

        $relations = $this->fieldsRelations($refCondGroup);
        if ($relations) {
            try {
                $refTb = $refCondGroup->first()->_table;
                $result[] = [
                    'refTable' => $refTb,
                    'relatedRows' => $this->applyRelations($refTb, $relations, $table_row, true),
                    'relations' => $relations,
                ];
            } catch (\Exception $e) {}
        }

        $fieldsWithSharedDDL = self::fieldsSharedDDLs($table_id);
        foreach ($fieldsWithSharedDDL->pluck('table_id')->unique() as $ref_table_id) {

            $relations = $this->fieldsRelations($refCondGroup, $ref_table_id);
            if ($relations) {
                try {
                    $refTb = Table::find($ref_table_id);
                    $result[] = [
                        'refTable' => $refTb,
                        'relatedRows' => $this->applyRelations($refTb, $relations, $table_row, true),
                        'relations' => $relations,
                    ];
                } catch (\Exception $e) {}
            }

        }

        return $result;
    }

    /**
     * @param Table $refTb
     * @param array $relations
     * @param array $row
     * @param bool $id
     * @return Collection
     */
    protected function applyRelations(Table $refTb, array $relations, array $row, bool $id = false): Collection
    {
        $sql = (new TableDataQuery($refTb))->getQuery();
        foreach ($relations as $current => $master) {
            $sql->where($current, '=', $row[$master]);
        }
        return $id
            ? $sql->select('id')->get()->pluck('id')
            : $sql->get();
    }

    /**
     * @param bool $skip
     * @return int
     */
    public function hasChildren(bool $skip = false): int
    {
        return ($skip || request('no_inheritance_ids') !== null)
            && self::queryChildrenForTable($this->table->id)->count();
    }
}
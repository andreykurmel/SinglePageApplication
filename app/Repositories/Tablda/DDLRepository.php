<?php

namespace Vanguard\Repositories\Tablda;


use Exception;
use Illuminate\Support\Collection;
use Ramsey\Uuid\Uuid;
use Vanguard\Helpers\ColorHelper;
use Vanguard\Ideas\StaticCacher;
use Vanguard\Jobs\FillMissedDdlRefColors;
use Vanguard\Models\DDL;
use Vanguard\Models\DDLItem;
use Vanguard\Models\DDLReference;
use Vanguard\Models\DDLReferenceColor;
use Vanguard\Models\Table\Table;
use Vanguard\Models\Table\TableField;
use Vanguard\Repositories\Tablda\Permissions\TableRefConditionRepository;
use Vanguard\Services\Tablda\HelperService;
use Vanguard\Singletones\AuthUserSingleton;

class DDLRepository
{
    use StaticCacher;

    protected $service;

    /**
     * TableRepository constructor.
     *
     */
    public function __construct()
    {
        $this->service = new HelperService();
    }

    /**
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function sharedDDLS()
    {
        $ddls = DDL::query()
            ->where('admin_public', '=', 1)
            ->orWhere(function ($query) {
                $query->where('created_by', '=', auth()->id())
                    ->where('owner_shared', '=', 1);
            })
            ->with(array_merge(['_table:id,name'], $this->ddlRelations()))
            ->get();

        $auth = app()->make(AuthUserSingleton::class);
        foreach ($ddls as $ddl) {
            $ddl->__url = $auth->getTableUrl($ddl->_table->id);
        }

        return $ddls;
    }

    /**
     * @param Table $table
     */
    public function loadForTable(Table $table)
    {
        $table->load([
            '_ddls' => function ($d) {
                $d->with($this->ddlRelations());
                $d->orderBy('row_order');
            },
        ]);
    }

    /**
     * @return array
     */
    protected function ddlRelations(): array
    {
        return [
            '_items',
            '_references' => function ($q) {
                $q->with(['_reference_clr_img']);
            },
        ];
    }

    /**
     * @param int $ddl_id
     * @return int
     */
    public function ddlIsIdShow(int $ddl_id)
    {
        return DDL::where('id', '=', $ddl_id)
            ->where(function ($inner) {
                $inner->whereHas('_references', function ($ref) {
                    $ref->whereNotNull('show_field_id');
                    $ref->whereRaw('(`show_field_id` != `target_field_id` OR `target_field_id` IS NULL)');
                });
                $inner->orWhereHas('_items', function ($ref) {
                    $ref->whereNotNull('show_option');
                    $ref->whereRaw('`show_option` != `option`');
                });
            })
            ->count();
    }

    /**
     * @param int $fld_id
     * @return Collection
     */
    public function findWatchingDDLs(int $fld_id)
    {
        return DDL::query()
            ->whereHas('_references', function ($ref) use ($fld_id) {
                $ref->where('target_field_id', '=', $fld_id);
            })
            ->with([
                '_applied_to_fields' => function ($ref) {
                    $ref->select(['id', 'field', 'ddl_id', 'table_id']);
                },
                '_references' => function ($ref) use ($fld_id) {
                    $ref->where('target_field_id', '=', $fld_id)
                        ->with('_ref_condition');
                },
            ])
            ->get();
    }

    /**
     * Get DDL with References.
     *
     * @param $ddl_id
     * @return DDL
     */
    public function getDDLwithRefs($ddl_id)
    {
        return DDL::where('id', '=', $ddl_id)->with(
            $this->ddlRelationsForGetValues()
        )->first();
    }

    /**
     * get array of relations to get values from DDL.
     *
     * @return array
     */
    public function ddlRelationsForGetValues()
    {
        $ref_cond_relations = (new TableRefConditionRepository())->refConditionRelations();
        return [
            '_references' => function ($q) use ($ref_cond_relations) {
                $q->with([
                    '_ref_condition' => function ($rc) use ($ref_cond_relations) {
                        $rc->with($ref_cond_relations);
                    },
                    '_reference_clr_img',
                    '_target_field',
                    '_image_field',
                    '_color_field',
                    '_show_field',
                    '_max_selections_field',
                ]);
            },
            '_items'
        ];
    }

    /**
     * @param int $ddl_id
     * @return null|DDL
     */
    public function getDDL(int $ddl_id)
    {
        return DDL::where('id', '=', $ddl_id)->first();
    }

    /**
     * @param string $name
     * @param int $table_id
     * @return DDL|null
     */
    public function getDDLbyName(string $name, int $table_id)
    {
        return DDL::where('name', '=', $name)
            ->where('table_id', '=', $table_id)
            ->first();
    }

    /**
     * @param array $ddl_ids
     * @return mixed
     */
    public function getDDLarray(array $ddl_ids)
    {
        return DDL::whereIn('id', $ddl_ids)->get();
    }

    /**
     * @param array $ddls_ids
     * @return array
     */
    public function getRelatedGroups(array $ddls_ids)
    {
        return array_merge(
            DDLReference::whereIn('ddl_id', $ddls_ids)->get()->pluck('apply_target_row_group_id')->toArray(),
            DDLItem::whereIn('ddl_id', $ddls_ids)->get()->pluck('apply_target_row_group_id')->toArray()
        );
    }

    /**
     * @param array $ddls_ids
     * @return array
     */
    public function getRelatedRefConds(array $ddls_ids)
    {
        return DDLReference::whereIn('ddl_id', $ddls_ids)
            ->get()
            ->pluck('table_ref_condition_id')
            ->toArray();
    }

    /**
     * Add DDL.
     *
     * @param $data
     * [
     *  +table_id: int,
     *  +name: string,
     *  +type: string,
     *  -notes: string,
     * ]
     * @return mixed
     */
    public function addDDL($data)
    {
        $data['type'] = $data['type'] ?? 'regular';
        $data['row_order'] = DDL::query()->max('id') + 1;
        return DDL::create(array_merge($this->service->delSystemFields($data), $this->service->getModified(), $this->service->getCreated()));
    }

    /**
     * Update DDL
     *
     * @param int $ddl_id
     * @param $data
     * [
     *  -table_id: int,
     *  -name: string,
     *  -type: string,
     *  -notes: string,
     * ]
     * @return array
     */
    public function updateDDL($ddl_id, $data)
    {
        $changedFld = $data['_changed_field'] ?? '';
        if ($changedFld == 'owner_shared' || $changedFld == 'admin_public') {
            DDLReference::query()
                ->where('ddl_id', '=', $ddl_id)
                ->whereHas('_ref_condition', function ($q) {
                    $q->where('rc_static', '!=', 1);
                })
                ->delete();
        }
        return DDL::where('id', $ddl_id)
            ->update(array_merge($this->service->delSystemFields($data), $this->service->getModified()));
    }

    /**
     * Delete DDL
     *
     * @param int $ddl_id
     * @return mixed
     */
    public function deleteDDL($ddl_id)
    {
        return DDL::where('id', $ddl_id)->delete();
    }

    /**
     * Fill DDL By Distinctive Values.
     *
     * @param $ddl_id
     * @param $values
     * @return mixed
     */
    public function fillDDL($ddl_id, $values)
    {
        $arr = [];
        foreach ($values as $v) {
            $arr[] = array_merge([
                'ddl_id' => $ddl_id,
                'option' => $v
            ], $this->service->getModified(), $this->service->getCreated());
        }
        DDLItem::insert($arr);
        return DDLItem::where('ddl_id', '=', $ddl_id)->get();
    }

    /**
     * Get DDLItem.
     *
     * @param $ddl_id
     * @return DDLItem
     */
    public function getDDLItem($ddl_id)
    {
        return DDLItem::where('id', '=', $ddl_id)->first();
    }

    /**
     * Add DDL Item.
     *
     * @param $data
     * [
     *  +ddl_id: int,
     *  +option: string,
     *  -notes: string,
     * ]
     * @return DDLItem
     */
    public function addDDLItem($data)
    {
        if ($data['option'] == null) {
            $data['option'] = '';
        }
        if (empty($data['opt_color']) && DDLItem::where('ddl_id', $data['ddl_id'])->whereNotNull('opt_color')->count()) {
            $data['opt_color'] = ColorHelper::randomHex();
        }
        $item = DDLItem::create(array_merge(
            $this->service->delSystemFields($data),
            $this->service->getModified(),
            $this->service->getCreated()
        ));
        $this->triggerFieldChangesIfNeeded($item->_ddl);

        return $item;
    }

    /**
     * Update DDL Item
     *
     * @param int $ddl_item_id
     * @param $data
     * [
     *  -ddl_id: int,
     *  -option: string,
     *  -notes: string,
     * ]
     * @return DDLItem
     */
    public function updateDDLItem($ddl_item_id, $data)
    {
        DDLItem::where('id', $ddl_item_id)
            ->update(array_merge($this->service->delSystemFields($data), $this->service->getModified()));

        $item = DDLItem::where('id', $ddl_item_id)->first();
        $this->triggerFieldChangesIfNeeded($item->_ddl);

        return $item;
    }

    /**
     * Delete DDL Item
     *
     * @param int $ddl_item_id
     * @return mixed
     */
    public function deleteDDLItem($ddl_item_id)
    {
        return DDLItem::where('id', $ddl_item_id)->delete();
    }

    /**
     * Get DDLReference.
     *
     * @param $ddl_id
     * @return DDLReference
     */
    public function getDDLReference($ddl_id)
    {
        return DDLReference::where('id', '=', $ddl_id)->first();
    }

    /**
     * @param array $ddl_ids
     * @return Collection
     */
    public function ddlReferencesWhichIds(array $ddl_ids)
    {
        [$references, $rest] = $this->getCache($ddl_ids);

        if ($rest) {
            $loaded = DDLReference::whereIn('ddl_id', $rest)
                ->with([
                    '_ref_condition' => function ($rc) {
                        $rc->with('_ref_table');
                    },
                    '_target_field:id,field,ddl_id',
                    '_image_field:id,field,ddl_id',
                    '_show_field:id,field,ddl_id',
                    '_color_field:id,field,ddl_id',
                    '_max_selections_field:id,field,ddl_id',
                    '_reference_clr_img',
                ])
                ->get();

            $this->setCache($loaded, 'ddl_id');
            $references = $references->merge($loaded);
        }

        return $references;
    }

    /**
     * @param $ddl_ids
     * @return Collection
     */
    public function ddlItemsWhichIds($ddl_ids)
    {
        return DDLItem::whereIn('ddl_id', $ddl_ids)
            //->isTbRef()
            ->get();
    }

    /**
     * Add DDL Reference.
     *
     * @param $data
     * [
     *  +ddl_id: int,
     *  +table_ref_condition_id: int,
     *  +target_field_id: int,
     *  -notes: string,
     * ]
     * @return DDLReference
     */
    public function addDDLReference($data)
    {
        $data = $this->service->delNullFields($data);
        $item = DDLReference::create(array_merge($this->service->delSystemFields($data), $this->service->getModified(), $this->service->getCreated()));
        $this->triggerFieldChangesIfNeeded($item->_ddl);
        return $item;
    }

    /**
     * Update DDL Reference
     *
     * @param int $ddl_reference_id
     * @param $data
     * [
     *  -ddl_id: int,
     *  -table_ref_condition_id: int,
     *  -target_field_id: int,
     *  -notes: string,
     * ]
     * @return DDLReference
     */
    public function updateDDLReference($ddl_reference_id, $data)
    {
        DDLReference::where('id', $ddl_reference_id)
            ->update(array_merge($this->service->delSystemFields($data), $this->service->getModified()));

        $item = DDLReference::where('id', $ddl_reference_id)->first();
        $this->triggerFieldChangesIfNeeded($item->_ddl);
        return $item;
    }

    /**
     * Delete DDL Reference
     *
     * @param int $ddl_reference_id
     * @return mixed
     */
    public function deleteDDLReference($ddl_reference_id)
    {
        return DDLReference::where('id', $ddl_reference_id)->delete();
    }

    /**
     * @param $ref_cond_id
     * @return mixed
     */
    public function syncRefCond($ref_cond_id)
    {
        return DDLReference::where('table_ref_condition_id', '=', $ref_cond_id)->update([
            'target_field_id' => null,
            'image_field_id' => null,
            'color_field_id' => null,
            'show_field_id' => null,
        ]);
    }

    /**
     * @param int $id
     * @return DDLReference
     */
    public function getDdlRef(int $id): DDLReference
    {
        return DDLReference::where('id', '=', $id)->first();
    }

    /**
     * @param int $ddl_ref_id
     * @param int $page
     * @return Collection
     */
    public function allRefColors(int $ddl_ref_id, int $page = 1): Collection
    {
        return DDLReferenceColor::where('ddl_reference_id', '=', $ddl_ref_id)
            ->offset(($page - 1) * 100)
            ->limit(100)
            ->get();
    }

    /**
     * @param int $ddl_ref_id
     * @param array $data
     * @return DDLReferenceColor
     */
    public function addDDLReferenceColor(int $ddl_ref_id, array $data): DDLReferenceColor
    {
        $data['ddl_reference_id'] = $ddl_ref_id;
        $data = $this->service->delSystemFields($data);
        if (!strlen($data['color'] ?? '')) {
            $data['color'] = '';
        }
        if (!strlen($data['ref_value'] ?? '')) {
            $data['ref_value'] = '';
        }
        return DDLReferenceColor::create($data);
    }

    /**
     * @param int $ref_color_id
     * @param array $data
     * @return DDLReferenceColor
     */
    public function updateDDLReferenceColor(int $ref_color_id, array $data): DDLReferenceColor
    {
        DDLReferenceColor::where('id', '=', $ref_color_id)
            ->update($this->service->delSystemFields($data));
        return $this->getDdlRefColor($ref_color_id);
    }

    /**
     * @param int $id
     * @return DDLReferenceColor
     */
    public function getDdlRefColor(int $id): DDLReferenceColor
    {
        return DDLReferenceColor::where('id', '=', $id)->first();
    }

    /**
     * @param int $ref_color_id
     * @return bool
     * @throws Exception
     */
    public function deleteDDLReferenceColor(int $ref_color_id): bool
    {
        return DDLReferenceColor::where('id', '=', $ref_color_id)->delete();
    }

    /**
     * @param DDLReference $reference
     * @param array $values
     * @param int $page
     * @return Collection
     */
    public function massInsertRefColors(DDLReference $reference, array $values, int $page = 1): Collection
    {
        $datas = [];
        foreach ($values as $idx => $val) {
            $datas[] = [
                'ddl_reference_id' => $reference->id,
                'ref_value' => $val['show'],
                'image_ref_path' => $reference->image_field_id ? ($val['image'] ?: null) : null,
                'color' => $reference->color_field_id ? ($val['color'] ?: '') : '',
                'max_selections' => $reference->max_selections_field_id ? ($val['max_selections'] ?: '') : '',
            ];
        }
        DDLReferenceColor::insert($datas);
        return $this->allRefColors($reference->id, $page);
    }

    /**
     * @param DDLReference $reference
     * @param string $behavior
     * @param int $page
     * @return Collection
     */
    public function massUpdateRefColors(DDLReference $reference, string $behavior, int $page = 1): Collection
    {
        $rand = rand(0, 0x101010);
        foreach ($this->allRefColors($reference->id, $page) as $idx => $refColor) {
            $brightness = floor($idx / 20) * $rand;
            DDLReferenceColor::where('id', '=', $refColor->id)->update([
                'color' => $behavior == 'auto' ? '#' . ColorHelper::autoHex($brightness) : null,
            ]);
        }

        if ($reference->get_ref_clr_img_count() > 100) {
            dispatch(new FillMissedDdlRefColors($reference->id, $behavior != 'auto'));
        }

        return $this->allRefColors($reference->id, $page);
    }

    /**
     * @param DDL $ddl
     * @param string $behavior
     * @return Collection
     */
    public function massUpdateItemColors(DDL $ddl, string $behavior = 'auto'): Collection
    {
        foreach ($ddl->_items as $item) {
            DDLItem::where('id', '=', $item->id)->update([
                "opt_color" => $behavior == 'auto' ? ColorHelper::randomHex() : null,
            ]);
        }
        return $ddl->_items()->get();
    }

    /**
     * @param DDLReference $reference
     * @return bool
     * @throws Exception
     */
    public function removeAllRefColors(DDLReference $reference): bool
    {
        return DDLReferenceColor::where('ddl_reference_id', '=', $reference->id)->delete();
    }

    /**
     * @param DDL $ddl
     * @return void
     * @throws Exception
     */
    protected function triggerFieldChangesIfNeeded(DDL $ddl): void
    {
        //
    }

    /**
     * @param Table $table
     * @param mixed $name
     * @param array $options
     * @param bool $isIgnored
     * @return void
     */
    public function autoDdlCreation(Table $table, mixed $name, array $options, bool $isIgnored = false): void
    {
        if (! $isIgnored || ! $table->_ddls->where('name', '=', $name)->count()) {
            $ddl = $this->addDDL([
                'table_id' => $table->id,
                'name' => $name,
            ]);
            foreach ($options as $option => $show) {
                if ($option && $show) {
                    $this->addDDLItem([
                        'ddl_id' => $ddl->id,
                        'option' => $option,
                        'show_option' => $show,
                    ]);
                }
            }
        }
    }
}
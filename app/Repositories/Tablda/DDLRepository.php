<?php

namespace Vanguard\Repositories\Tablda;


use Illuminate\Database\Eloquent\Collection;
use Vanguard\Ideas\StaticCacher;
use Vanguard\Models\DDL;
use Vanguard\Models\DDLItem;
use Vanguard\Models\DDLReference;
use Vanguard\Repositories\Tablda\Permissions\TableRefConditionRepository;
use Vanguard\Services\Tablda\HelperService;
use Vanguard\User;

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
     * Get DDL with References.
     *
     * @param $ddl_id
     * @return DDL
     */
    public function getDDLwithRefs($ddl_id) {
        return DDL::where('id', '=', $ddl_id)->with(
            $this->ddlRelationsForGetValues()
        )->first();
    }

    /**
     * get array of relations to get values from DDL.
     *
     * @return array
     */
    public function ddlRelationsForGetValues() {
        $ref_cond_relations = (new TableRefConditionRepository())->refConditionRelations();
        return [
            '_references' => function($q) use ($ref_cond_relations) {
                $q->with([
                    '_ref_condition' => function($rc) use ($ref_cond_relations) {
                        $rc->with( $ref_cond_relations );
                    },
                    '_target_field',
                    '_image_field',
                    '_show_field',
                ]);
            },
            '_items'
        ];
    }

    /**
     * Get DDL.
     *
     * @param $ddl_id
     * @return DDL
     */
    public function getDDL($ddl_id) {
        return DDL::where('id', '=', $ddl_id)->first();
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
        return DDL::create( array_merge($this->service->delSystemFields($data), $this->service->getModified(), $this->service->getCreated()) );;
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
        return DDL::where('id', $ddl_id)
            ->update( array_merge($this->service->delSystemFields($data), $this->service->getModified()) );
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
    public function getDDLItem($ddl_id) {
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
     * @return mixed
     */
    public function addDDLItem($data)
    {
        if ($data['option'] == null) {
            $data['option'] = '';
        }
        return DDLItem::create( array_merge($this->service->delSystemFields($data), $this->service->getModified(), $this->service->getCreated()) );
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
     * @return array
     */
    public function updateDDLItem($ddl_item_id, $data)
    {
        return DDLItem::where('id', $ddl_item_id)
            ->update( array_merge($this->service->delSystemFields($data), $this->service->getModified()) );
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
    public function getDDLReference($ddl_id) {
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
                ->isTbRef()
                ->with([
                    '_ref_condition' => function($rc) {
                        $rc->with('_ref_table');
                    },
                    '_target_field:id,field,ddl_id',
                    '_image_field:id,field,ddl_id',
                    '_show_field:id,field,ddl_id',
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
    public function ddlItemsWhichIds($ddl_ids) {
        return DDLItem::whereIn('ddl_id', $ddl_ids)
            ->isTbRef()
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
     * @return mixed
     */
    public function addDDLReference($data)
    {
        $data = $this->service->delNullFields($data);
        return DDLReference::create( array_merge($this->service->delSystemFields($data), $this->service->getModified(), $this->service->getCreated()) );
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
     * @return array
     */
    public function updateDDLReference($ddl_reference_id, $data)
    {
        return DDLReference::where('id', $ddl_reference_id)
            ->update( array_merge($this->service->delSystemFields($data), $this->service->getModified()) );
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
            'show_field_id' => null,
        ]);
    }
}
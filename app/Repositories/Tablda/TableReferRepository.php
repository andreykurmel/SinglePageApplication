<?php

namespace Vanguard\Repositories\Tablda;


use Vanguard\Models\Table\TableReference;
use Vanguard\Models\Table\TableReferenceCorr;
use Vanguard\Services\Tablda\HelperService;

class TableReferRepository
{
    protected $service;

    /**
     * TableRepository constructor.
     */
    public function __construct()
    {
        $this->service = new HelperService();
    }

    /**
     * @param int $refer_id
     * @param bool $rels
     * @return mixed
     */
    public function getRefer(int $refer_id, bool $rels = false)
    {
        $sql = TableReference::where('id', '=', $refer_id);
        if ($rels) {
            $sql->with([
                '_reference_corrs' => function ($q) {
                    $q->with(['_ref_field', '_field']);
                }
            ]);
        }
        return $sql->first();
    }

    /**
     * @param int $table_id
     * @return mixed
     */
    public function allRefers(int $table_id)
    {
        return TableReference::where('table_id', '=', $table_id)
            ->with('_reference_corrs')
            ->get();
    }

    /**
     * Add Refer row to store Table
     *
     * @param array $data
     * @return mixed
     */
    public function addRefer(Array $data)
    {
        return TableReference::create($this->service->delSystemFields($data));
    }

    /**
     * @param $table_id
     * @param $refer_id
     * @param array $data
     * @return mixed
     */
    public function updateRefer($table_id, $refer_id, array $data)
    {
        return TableReference::where('table_id', '=', $table_id)
            ->where('id', '=', $refer_id)
            ->update($this->service->delSystemFields($data));
    }

    /**
     * @param $table_id
     * @param $refer_id
     * @return mixed
     */
    public function deleteRefer($table_id, $refer_id)
    {
        return TableReference::where('table_id', '=', $table_id)
            ->where('id', '=', $refer_id)
            ->delete();
    }

    /**
     * @param array $data
     * @return mixed
     */
    public function addReferCorr(Array $data)
    {
        return TableReferenceCorr::create($this->service->delSystemFields($data));
    }

    /**
     * @param $refer_id
     * @param $corr_id
     * @param array $data
     * @return mixed
     */
    public function updateReferCorr($refer_id, $corr_id, Array $data)
    {
        return TableReferenceCorr::where('import_ref_id', '=', $refer_id)
            ->where('id', '=', $corr_id)
            ->update($this->service->delSystemFields($data));
    }

    /**
     * @param $refer_id
     * @param $corr_id
     * @return mixed
     */
    public function deleteReferCorr($refer_id, $corr_id)
    {
        return TableReferenceCorr::where('import_ref_id', '=', $refer_id)
            ->where('id', '=', $corr_id)
            ->delete();
    }
}
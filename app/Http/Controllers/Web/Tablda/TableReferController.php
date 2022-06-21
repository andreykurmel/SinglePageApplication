<?php

namespace Vanguard\Http\Controllers\Web\Tablda;


use Illuminate\Http\Request;
use Vanguard\Http\Controllers\Controller;
use Vanguard\Models\Table\TableData;
use Vanguard\Repositories\Tablda\TableReferRepository;
use Vanguard\Repositories\Tablda\TableRepository;

class TableReferController extends Controller
{
    protected $tbRepo;
    protected $referRepo;

    /**
     * TableReferController constructor.
     */
    public function __construct() {
        $this->tbRepo = new TableRepository();
        $this->referRepo = new TableReferRepository();
    }

    /**
     * @param Request $request
     * @return mixed
     */
    public function insert(Request $request)
    {
        $table = $this->tbRepo->getTable($request->table_id);
        $this->authorize('isOwner', [TableData::class, $table]);
        $this->referRepo->addRefer(array_merge($request->fields, ['table_id' => $table->id]));
        return $this->referRepo->allRefers($table->id);
    }

    /**
     * @param Request $request
     * @return mixed
     */
    public function update(Request $request)
    {
        $table = $this->tbRepo->getTable($request->table_id);
        $this->authorize('isOwner', [TableData::class, $table]);
        $this->referRepo->updateRefer( $request->table_id, $request->refer_id, array_merge($request->fields, ['table_id' => $table->id]) );
        return $this->referRepo->allRefers($table->id);
    }

    /**
     * @param Request $request
     * @return mixed
     */
    public function delete(Request $request)
    {
        $table = $this->tbRepo->getTable($request->table_id);
        $this->authorize('isOwner', [TableData::class, $table]);
        $this->referRepo->deleteRefer($request->table_id, $request->refer_id);
        return $this->referRepo->allRefers($table->id);
    }

    /**
     * @param Request $request
     * @return mixed
     */
    public function insertCorrs(Request $request)
    {
        $refer = $this->referRepo->getRefer($request->refer_id);
        $this->authorize('isOwner', [TableData::class, $refer->_table]);
        $this->referRepo->addReferCorr(array_merge($request->fields, ['import_ref_id' => $refer->id]));
        return $this->referRepo->allRefers($refer->_table->id);
    }

    /**
     * @param Request $request
     * @return mixed
     */
    public function updateCorrs(Request $request)
    {
        $refer = $this->referRepo->getRefer($request->refer_id);
        $this->authorize('isOwner', [TableData::class, $refer->_table]);
        $this->referRepo->updateReferCorr( $request->refer_id, $request->corr_id, array_merge($request->fields, ['import_ref_id' => $refer->id]) );
        return $this->referRepo->allRefers($refer->_table->id);
    }

    /**
     * @param Request $request
     * @return array
     */
    public function deleteCorrs(Request $request)
    {
        $refer = $this->referRepo->getRefer($request->refer_id);
        $this->authorize('isOwner', [TableData::class, $refer->_table]);
        $this->referRepo->deleteReferCorr($request->refer_id, $request->corr_id);
        return $this->referRepo->allRefers($refer->_table->id);
    }
}

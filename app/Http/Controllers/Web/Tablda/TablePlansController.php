<?php

namespace Vanguard\Http\Controllers\Web\Tablda;


use Illuminate\Support\Facades\Request;
use Vanguard\Http\Controllers\Controller;
use Vanguard\Repositories\Tablda\PlanRepository;
use Vanguard\Services\Tablda\TableService;

class TablePlansController extends Controller
{
    private $tableService;
    private $PlanRepository;

    /**
     * TablePlansController constructor.
     * 
     * @param TableService $tableService
     * @param PlanRepository $PlanRepository
     */
    public function __construct(TableService $tableService, PlanRepository $PlanRepository)
    {
        $this->tableService = $tableService;
        $this->PlanRepository = $PlanRepository;
    }

    /**
     * Add Plan
     *
     * @param Request $request
     * @return mixed
     */
    public function addPlan(Request $request){
        if (auth()->user() && auth()->user()->isAdmin()) {
            return $this->PlanRepository->addPlan($request->fields);
        } else {
            abort(403);
        }
    }

    /**
     * Update Plan
     *
     * @param Request $request
     * @return array
     */
    public function updatePlan(Request $request){
        if (auth()->user() && auth()->user()->isAdmin()) {
            return $this->PlanRepository->updatePlan($request->id, $request->fields);
        } else {
            abort(403);
        }
    }

    /**
     * Add Addon
     *
     * @param Request $request
     * @return mixed
     */
    public function addAddon(Request $request){
        if (auth()->user() && auth()->user()->isAdmin()) {
            return $this->PlanRepository->addAddon($request->fields);
        } else {
            abort(403);
        }
    }

    /**
     * Update Addon
     *
     * @param Request $request
     * @return array
     */
    public function updateAddon(Request $request){
        if (auth()->user() && auth()->user()->isAdmin()) {
            return $this->PlanRepository->updateAddon($request->id, $request->fields);
        } else {
            abort(403);
        }
    }
}

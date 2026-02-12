<?php

namespace Vanguard\Http\Controllers\Web\Tablda;


use Illuminate\Http\Request;
use Vanguard\Http\Controllers\Controller;
use Vanguard\Repositories\Tablda\PlanRepository;
use Vanguard\Services\Tablda\TableService;

class TablePlansController extends Controller
{
    private $planRepository;

    /**
     * TablePlansController constructor.
     */
    public function __construct()
    {
        $this->planRepository = new PlanRepository();
    }

    /**
     * Add Plan
     *
     * @param Request $request
     * @return mixed
     */
    public function addPlan(Request $request){
        if (auth()->user() && auth()->user()->isAdmin()) {
            return $this->planRepository->addPlan($request->fields);
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
            return $this->planRepository->updatePlan($request->id, $request->fields);
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
            return $this->planRepository->addAddon($request->fields);
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
            return $this->planRepository->updateAddon($request->id, $request->fields);
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
    public function renameAddon(Request $request){
        if (auth()->user() && (auth()->user()->isAdmin() || auth()->user()->role_id == 3)) {
            return [ 'ok' => $this->planRepository->renameAddon($request->id, $request->name, $request->description ?: '') ];
        } else {
            abort(403);
        }
    }
}

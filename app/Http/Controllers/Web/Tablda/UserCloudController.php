<?php

namespace Vanguard\Http\Controllers\Web\Tablda;


use Illuminate\Http\Request;
use Vanguard\Http\Controllers\Controller;
use Vanguard\Repositories\Tablda\UserCloudRepository;
use Vanguard\Services\Tablda\BladeVariablesService;

class UserCloudController extends Controller
{
    private $cloudRepository;
    private $variablesService;

    /**
     * UserCloudController constructor.
     * @param BladeVariablesService $variablesService
     */
    public function __construct(BladeVariablesService $variablesService)
    {
        $this->cloudRepository = new UserCloudRepository();
        $this->variablesService = $variablesService;
    }

    /**
     * Add DDL
     *
     * @param Request $request
     * @return mixed
     */
    public function insert(Request $request)
    {
        return $this->cloudRepository->addUserCloud(array_merge($request->fields, ['user_id' => auth()->id()]));
    }

    /**
     * Update DDL.
     *
     * @param Request $request
     * @return array
     */
    public function update(Request $request)
    {
        $cloud = $this->cloudRepository->getCloud($request->user_cloud_id);
        return [
            'status' => $cloud && $cloud->user_id == auth()->id()
                ? $this->cloudRepository->updateUserCloud($request->user_cloud_id, array_merge($request->fields, ['user_id' => auth()->id()]))
                : false
        ];
    }

    /**
     * Delete DDL
     *
     * @param Request $request
     * @return mixed
     */
    public function delete(Request $request)
    {
        $cloud = $this->cloudRepository->getCloud($request->user_cloud_id);
        return [
            'status' => $cloud && $cloud->user_id == auth()->id()
                ? $this->cloudRepository->deleteUserCloud($request->user_cloud_id)
                : false
        ];
    }

    /**
     * Activate
     *
     * @param Request $request
     * @return mixed
     */
    public function activate(Request $request)
    {
        $state = json_decode($request->state, true);
        if ($request->code && !empty($state['cloud_id'])) {
            if ($this->cloudRepository->setCloudToken($state['cloud_id'], $request->code)) {
                $msg = 'Successfully Connected!';
            }
        }
        return view('tablda.cloud-activation', array_merge(
            $this->variablesService->getVariables(),
            [
                'msg' => $msg ?? 'Incorrect request!',
                'lightweight' => true,
                'embed' => true,
            ]
        ));
    }

    /**
     * set inactive
     *
     * @param Request $request
     * @return mixed
     */
    public function setInactive(Request $request)
    {
        $cloud = $this->cloudRepository->getCloud($request->user_cloud_id);
        if ($cloud && $cloud->user_id == auth()->id()) {
            $this->cloudRepository->setInactiveCloud($cloud);
        }
        return $cloud;
    }
}

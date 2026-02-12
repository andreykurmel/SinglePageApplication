<?php

namespace Vanguard\Http\Controllers\Web\Tablda\Applications;

use Illuminate\Http\Request;
use Vanguard\Http\Controllers\Controller;
use Vanguard\Models\Correspondences\CorrespApp;
use Vanguard\Services\Tablda\BladeVariablesService;
use Vanguard\Services\Tablda\HelperService;

class AllController extends Controller
{
    protected $service;
    protected $bladeVariablesService;
    protected $controller_namespace = 'Vanguard\\Http\\Controllers\\Web\\Tablda\\Applications\\';

    public function __construct(BladeVariablesService $bladeVariablesService)
    {
        $this->bladeVariablesService = $bladeVariablesService;
        $this->bladeVariablesService->is_app_route = 1;
        $this->service = new HelperService();
    }

    /**
     * Get request Handler for Applications.
     *
     * @param Request $request
     * @return \Illuminate\Contracts\Routing\ResponseFactory|\Illuminate\Contracts\View\Factory|\Illuminate\Http\Response|\Illuminate\View\View
     */
    public function index(Request $request)
    {
        return $this->parseMethod('get', '', $request);
    }

    /**
     * List of all available Apps.
     *
     * @param Request $request
     * @return \Illuminate\Contracts\Routing\ResponseFactory|\Illuminate\Contracts\View\Factory|\Illuminate\Http\Response|\Illuminate\View\View
     */
    public function listApps(Request $request)
    {
        if ($this->service->cur_subdomain == 'apps') {
            $subs_ids = auth()->user() ? auth()->user()->_subscribed_apps->pluck('app_id') : [];

            return view('tablda.applications.list-tablda-apps', array_merge(
                $this->bladeVariablesService->getVariables(),
                [
                    'all_apps' => CorrespApp::onlyPublicActive()
                        ->get()
                        ->groupBy('subdomain')
                        ->values(),
                    'subs_ids' => $subs_ids,
                ]
            ));
        } else {
            return redirect( route('data') );
        }
    }

    /**
     * View with user's Apps
     *
     * @param Request $request
     * @return \Illuminate\Contracts\Routing\ResponseFactory|\Illuminate\Contracts\View\Factory|\Illuminate\Http\Response|\Illuminate\View\View
     */
    public function myApps(Request $request)
    {
        $subs_ids = auth()->user() ? auth()->user()->_subscribed_apps->pluck('app_id') : [];

        return view('tablda.applications.my-apps', array_merge(
            $this->bladeVariablesService->getVariables(),
            [
                'my_apps' => CorrespApp::onlyActive()
                    ->where('user_id', auth()->id())
                    ->get(),
                'subscribed_apps' => CorrespApp::onlyActive()
                    ->whereIn('id', $subs_ids)
                    ->get()
                    ->groupBy('subdomain')
                    ->values(),
                'subs_ids' => $subs_ids,
            ]
        ));
    }

    /**
     * Subscribe to App
     *
     * @param Request $request
     * @return \Illuminate\Contracts\Routing\ResponseFactory|\Illuminate\Contracts\View\Factory|\Illuminate\Http\Response|\Illuminate\View\View
     */
    public function appToggle(Request $request)
    {
        $app_id = (int)$request->app_id;
        if (auth()->id() && $app_id) {
            //remove
            auth()->user()->_subscribed_apps()->where('app_id', $app_id)->delete();
            //add if needed
            if ($request->status) {
                auth()->user()->_subscribed_apps()->insert([
                    'user_id' => auth()->id(),
                    'app_id' => $app_id,
                ]);
            }
        }
    }

    /**
     * Get request Handler for Applications.
     *
     * @param $apppath
     * @param Request $request
     * @return \Illuminate\Contracts\Routing\ResponseFactory|\Illuminate\Contracts\View\Factory|\Illuminate\Http\Response|\Illuminate\View\View
     */
    protected function parseMethod($method, $apppath, Request $request)
    {
        try {
            $app = $this->findApplication($apppath);
        } catch (\Exception $e) {
            return response('Incorrect Application Path '.$apppath, 400);
        }

        //For all request types
        if ($app->type == 'local' && $app->controller) {
            $controller = app()->make($this->controller_namespace . $app->controller);
            return $controller->{$method}($request, $app);
        }

        //views only for 'GET' requests.
        if ($app->type == 'iframe' && $method == 'get') {
            $query = $request->getQueryString();
            return view('tablda.applications.iframe-app', array_merge(
                $this->bladeVariablesService->getVariables(),
                [
                    'iframe_path' => $app->_ajax_path . ($query ? '?'.$query : ''),
                ]
            ));
        }

        return response('Incorrect Application Settings in CorrespondenceTables.', 400);
    }

    /**
     * @param $path
     * @return mixed
     * @throws \Exception
     */
    protected function findApplication($path)
    {
        $first = $path[0] ?? '';
        $path = $first != '/' ? ('/' . $path) : $path;
        $app = CorrespApp::onlyActive()
            ->where(function ($in) {
                $in->where('subdomain', $this->service->cur_subdomain);
                $in->orWhere(function ($in2) {
                    $in2->where('user_id', 1);
                    $in2->where('is_public', 1);
                });
            })
            ->whereRaw('\'' . $path . '\' like CONCAT(`app_path` ,\'%\')')
            ->first();

        if (!$app) {
            throw new \Exception('Application not found ('.$path.')');
        }

        $app->_ajax_path = preg_replace('#^' . $app->app_path . '#i', $this->service->hidden_tablda_app, $path)
            . '/' . $app->iframe_app_path;
        $app->_ajax_path = preg_replace('#/+#i', '/', $app->_ajax_path);
        return $app;
    }

    /**
     * Get request Handler for Applications.
     *
     * @param $apppath
     * @param Request $request
     * @return \Illuminate\Contracts\Routing\ResponseFactory|\Illuminate\Contracts\View\Factory|\Illuminate\Http\Response|\Illuminate\View\View
     */
    public function get($apppath, Request $request)
    {
        return $this->parseMethod('get', $apppath, $request);
    }

    /**
     * Post request Handler for Applications.
     *
     * @param $apppath
     * @param Request $request
     * @return \Illuminate\Contracts\Routing\ResponseFactory|\Illuminate\Http\Response
     */
    public function post($apppath, Request $request)
    {
        return $this->parseMethod('post', $apppath, $request);
    }

    /**
     * Put request Handler for Applications.
     *
     * @param $apppath
     * @param Request $request
     * @return \Illuminate\Contracts\Routing\ResponseFactory|\Illuminate\Http\Response
     */
    public function put($apppath, Request $request)
    {
        return $this->parseMethod('put', $apppath, $request);
    }

    /**
     * Delete request Handler for Applications.
     *
     * @param $apppath
     * @param Request $request
     * @return \Illuminate\Contracts\Routing\ResponseFactory|\Illuminate\Http\Response
     */
    public function delete($apppath, Request $request)
    {
        return $this->parseMethod('delete', $apppath, $request);
    }

}

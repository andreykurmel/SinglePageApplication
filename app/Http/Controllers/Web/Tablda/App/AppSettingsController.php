<?php

namespace Vanguard\Http\Controllers\Web\Tablda\App;


use Illuminate\Http\Request;
use Vanguard\Http\Controllers\Controller;
use Vanguard\Http\Requests\Tablda\App\AppSettEditRequest;
use Vanguard\Services\Tablda\App\AppSettingsService;

class AppSettingsController extends Controller
{
    private $appSettingsService;

    /**
     * DDLController constructor.
     * @param AppSettingsService $appSettingsService
     */
    public function __construct(AppSettingsService $appSettingsService)
    {
        $this->appSettingsService = $appSettingsService;
    }

    /**
     * Update AppSetting.
     *
     * @param AppSettEditRequest $request
     * @return array
     */
    public function updateAppSett(AppSettEditRequest $request)
    {
        return [
            'status' => $this->appSettingsService->updateAppSetting($request->app_key, $request->app_val)
        ];
    }
}

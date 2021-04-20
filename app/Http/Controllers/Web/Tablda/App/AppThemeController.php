<?php

namespace Vanguard\Http\Controllers\Web\Tablda\App;


use Illuminate\Http\Request;
use Vanguard\Http\Controllers\Controller;
use Vanguard\Http\Requests\Tablda\App\AppThemeEditRequest;
use Vanguard\Models\AppTheme;
use Vanguard\Services\Tablda\App\AppThemeService;

class AppThemeController extends Controller
{
    private $appThemeService;

    /**
     * DDLController constructor.
     * @param AppThemeService $appThemeService
     */
    public function __construct(AppThemeService $appThemeService)
    {
        $this->appThemeService = $appThemeService;
    }

    /**
     * Update AppSetting.
     *
     * @param AppThemeEditRequest $request
     * @return array
     */
    public function updateTheme(AppThemeEditRequest $request)
    {
        $appTheme = $this->appThemeService->getAppTheme($request->theme_id);

        $this->authorize('edit', [AppTheme::class, $appTheme]);

        return [
            'status' => $this->appThemeService->updateAppTheme($request->theme_id, $request->fields)
        ];
    }
}

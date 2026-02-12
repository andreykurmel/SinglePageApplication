<?php

namespace Vanguard\Repositories\Tablda\App;


use Vanguard\Models\AppSetting;
use Vanguard\Models\FormulaHelper;
use Vanguard\Services\Tablda\HelperService;
use Vanguard\User;

class AppSettingsRepository
{
    protected $service;

    /**
     * TableRepository constructor.
     *
     * @param HelperService $service
     */
    public function __construct(HelperService $service)
    {
        $this->service = $service;
    }

    /**
     * Get AppSetting.
     *
     * @param $app_key
     * @return AppSetting
     */
    public function getAppSetting($app_key) {
        return AppSetting::where('key', '=', $app_key)->first();
    }

    /**
     * Add AppSetting.
     *
     * @param $app_key
     * @param $app_val
     * @return mixed
     */
    public function addAppSetting($app_key, $app_val)
    {
        return AppSetting::create( [
            'key' => $app_key,
            'val' => $app_val
        ] );
    }

    /**
     * Update AppSetting
     *
     * @param $app_key
     * @param $app_val
     * @return mixed
     */
    public function updateAppSetting($app_key, $app_val)
    {
        return AppSetting::where('key', $app_key)
            ->update( ['val' => $app_val] );
    }

    /**
     * Delete AppSetting
     *
     * @param int $app_sett_id
     * @return mixed
     */
    public function deleteAppSetting($app_sett_id)
    {
        return AppSetting::where('id', $app_sett_id)->delete();
    }

    /**
     * Update FormulaHelper
     *
     * @param $app_key
     * @param $app_val
     * @return mixed
     */
    public function updateFormulaHelper($app_key, $app_val)
    {
        return FormulaHelper::where('formula', $app_key)
            ->update( ['notes' => $app_val] );
    }
}
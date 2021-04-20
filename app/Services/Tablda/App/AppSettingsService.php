<?php


namespace Vanguard\Services\Tablda\App;


use Vanguard\Repositories\Tablda\App\AppSettingsRepository;
use Vanguard\User;

class AppSettingsService
{
    protected $appSettingsRepository;

    /**
     * AppSettingsService constructor.
     * @param AppSettingsRepository $appSettingsRepository
     */
    public function __construct(AppSettingsRepository $appSettingsRepository)
    {
        $this->appSettingsRepository = $appSettingsRepository;
    }

    /**
     * Update AppSetting
     *
     * @param $app_key
     * @param $app_val
     * @return array
     */
    public function updateAppSetting($app_key, $app_val)
    {
        if ($this->appSettingsRepository->getAppSetting($app_key)) {
            return $this->appSettingsRepository->updateAppSetting($app_key, $app_val);
        } else {
            return $this->appSettingsRepository->addAppSetting($app_key, $app_val);
        }
    }
}
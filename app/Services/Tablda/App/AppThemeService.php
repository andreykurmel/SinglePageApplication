<?php


namespace Vanguard\Services\Tablda\App;


use Vanguard\Repositories\Tablda\App\AppThemeRepository;
use Vanguard\User;

class AppThemeService
{
    protected $appThemeRepository;

    /**
     * AppThemeService constructor.
     * @param AppThemeRepository $appThemeRepository
     */
    public function __construct(AppThemeRepository $appThemeRepository)
    {
        $this->appThemeRepository = $appThemeRepository;
    }

    /**
     * Get AppTheme.
     *
     * @param $app_sett_id
     * @return mixed
     */
    public function getAppTheme($app_sett_id) {
        return $this->appThemeRepository->getAppTheme($app_sett_id);
    }

    /**
     * Add AppTheme.
     *
     * @param $data
     * [
     *  +obj_type: string, // ['system','user','table']
     *  -obj_id: string,
     *  -navbar_bg_color: string,
     *  -ribbon_bg_color: string,
     *  -button_bg_color: string,
     *  -main_bg_color: string,
     *  -table_hdr_bg_color: string,
     *  -obj_id: string,
     * ]
     * @return mixed
     */
    public function addAppTheme($data)
    {
        $app_sett = $this->appThemeRepository->addAppTheme( $data );
        return $app_sett;
    }

    /**
     * Update AppTheme
     *
     * @param int $app_sett_id
     * @param $data
     * [
     *  +obj_type: string, // ['system','user','table']
     *  -obj_id: string,
     *  -navbar_bg_color: string,
     *  -ribbon_bg_color: string,
     *  -button_bg_color: string,
     *  -main_bg_color: string,
     *  -table_hdr_bg_color: string,
     *  -obj_id: string,
     * ]
     * @return array
     */
    public function updateAppTheme($app_sett_id, $data)
    {
        return $this->appThemeRepository->updateAppTheme($app_sett_id, $data);
    }

    /**
     * Delete AppTheme
     *
     * @param int $app_sett_id
     * @return mixed
     */
    public function deleteAppTheme($app_sett_id)
    {
        return $this->appThemeRepository->deleteAppTheme($app_sett_id);
    }
}
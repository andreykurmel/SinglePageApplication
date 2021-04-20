<?php

namespace Vanguard\Repositories\Tablda\App;


use Vanguard\Models\AppTheme;
use Vanguard\Services\Tablda\HelperService;
use Vanguard\User;

class AppThemeRepository
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
     * Get AppTheme.
     *
     * @param $app_sett_id
     * @return AppTheme
     */
    public function getAppTheme($app_sett_id) {
        return AppTheme::where('id', '=', $app_sett_id)->first();
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
        return AppTheme::create( $this->service->delSystemFields($data) );
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
        return AppTheme::where('id', $app_sett_id)
            ->update( $this->service->delSystemFields($data) );
    }

    /**
     * Delete AppTheme
     *
     * @param int $app_sett_id
     * @return mixed
     */
    public function deleteAppTheme($app_sett_id)
    {
        return AppTheme::where('id', $app_sett_id)->delete();
    }
}
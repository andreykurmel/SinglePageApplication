<?php

namespace Vanguard\TabldaApps;


use Tablda\DataReceiver\TabldaDataInterface;
use Vanguard\Modules\Settinger;

class Risa3dDeleter
{
    //NOTE: 'code' in 'correspondence_apps' must be 'risa3d_deleter'

    protected $mg_name = '';

    protected $errors = [];

    /**
     * @param string $mg_name
     * @return array
     */
    public function remove(string $mg_name)
    {
        $settings = Settinger::get('risa3d_deleter');
        $interface = app(TabldaDataInterface::class, ['settings' => $settings]);
        $appDatas = $interface->appDatas();

        foreach ($appDatas['_tables'] as $tbObject) {
            if ($tbObject->app_table != 'CALLING_URL_PARAMETERS') {
                $sql = $interface->tableReceiver($tbObject->app_table);
                try {
                    $sql->where('MG_NAME', '=', $mg_name)
                        ->delete();
                } catch (\Exception $e) {
                }
            }
        }

        return $this->errors;
    }
}
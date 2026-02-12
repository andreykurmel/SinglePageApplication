<?php

namespace Vanguard\Support;

use Vanguard\Models\AppSetting;

class FrontNotificator
{
    /**
     * @param int $table_id
     * @param string $message
     */
    public static function sendFromJob(int $table_id, string $message): void
    {
        AppSetting::updateOrCreate(['val' => $message], ['key' => '_sys_front_message_tb_'.$table_id]);
    }

    /**
     * @param int $table_id
     * @return string
     * @throws \Exception
     */
    public static function checkJob(int $table_id): string
    {
        $msg = AppSetting::where('key', '=', '_sys_front_message_tb_'.$table_id)->first();
        if ($msg) {
            AppSetting::where('key', '=', '_sys_front_message_tb_'.$table_id)->delete();
            return $msg->val;
        }
        return '';
    }
}
<?php

namespace Vanguard\Modules;


class Settinger
{
    public static function get(string $app_name)
    {
        return [
            'TABLDA_APP_NAME' => $app_name,
            'TABLDA_SYS_CONN' => config('app.tablda.sys_conn'),
            'TABLDA_DATA_CONN' => config('app.tablda.data_conn'),
            'TABLDA_APPS_TB' => config('app.tablda.apps_tb'),
            'TABLDA_TABLES_TB' => config('app.tablda.tables_tb'),
            'TABLDA_FIELDS_TB' => config('app.tablda.fields_tb'),
            'DEF_HOST' => config('app.tablda.def_host'),
            'DEF_DB' => config('app.tablda.def_database'),
            'DEF_LOGIN' => config('app.tablda.def_username'),
            'DEF_PASS' => config('app.tablda.def_password'),
        ];
    }
}
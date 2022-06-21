<?php

namespace Vanguard\Repositories\Tablda\TableData;


use Vanguard\Models\Table\Table;

class SqlFieldHelper
{

    /**
     * @param string $field
     * @param Table|null $table
     * @return string
     */
    public static function getSqlFld(Table $table, string $field = 'id')
    {
        $db_name = $table->db_name;
        if ($db_name == 'sum_usages') {
            $db_name = 'tables';
        }
        elseif ($db_name == 'fees') {
            $db_name = 'plans';
        }
        elseif ($db_name == 'table_fields__for_tooltips') {
            $db_name = 'table_fields';
        }
        elseif ($db_name == 'correspondence_tables' && in_array($field, ['name'])) {
            $db_name = 'correspondence_apps';
        }
        elseif ($db_name == 'correspondence_fields') {
            if (in_array($field, ['name'])) {
                $db_name = 'correspondence_apps';
            }
            if (in_array($field, ['app_table', 'data_table'])) {
                $db_name = 'correspondence_tables';
            }
        }
        elseif ($db_name == 'user_subscriptions' && in_array($field, ['renew', 'recurrent_pay', 'avail_credit'])) {
            $db_name = 'users';
        }
        return $db_name . '.' . $field;
    }
}
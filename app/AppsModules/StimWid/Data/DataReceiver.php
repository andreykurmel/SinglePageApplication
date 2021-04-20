<?php

namespace Vanguard\AppsModules\StimWid\Data;


use Illuminate\Support\Collection;
use Vanguard\Models\Table\Table;
use Vanguard\Modules\Settinger;
use Vanguard\Repositories\Tablda\TableRepository;

class DataReceiver
{
    /**
     * @var TabldaReceiver
     */
    protected static $tablda_data_interface;

    /**
     * Set Tablda Data interface
     */
    protected static function data_interface()
    {
        $settings = Settinger::get('stim_3d');
        self::$tablda_data_interface = new TabldaReceiver($settings);
    }

    /**
     * @param string $table_name
     * @return TableConverter
     */
    public static function mapped_query(string $table_name)
    {
        if (!self::$tablda_data_interface) {
            self::data_interface();
        }
        return self::$tablda_data_interface->tableReceiver($table_name);
    }

    /**
     * @return array
     */
    public static function app_datas()
    {
        if (!self::$tablda_data_interface) {
            self::data_interface();
        }
        return self::$tablda_data_interface->appDatas();
    }

    /**
     * @param string $app_table
     * @return array
     */
    public static function app_table_and_fields(string $app_table)
    {
        if (!self::$tablda_data_interface) {
            self::data_interface();
        }
        return self::$tablda_data_interface->getTableWithMaps($app_table);
    }

    /**
     * @param string $app_table
     * @return Table
     */
    public static function meta_table(string $app_table)
    {
        $table_info = self::app_table_and_fields($app_table);
        return (new TableRepository())->getTableByDB($table_info['data_table']);
    }

    /**
     * @param string $app_table
     * @param array $master_row
     * @return array
     */
    public static function build_search_array(string $app_table, array $master_row)
    {
        $search = '';
        $columns = [];

        $table_info = self::app_table_and_fields($app_table);
        $links = self::get_link_fields($table_info['_app_fields']);
        try {
            if (count($links)) {
                $arr = [];
                foreach ($links as $link) {
                    $columns[] = $link->data_field;
                    $arr[] = $master_row[$link->link_field_db];
                }
                $search = '"' . implode('" AND "', $arr) . '"';
            }
        } catch (\Exception $e) {
            $search = '';
            $columns = [];
        }

        return [$search, $columns];
    }

    /**
     * @param Collection $app_fields
     * @return Collection
     */
    public static function get_link_fields(Collection $app_fields)
    {
        return $app_fields->filter(function ($el) {
                return $el->link_field_db;
            })
            ->values();
    }

    /**
     * @param Collection $app_fields
     * @return Collection
     */
    public static function get_notes_fields(Collection $app_fields)
    {
        return $app_fields->filter(function ($el) {
                return $el->notes && $el->notes[0] == ':';
            })
            ->values();
    }

    /**
     * @param Collection $app_fields
     * @return Collection
     */
    public static function get_master_from_notes(Collection $app_fields)
    {
        return $app_fields->filter(function ($el) {
                return $el->notes && $el->notes[0] == '@';
            })
            ->values();
    }
}
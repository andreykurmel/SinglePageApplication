<?php

namespace Vanguard\AppsModules\StimWid;


use Illuminate\Http\Request;
use Vanguard\AppsModules\StimWid\Data\DataReceiver;
use Vanguard\AppsModules\StimWid\Data\UserPermisQuery;

class Model3dService
{
    /**
     * @var string
     */
    protected $app_table;

    /**
     * @var Data\TableConverter
     */
    protected $receiver;

    /**
     * Model3dService constructor.
     * @param string $app_table
     * @param bool $apply_user
     */
    function __construct(string $app_table, $apply_user = false)
    {
        $this->app_table = $app_table;
        $this->receiver = DataReceiver::mapped_query($app_table);
        if ($apply_user) {
            $this->receiver->apply_user_where();
        }
        $this->receiver->not_systems();
    }

    /**
     * @param array $front_filters
     * @return Data\TableConverter
     */
    protected function applyFilters(array $front_filters)
    {
        foreach ($front_filters as $fl_tb => $filters) {
            if ($filters && strtolower($this->app_table) == strtolower($fl_tb)) {
                $this->receiver->apply_filters($filters);
            }
        }
        return $this->receiver;
    }

    /**
     * @param array $model_row
     * @param array $front_filters
     * @return Data\TableConverter
     */
    public function queryFindModel(array $model_row, array $front_filters = [])
    {
        $table_info = DataReceiver::app_table_and_fields($this->app_table);
        $links = DataReceiver::get_link_fields($table_info['_app_fields']);
        foreach ($links as $link) {
            $this->receiver->where($link->app_field, '=', $model_row[$link->link_field_db] ?? '');
        }

        $notes = DataReceiver::get_notes_fields($table_info['_app_fields']);
        foreach ($notes as $note) {
            $this->receiver->where($note->app_field, '=', substr($note->notes, 1));
        }

        $this->applyFilters($front_filters);

        return $this->receiver;
    }

    /**
     * @param string $usergroup
     * @param string $model
     * @param string $curtab
     * @return Data\TableConverter
     */
    public function findModelAndUser(string $usergroup, string $model, string $curtab = '')
    {
        $this->receiver->where('usergroup', '=', $usergroup);
        $this->receiver->where('model', '=', $model);
        if ($curtab) {
            $this->receiver->where('curtab', '=', $curtab);
        }
        return $this->receiver;
    }

    /**
     * @param string $ma_user
     * @param string $ma_model
     * @param array $front_filters
     * @return Data\TableConverter
     */
    public function queryFindMaster(string $ma_user, string $ma_model, array $front_filters = [])
    {
        $table_info = DataReceiver::app_table_and_fields($this->app_table);
        $notes = DataReceiver::get_master_from_notes($table_info['_app_fields']);

        foreach ($notes as $note) {
            switch ($note->notes) {
                case '@usergroup': $this->receiver->where($note->app_field, '=', $ma_user); break;
                case '@model': $this->receiver->where($note->app_field, '=', $ma_model); break;
            }
        }

        $this->applyFilters($front_filters);

        return $this->receiver;
    }

    /**
     * @return Data\TableConverter
     */
    public function queryReceiver()
    {
        return $this->receiver;
    }

    /**
     * @param int $id
     * @return array|null
     */
    public function getById(int $id)
    {
        $this->receiver->where('_id', '=', $id);
        return $this->receiver->first();
    }

    /**
     * @param string $hash
     * @return array|null
     */
    public function getByHash(string $hash)
    {
        $this->receiver->where('_row_hash', '=', $hash);
        return $this->receiver->first();
    }

    /**
     * @return array
     */
    public function getMaps()
    {
        return $this->receiver->getMaps();
    }
}
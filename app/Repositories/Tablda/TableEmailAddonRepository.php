<?php

namespace Vanguard\Repositories\Tablda;

use Vanguard\Models\Table\Table;
use Vanguard\Models\Table\TableEmailAddonSetting;
use Vanguard\Services\Tablda\HelperService;

class TableEmailAddonRepository
{
    protected $service;

    /**
     * TableAlertRepository constructor.
     */
    public function __construct()
    {
        $this->service = new HelperService();
    }

    /**
     * @param $table_alert_id
     * @return TableEmailAddonSetting
     */
    public function getEmailSett($table_alert_id)
    {
        return TableEmailAddonSetting::where('id', '=', $table_alert_id)->first();
    }

    /**
     * @param Table $table
     */
    public function loadForTable(Table $table)
    {
        $table->load('_email_addon_settings');
        if (!$table->_email_addon_settings) {
            $this->insertEmailSett(['table_id' => $table->id]);
            $table->load('_email_addon_settings');
        }
    }

    /**
     * @param array $data
     * @return mixed
     */
    public function insertEmailSett(Array $data)
    {
        $data = $this->dataDef($data);
        return TableEmailAddonSetting::create($this->service->delSystemFields($data));
    }

    /**
     * @param array $data
     * @return array
     */
    protected function dataDef(array $data)
    {
        $data['server_type'] = $data['server_type'] ?? 'google';
        $data['smtp_key_mode'] = $data['smtp_key_mode'] ?? 'account';
        return $data;
    }

    /**
     * @param $alert_id
     * @param array $data
     * @return mixed
     */
    public function updateEmailSett($alert_id, Array $data)
    {
        $data = $this->dataDef($data);
        return TableEmailAddonSetting::where('id', '=', $alert_id)
            ->update($this->service->delSystemFields($data));
    }

    /**
     * @param $table_alert_id
     * @return mixed
     */
    public function deleteEmailSett($table_alert_id)
    {
        return TableEmailAddonSetting::where('id', '=', $table_alert_id)
            ->delete();
    }
}
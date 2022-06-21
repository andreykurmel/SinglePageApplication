<?php

namespace Vanguard\Repositories\Tablda;

use Carbon\Carbon;
use Vanguard\Mail\TabldaMail;
use Vanguard\Models\Table\Table;
use Vanguard\Models\Table\TableEmailAddonHistory;
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
     * @return TableEmailAddonSetting
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
        $data['name'] = $data['name'] ?? 'Template';
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

    /**
     * @param int $from_add_id
     * @param int $to_add_id
     * @return TableEmailAddonSetting
     */
    public function copyAdn(int $from_add_id, int $to_add_id)
    {
        $from = $this->getEmailSett($from_add_id);
        $to = $this->getEmailSett($to_add_id);
        $to->update(array_merge(
            $from->toArray(),
            ['name' => $to->name, 'description' => $to->description, ]
        ));
        return $to;
    }

    /**
     * @param int $addon_id
     * @param int $row_id
     * @param TabldaMail $mailable
     * @return TableEmailAddonHistory
     */
    public function insertEmailHistory(int $addon_id, int $row_id, TabldaMail $mailable): TableEmailAddonHistory
    {
        $arr = $mailable->for_preview(true);
        return TableEmailAddonHistory::create([
            'table_email_addon_id' => $addon_id,
            'row_id' => $row_id,
            'send_date' => Carbon::now(),
            'preview_from' => $arr['from'],
            'preview_to' => $arr['to'],
            'preview_cc' => $arr['cc'],
            'preview_bcc' => $arr['bcc'],
            'preview_reply' => $arr['reply'],
            'preview_subject' => $arr['subject'],
            'preview_body' => $arr['body'],
            'preview_tablda_row' => $arr['row_tablda'],
        ]);
    }

    /**
     * @param TableEmailAddonSetting $email
     * @param int|null $row_id
     * @return mixed
     */
    public function clearHistory(TableEmailAddonSetting $email, int $row_id = null)
    {
        if ($row_id) {
            return $email->_history_emails()->where('row_id', '=', $row_id)->delete();
        } else {
            return $email->_history_emails()->delete();
        }
    }
}
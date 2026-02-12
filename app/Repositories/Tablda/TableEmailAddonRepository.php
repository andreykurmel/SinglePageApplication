<?php

namespace Vanguard\Repositories\Tablda;

use Carbon\Carbon;
use Vanguard\Mail\TabldaMail;
use Vanguard\Models\Table\Table;
use Vanguard\Models\Table\TableEmailAddonHistory;
use Vanguard\Models\Table\TableEmailAddonSetting;
use Vanguard\Models\Table\TableEmailRight;
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
    public function loadForTable(Table $table, int $user_id = null)
    {
        if (!$table->_email_addon_settings->count()) {
            $this->insertEmailSett(['table_id' => $table->id]);
        }
        $table->load([
            '_email_addon_settings' => function ($s) use ($table, $user_id) {
                $vPermisId = $this->service->viewPermissionId($table);
                if ($table->user_id != $user_id && $vPermisId != -1) {
                    //get only 'shared' tableCharts for regular User.
                    $s->whereHas('_table_permissions', function ($tp) use ($vPermisId) {
                        $tp->applyIsActiveForUserOrPermission($vPermisId);
                    });
                }
                $s->with('_email_rights');
            }
        ]);
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
     * @param int|null $history_id
     * @return mixed
     */
    public function clearHistory(TableEmailAddonSetting $email, int $row_id = null, int $history_id = null)
    {
        if ($row_id) {
            return $email->_history_emails()->where('row_id', '=', $row_id)->delete();
        } elseif ($history_id) {
            return $email->_history_emails()->where('id', '=', $history_id)->delete();
        } else {
            return $email->_history_emails()->delete();
        }
    }

    /**
     * @param TableEmailAddonSetting $email
     * @param int $table_permis_id
     * @param $can_edit
     * @return TableEmailRight
     */
    public function toggleEmailRight(TableEmailAddonSetting $email, int $table_permis_id, $can_edit)
    {
        $right = $email->_email_rights()
            ->where('table_permission_id', $table_permis_id)
            ->first();

        if (!$right) {
            $right = TableEmailRight::create([
                'table_email_id' => $email->id,
                'table_permission_id' => $table_permis_id,
                'can_edit' => $can_edit,
            ]);
        } else {
            $right->update([
                'can_edit' => $can_edit
            ]);
        }

        return $right;
    }

    /**
     * @param TableEmailAddonSetting $email
     * @param int $table_permis_id
     * @return mixed
     */
    public function deleteEmailRight(TableEmailAddonSetting $email, int $table_permis_id)
    {
        return $email->_email_rights()
            ->where('table_permission_id', $table_permis_id)
            ->delete();
    }
}
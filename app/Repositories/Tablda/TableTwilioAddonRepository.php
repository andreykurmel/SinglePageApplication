<?php

namespace Vanguard\Repositories\Tablda;

use Carbon\Carbon;
use Vanguard\Models\Table\Table;
use Vanguard\Models\Table\TableEmailAddonSetting;
use Vanguard\Models\Table\TableEmailRight;
use Vanguard\Models\Table\TableTwilioAddonHistory;
use Vanguard\Models\Table\TableTwilioAddonSetting;
use Vanguard\Models\Table\TableTwilioRight;
use Vanguard\Modules\Twilio\TwilioSmsObject;
use Vanguard\Services\Tablda\HelperService;

class TableTwilioAddonRepository
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
     * @param Table $table
     */
    public function loadForTable(Table $table, int $user_id = null)
    {
        if (!$table->_twilio_addon_settings->count()) {
            $this->insertTwilioSett(['table_id' => $table->id]);
        }
        $table->load([
            '_twilio_addon_settings' => function ($s) use ($table, $user_id) {
                $vPermisId = $this->service->viewPermissionId($table);
                if ($table->user_id != $user_id && $vPermisId != -1) {
                    //get only 'shared' tableCharts for regular User.
                    $s->whereHas('_table_permissions', function ($tp) use ($vPermisId) {
                        $tp->applyIsActiveForUserOrPermission($vPermisId);
                    });
                }
                $s->with('_twilio_rights');
            }
        ]);
    }

    /**
     * @param array $data
     * @return TableTwilioAddonSetting
     */
    public function insertTwilioSett(array $data)
    {
        $data = $this->dataDef($data);
        return TableTwilioAddonSetting::create($this->service->delSystemFields($data));
    }

    /**
     * @param array $data
     * @return array
     */
    protected function dataDef(array $data)
    {
        $data['name'] = $data['name'] ?? 'Template';
        return $data;
    }

    /**
     * @param $alert_id
     * @param array $data
     * @return mixed
     */
    public function updateTwilioSett($alert_id, array $data)
    {
        $data = $this->dataDef($data);
        return TableTwilioAddonSetting::where('id', '=', $alert_id)
            ->update($this->service->delSystemFields($data));
    }

    /**
     * @param $twilio_id
     * @return bool|int|null
     * @throws \Exception
     */
    public function deleteTwilioSett($twilio_id)
    {
        return TableTwilioAddonSetting::where('id', '=', $twilio_id)
            ->delete();
    }

    /**
     * @param int $from_add_id
     * @param int $to_add_id
     * @return TableTwilioAddonSetting
     */
    public function copyAdn(int $from_add_id, int $to_add_id)
    {
        $from = $this->getTwilioSett($from_add_id);
        $to = $this->getTwilioSett($to_add_id);
        $to->update(array_merge(
            $from->toArray(),
            ['name' => $to->name, 'description' => $to->description,]
        ));
        return $to;
    }

    /**
     * @param $twilio_id
     * @return TableTwilioAddonSetting
     */
    public function getTwilioSett($twilio_id)
    {
        return TableTwilioAddonSetting::where('id', '=', $twilio_id)->first();
    }

    /**
     * @param int $addon_id
     * @param int $row_id
     * @param TwilioSmsObject $sms
     * @return TableTwilioAddonHistory
     */
    public function insertEmailHistory(int $addon_id, int $row_id, TwilioSmsObject $sms): TableTwilioAddonHistory
    {
        $arr = $sms->preview(true);
        return TableTwilioAddonHistory::create([
            'table_twilio_addon_id' => $addon_id,
            'row_id' => $row_id,
            'msg_type' => 'sms',
            'send_date' => Carbon::now(),
            'preview_from' => $arr['preview_from'],
            'preview_to' => $arr['preview_to'],
            'preview_body' => $arr['preview_body'],
            'preview_row' => $arr['preview_row'],
        ]);
    }

    /**
     * @param TableTwilioAddonSetting $twilio
     * @param int|null $row_id
     * @return mixed
     */
    public function clearHistory(TableTwilioAddonSetting $twilio, int $row_id = null)
    {
        if ($row_id) {
            return $twilio->_history_sms()->where('id', '=', $row_id)->delete();
        } else {
            return $twilio->_history_sms()->delete();
        }
    }

    /**
     * @param TableTwilioAddonSetting $twilio
     * @param int $table_permis_id
     * @param $can_edit
     * @return TableTwilioRight
     */
    public function toggleTwilioRight(TableTwilioAddonSetting $twilio, int $table_permis_id, $can_edit)
    {
        $right = $twilio->_twilio_rights()
            ->where('table_permission_id', $table_permis_id)
            ->first();

        if (!$right) {
            $right = TableTwilioRight::create([
                'table_twilio_id' => $twilio->id,
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
     * @param TableTwilioAddonSetting $twilio
     * @param int $table_permis_id
     * @return mixed
     */
    public function deleteTwilioRight(TableTwilioAddonSetting $twilio, int $table_permis_id)
    {
        return $twilio->_twilio_rights()
            ->where('table_permission_id', $table_permis_id)
            ->delete();
    }
}
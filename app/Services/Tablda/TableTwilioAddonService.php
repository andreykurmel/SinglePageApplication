<?php

namespace Vanguard\Services\Tablda;

use Carbon\Carbon;
use Exception;
use Twilio\Exceptions\ConfigurationException;
use Vanguard\Jobs\DelayTwilioAddon;
use Vanguard\Models\Table\Table;
use Vanguard\Models\Table\TableTwilioAddonHistory;
use Vanguard\Models\Table\TableTwilioAddonSetting;
use Vanguard\Models\Table\TableTwilioRight;
use Vanguard\Modules\Twilio\TwilioSmsObject;
use Vanguard\Repositories\Tablda\TableData\FormulaEvaluatorRepository;
use Vanguard\Repositories\Tablda\TableData\TableDataRepository;
use Vanguard\Repositories\Tablda\TableTwilioAddonRepository;

class TableTwilioAddonService
{
    protected $service;
    protected $addonRepo;

    /**
     * TableTwilioAddonService constructor.
     */
    public function __construct()
    {
        $this->service = new HelperService();
        $this->addonRepo = new TableTwilioAddonRepository();
    }

    /**
     * @param TableTwilioAddonSetting $twilio
     * @param int|null $row_id
     * @param string $special
     * @return array
     * @throws ConfigurationException
     */
    public function previewTwilio(TableTwilioAddonSetting $twilio, int $row_id = null, string $special = ''): array
    {
        $all_rows = $this->getRowsArray($twilio->_table, $twilio->limit_row_group_id, $row_id);
        $previews = [];
        foreach ($all_rows as $i => $row) {
            if (
                ($special && $i == 0) //init -> preview first
                ||
                (!$special && (!$row_id || $row_id == $row['id'])) //preview selected
            ) {
                $sms = $this->getTwilioSmsObject($twilio, $row);
                if ($sms) {
                    $previews[$row['id']] = $sms->preview();
                    $previews[$row['id']]['history'] = $twilio->_history_sms
                        ->where('row_id', '=', $row['id'])
                        ->sortBy('send_date', SORT_REGULAR, true)
                        ->map(function (TableTwilioAddonHistory $item) {
                            return $item->decodeArrays();
                        })
                        ->values();
                }
            }
        }
        return [
            'all_rows' => $all_rows,
            'previews' => $previews,
        ];
    }

    /**
     * @param Table $table
     * @param int|null $row_group_id
     * @param int|null $single_id
     * @return array
     */
    protected function getRowsArray(Table $table, int $row_group_id = null, int $single_id = null): array
    {
        return (new TableDataRepository())
            ->getRowsByRowGroup($table, $row_group_id, $single_id)
            ->toArray();
    }

    /**
     * @param TableTwilioAddonSetting $twilio
     * @param array $row
     * @return TwilioSmsObject
     * @throws ConfigurationException
     */
    public function getTwilioSmsObject(TableTwilioAddonSetting $twilio, array $row): TwilioSmsObject
    {
        return new TwilioSmsObject($twilio, $row);
    }

    /**
     * @param $table_twilio_id
     * @return TableTwilioAddonSetting
     */
    public function getTwilioSett($table_twilio_id)
    {
        return $this->addonRepo->getTwilioSett($table_twilio_id);
    }

    /**
     * @param Table $table
     */
    public function loadForTable(Table $table, int $user_id = null)
    {
        $this->addonRepo->loadForTable($table, $user_id);
    }

    /**
     * @param array $data
     * @return TableTwilioAddonSetting
     */
    public function insertTwilioSett(array $data)
    {
        return $this->addonRepo->insertTwilioSett($data);
    }

    /**
     * @param $twilio_id
     * @param array $data
     * @return mixed
     */
    public function updateTwilioSett($twilio_id, array $data)
    {
        return $this->addonRepo->updateTwilioSett($twilio_id, $data);
    }

    /**
     * @param $table_twilio_id
     * @return mixed
     */
    public function deleteTwilioSett($table_twilio_id)
    {
        return $this->addonRepo->deleteTwilioSett($table_twilio_id);
    }

    /**
     * @param int $from_add_id
     * @param int $to_add_id
     * @return TableTwilioAddonSetting
     */
    public function copyAdn(int $from_add_id, int $to_add_id)
    {
        return $this->addonRepo->copyAdn($from_add_id, $to_add_id);
    }

    /**
     * @param TableTwilioAddonSetting $twilio
     * @param int|null $row_id
     * @return array
     */
    public function sendTwilios(TableTwilioAddonSetting $twilio, int $row_id = null): array
    {
        $msg = $twilio->notInProgressSms()
            ? 'Icorrect twilio settings or empty rows!'
            : 'Twilios are in sending process!';
        if ($twilio->notInProgressSms()) {
            $all_rows = $this->getRowsArray($twilio->_table, $twilio->limit_row_group_id, $row_id);
            foreach ($all_rows as $row) {
                $this->twilioJob($twilio, $row);
            }
            if ($all_rows) {
                $twilio->startPreparedSms(count($all_rows), !!$row_id);
                $msg = count($all_rows) . ' sms are added to query!';
            }
        }
        return [
            'result' => $msg,
            'prepared_sms' => $twilio->prepared_sms,
            'sent_sms' => $twilio->sent_sms,
        ];
    }

    /**
     * @param TableTwilioAddonSetting $twilio
     * @param array $row
     */
    protected function twilioJob(TableTwilioAddonSetting $twilio, array $row)
    {
        $queued = DelayTwilioAddon::dispatch($twilio, $row);
        if ($twilio->sms_send_time != 'now') {
            try {
                $interval = $twilio->sms_send_time == 'field_specific'
                    ? ($twilio->_sms_delay_record_field ? $row[$twilio->_sms_delay_record_field->field] ?? '' : '')
                    : $twilio->sms_delay_time;

                if (Carbon::make($interval) > Carbon::now()) {
                    $seconds = Carbon::now()->diffInSeconds($interval ?: '');
                    $queued->delay(Carbon::now()->addSeconds($seconds));
                }
            } catch (Exception $e) {
            }
        }
    }

    /**
     * @param TableTwilioAddonSetting $twilio
     * @return array
     */
    public function cancelTwilio(TableTwilioAddonSetting $twilio): array
    {
        $twilio->cancelQueueSms();
        return [
            'prepared_emails' => $twilio->prepared_emails,
            'sent_emails' => $twilio->sent_emails,
        ];
    }

    /**
     * @param TableTwilioAddonSetting $twilio
     * @param int|null $row_id
     * @return array
     */
    public function clearHistory(TableTwilioAddonSetting $twilio, int $row_id = null): array
    {
        return [
            'status' => $this->addonRepo->clearHistory($twilio, $row_id)
        ];
    }

    /**
     * @param Table $table
     * @param int $row_id
     * @param string $message
     * @return string
     */
    public function replaceSmsBody(Table $table, int $row_id, string $message): string
    {
        $row = (new TableDataRepository())->getDirectRow($table, $row_id)->toArray();
        $parser = new FormulaEvaluatorRepository($table, $table->user_id, true);
        return $parser->formulaReplaceVars($row, $message);
    }

    /**
     * @param TableTwilioAddonSetting $twilio
     * @param int $table_permis_id
     * @param $can_edit
     * @return TableTwilioRight
     */
    public function toggleTwilioRight(TableTwilioAddonSetting $twilio, int $table_permis_id, $can_edit)
    {
        return $this->addonRepo->toggleTwilioRight($twilio, $table_permis_id, $can_edit);
    }

    /**
     * @param TableTwilioAddonSetting $twilio
     * @param int $table_permis_id
     * @return mixed
     */
    public function deleteTwilioRight(TableTwilioAddonSetting $twilio, int $table_permis_id)
    {
        return $this->addonRepo->deleteTwilioRight($twilio, $table_permis_id);
    }
}
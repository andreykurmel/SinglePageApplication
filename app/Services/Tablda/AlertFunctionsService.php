<?php

namespace Vanguard\Services\Tablda;


use Carbon\Carbon;
use Illuminate\Support\Arr;
use Ramsey\Uuid\Uuid;
use Vanguard\Jobs\AlertAutomationAddJob;
use Vanguard\Jobs\DelayAlertJob;
use Vanguard\Mail\EmailWithSettings;
use Vanguard\Models\Table\Table;
use Vanguard\Models\Table\TableAlert;
use Vanguard\Modules\Twilio\TwilioApi;
use Vanguard\Repositories\Tablda\AutomationHistoryRepository;
use Vanguard\Repositories\Tablda\TableAlertsRepository;
use Vanguard\Repositories\Tablda\TableData\FormulaEvaluatorRepository;
use Vanguard\Repositories\Tablda\TableData\TableDataQuery;
use Vanguard\Repositories\Tablda\TableData\TableDataRepository;
use Vanguard\Repositories\Tablda\UserConnRepository;
use Vanguard\User;

class AlertFunctionsService
{
    protected $alertsRepository;
    protected $service;
    protected $maxIndividual;

    /**
     * TableAlertService constructor.
     */
    public function __construct()
    {
        $this->alertsRepository = new TableAlertsRepository();
        $this->service = new HelperService();
        $this->maxIndividual = 10;
    }

    /**
     * @param TableAlert $alert
     * @param array $active_rows
     * @param array $recipients
     * @param array $changed_fields
     * @param string $type
     */
    public function sendSms(TableAlert $alert, array $active_rows, array $recipients, array $changed_fields, string $type)
    {
        if (!$alert->sms_delay_hour && !$alert->sms_delay_min && !$alert->sms_delay_sec) {
            $this->sendSmsNotificationjob($alert->id, $active_rows, $recipients); // no Delay
        } else {
            $delayTime = Carbon::now()
                ->addHours($alert->sms_delay_hour)
                ->addMinutes($alert->sms_delay_min)
                ->addSeconds($alert->sms_delay_sec);
            DelayAlertJob::dispatch($alert->id, $active_rows, $recipients, $changed_fields, $type, 'sms')->delay( $delayTime );
        }
    }

    /**
     * @param TableAlert $alert
     * @param array $active_rows
     * @param array $recipients
     * @param array $changed_fields
     * @param string $type
     */
    public function sendEmail(TableAlert $alert, array $active_rows, array $recipients, array $changed_fields, string $type)
    {
        if (!$alert->mail_delay_hour && !$alert->mail_delay_min && !$alert->mail_delay_sec) {
            $this->sendEmailNotificationjob($alert->id, $active_rows, $recipients, $changed_fields, $type); // no Delay
        } else {
            $delayTime = Carbon::now()
                ->addHours($alert->mail_delay_hour)
                ->addMinutes($alert->mail_delay_min)
                ->addSeconds($alert->mail_delay_sec);
            DelayAlertJob::dispatch($alert->id, $active_rows, $recipients, $changed_fields, $type, 'email')->delay( $delayTime );
        }
    }


    /**
     * @param int $alert_id
     * @param array $active_rows
     * @param array $recipients
     * @return void
     * @throws \Twilio\Exceptions\ConfigurationException
     */
    public function sendSmsNotificationjob(int $alert_id, array $active_rows, array $recipients)
    {
        $alert = $this->alertsRepository->justAlert($alert_id);
        $alert->load(['_table']);

        $automationHistory = new AutomationHistoryRepository($alert->user_id, $alert->table_id);
        $automationHistory->startTimer();

        $formula_parser = new FormulaEvaluatorRepository($alert->_table, $alert->_table->user_id, true);

        $user_api = (new UserConnRepository())->defaultTwilioAcc($alert->_table->user_id);
        $api = new TwilioApi($user_api->id);

        foreach ($active_rows as $row) {
            foreach ($recipients as $phone) {
                $msg = $formula_parser->formulaReplaceVars($row, $alert->sms_body);
                $api->sendSMS($phone, $msg);
            }
        }

        $automationHistory->stopTimerAndSave('ANA', $alert->name, 'Notification', 'SMS');
    }


    /**
     * @param int $alert_id
     * @param array $active_rows
     * @param array $recipients
     * @param array $changed_fields
     * @param string $type
     * @return void
     */
    public function sendEmailNotificationjob(int $alert_id, array $active_rows, array $recipients, array $changed_fields, string $type)
    {
        $alert = $this->alertsRepository->justAlert($alert_id);
        $alert->load(['_table', '_col_group']);

        $automationHistory = new AutomationHistoryRepository($alert->user_id, $alert->table_id);
        $automationHistory->startTimer();

        $active_rows = $this->prepareActives($alert->_table, $active_rows);

        $datas = $this->mailDataWithAnaLink($alert, $active_rows, $type);

        $fields_arr = $alert->notif_email_add_tabledata
            ? $this->service->getFieldsArrayForNotification($alert->_table, $alert->_col_group)
            : [];
        $params = [
            'from.name' => config('app.name').' ANA',
            'from.account' => 'noreply',
            'subject' => $datas['subject'],
            'to.address' => $recipients['to'],
        ];
        $data = [
            'greeting' => $datas['greeting'],
            'replace_main_message' => $datas['message'],
            'table_arr' => $alert->_table->getAttributes(),
            'fields_arr' => $fields_arr,
            'all_rows_arr' => $active_rows,
            'has_unit' => $this->service->fldsArrHasUnit($fields_arr),
            'changed_fields' => $changed_fields,
            'alert_arr' => $alert->getAttributes(),
            'type' => $type,
        ];

        $mailer = new EmailWithSettings('alert_notification', $recipients['to'], $recipients['cc'] ?? [], $recipients['bcc'] ?? []);
        $mailer->send($params, $data);

        $automationHistory->stopTimerAndSave('ANA', $alert->name, 'Notification', 'Email');
    }

    /**
     * @param Table $table
     * @param array $active_rows
     * @return array
     */
    protected function prepareActives(Table $table, array $active_rows): array
    {
        if (in_array($table->db_name, ['user_activity', 'user_subscriptions'])) {
            foreach ($active_rows as &$row) {
                $user = User::find($row['user_id']);
                $row['user_id'] = $user->full_name() . ' (' . $user->email . ')';
                if ($table->db_name == 'user_activity') {
                    $timezone = $user->timezone ?: 'UTC';
                    $row['description_time'] = Carbon::createFromTimestamp($row['description_time'])->timezone($timezone)->toDateTimeString() . ' (' . $timezone . ')';
                    $row['ending_time'] = Carbon::createFromTimestamp($row['ending_time'])->timezone($timezone)->toDateTimeString() . ' (' . $timezone . ')';
                }
            }
        }
        return $active_rows;
    }

    /**
     * @param TableAlert $alert
     * @param array $active_rows
     * @param string $type
     * @return array
     */
    protected function mailDataWithAnaLink(TableAlert $alert, array $active_rows, string $type): array
    {
        $row = Arr::first($active_rows) ?: [];
        $formula_parser = new FormulaEvaluatorRepository($alert->_table, $alert->_table->user_id, true);
        $_h_fields = $alert->_table->_fields()->joinOwnerHeader()->get();

        $greeting = $alert->mail_addressee ? $formula_parser->formulaReplaceVars($row, $alert->mail_addressee) : '';
        $subject = $alert->mail_subject ? $formula_parser->formulaReplaceVars($row, $alert->mail_subject) : $this->getSubject($type, $_h_fields, $row, $alert);
        $message = $alert->mail_message ? $formula_parser->formulaReplaceVars($row, $alert->mail_message) : '';

        if ($alert->notif_email_add_clicklink) {
            $intro = $alert->click_introduction ?: 'Click the following link to update (confirm, etc.):';
            $link = config('app.url') . '/ana_click?link=' . Uuid::uuid4() . '_' . $alert->id . '_' . $row['id'] . '_' . Uuid::uuid4();
            $message .= ($message ? "<br>" : '') . "<span>{$intro} <a href='{$link}'>LINK</a></span>";
        }

        return [
            'greeting' => $greeting,
            'subject' => $subject,
            'message' => $message,
        ];
    }

    /**
     * @param int $alert_id
     * @param int $row_id
     * @return string
     * @throws \Exception
     */
    public function anaClickUpdateRun(int $alert_id, int $row_id): string
    {
        $alert = $this->alertsRepository->justAlert($alert_id);
        if ($alert && $alert->_table) {
            if (!$alert->is_active) {
                throw new \Exception('Error: Alert is not active!');
            }

            $alert->load(['_click_updates' => function ($q) {
                $q->with('_field');
            }]);

            if ($alert->_click_updates->count()) {
                $new = [];
                foreach ($alert->_click_updates as $click_update) {
                    if (!$click_update->_field) {
                        throw new \Exception('Error: Empty field for update!');
                    }
                    $new[$click_update->_field->field] = $click_update->new_value;
                }
                (new TableDataRepository())->updateRow($alert->_table, $row_id, $new, $alert->_table->user_id);
                (new TableDataService())->newTableVersion($alert->_table);
                return $alert->click_success_message ?: 'Data is updated';
            }

            return 'Empty click to update fields!';
        }
        throw new \Exception('Error: Alert is not found!');
    }

    /**
     * Get type params for Alert Email.
     *
     * @param $mode
     * @param $_h_fields
     * @param array $row
     * @param TableAlert $alert
     * @return string
     */
    protected function getSubject($mode, $_h_fields, array $row = [], TableAlert $alert)
    {
        switch ($mode) {
            case 'added':   $subject = 'New Record Added';
                break;
            case 'updated': $subject = 'Existing Record Updated'
                . ($alert->_field ? ': '.$alert->_field->name : '')
                . ($alert->new_value ? ': '.$alert->new_value : '');
                break;
            case 'deleted': $subject = 'Record Deleted';
                break;
            default:        $subject = '';
                break;
        }

        $shown_in_header = [];
        foreach ($_h_fields as $fld) {
            if ($fld->popup_header || $fld->popup_header_val) {
                $ar = $fld->popup_header ? [$fld->name] : [];
                if ($fld->popup_header_val) { $ar[] = ($row[$fld->field] ?? ''); }
                $shown_in_header[] = join(': ', $ar);
            }
        }
        if ($shown_in_header && $subject) {
            $subject .= ' (' . implode(', ', $shown_in_header) . ')';
        }

        return $subject;
    }

    /**
     * @param TableAlert $alert
     * @param array $trigger_row
     */
    public function doANR(TableAlert $alert, array $trigger_row)
    {
        $this->prepareAntomationsAdd($alert, $trigger_row);
        if ( ! $alert->ask_anr_confirmation) {
            $this->runAutomationsAdd($alert, $trigger_row);
        }
    }

    /**
     * @param TableAlert $alert
     */
    public function saveChangedTmpAnrs(TableAlert $alert)
    {
        foreach ($alert->_anr_tables as $anr_table) {
            $this->alertsRepository->updateAnrTable($alert->id, $anr_table->id, [
                'is_active' => $anr_table->temp_is_active,
                'name' => $anr_table->temp_name,
                'table_id' => $anr_table->temp_table_id,
                'qty' => $anr_table->temp_qty,
            ], true);

            foreach ($anr_table->_anr_fields as $anr_field) {
                $this->alertsRepository->updateAnrField($anr_table->id, $anr_field->id, [
                    'table_field_id' => $anr_field->temp_table_field_id,
                    'source' => $anr_field->temp_source,
                    'input' => $anr_field->temp_input,
                    'inherit_field_id' => $anr_field->temp_inherit_field_id,
                ], true);
            }
        }
    }

    /**
     * @param TableAlert $alert
     * @param array $row
     */
    protected function prepareAntomationsAdd(TableAlert $alert, array $row)
    {
        foreach ($alert->_anr_tables as $anr_table) {
            $this->alertsRepository->updateAnrTable($alert->id, $anr_table->id, [
                'approve_user' => auth()->id(),
                'need_approve' => 1,
                'triggered_row' => json_encode($row),
                'temp_is_active' => $anr_table->is_active,
                'temp_name' => $anr_table->name,
                'temp_table_id' => $anr_table->table_id,
                'temp_qty' => $anr_table->qty,
            ], true);

            foreach ($anr_table->_anr_fields as $anr_field) {
                $this->alertsRepository->updateAnrField($anr_table->id, $anr_field->id, [
                    'temp_table_field_id' => $anr_field->table_field_id,
                    'temp_source' => $anr_field->source,
                    'temp_input' => $anr_field->input,
                    'temp_inherit_field_id' => $anr_field->inherit_field_id,
                ], true);
            }
        }
    }

    /**
     * @param TableAlert $alert
     */
    protected function clearAntomationsAdd(TableAlert $alert)
    {
        $this->alertsRepository->massUpdateAnrTable($alert->id, [
            'need_approve' => 0,
            'triggered_row' => null,
        ]);
        $this->alertsRepository->clearAddedForTemp();
    }

    /**
     * @param TableAlert $alert
     * @param array $row
     */
    protected function runAutomationsAdd(TableAlert $alert, array $row)
    {
        $this->alertsRepository->massUpdateAnrTable($alert->id, [ 'need_approve' => 0 ]);
        //(new AlertAutomationAddJob($alert->id))->handle();
        AlertAutomationAddJob::dispatch($alert->id);
    }

    /**
     * @param int $alert_id
     * @param array $trigger_row
     * @return void
     * @throws \Exception
     */
    public function automationUpdateRecordsJob(int $alert_id, array $trigger_row)
    {
        $alert = $this->alertsRepository->getAlertAnrUfv($alert_id);
        $dataservice = new TableDataService();

        $automationHistory = new AutomationHistoryRepository($alert->user_id, $alert->table_id);
        $automationHistory->startTimer();

        foreach ($alert->_ufv_tables as $ufv_table) {
            if ($ufv_table->is_active && $ufv_table->_ufv_fields->count()) {
                $target_tb = intval($ufv_table->table_ref_cond_id) ? $ufv_table->_ref_cond->_ref_table : $alert->_table;
                $sql = (new TableDataQuery( $target_tb ))->getQuery();

                if ($ufv_table->table_ref_cond_id == 'this_row') {
                    $applied_ids = [$trigger_row['id']];
                } else {
                    $applied_ids = new TableDataQuery($target_tb);
                    if (intval($ufv_table->table_ref_cond_id)) {
                        $applied_ids->applyRefConditionRow($ufv_table->_ref_cond, $trigger_row, false);
                    }
                    $applied_ids = $applied_ids->getQuery()->select('id')->get()->pluck('id')->toArray();
                }
                $sql->whereIn('id', $applied_ids);

                $upd = [];
                foreach ($ufv_table->_ufv_fields as $ufv_field) {
                    $upd[$ufv_field->_field->field] = $ufv_field->source == 'Input'
                        ? $ufv_field->input
                        : $trigger_row[$ufv_field->_inherit_field->field];
                }
                $upd['row_hash'] = Uuid::uuid4();

                $affected_count = $sql->count();
                if (count($applied_ids) > $this->maxIndividual) {
                    $sql->update($upd);
                    (new TableDataService())->newTableVersion($target_tb);
                } else {
                    foreach ($applied_ids as $one_id) {
                        $dataservice->updateFromAlert($target_tb, $one_id, $upd);
                    }
                }
            }
        }

        $automationHistory->stopTimerAndSave('ANA', $alert->name, 'Automation', 'UFV');
    }

    /**
     * @param int $alert_id
     */
    public function automationAddRecordsJob(int $alert_id)
    {
        $alert = $this->alertsRepository->getAlertAnrUfv($alert_id);
        $datarepo = new TableDataRepository();
        $dataservice = new TableDataService();

        $automationHistory = new AutomationHistoryRepository($alert->user_id, $alert->table_id);
        $automationHistory->startTimer();

        foreach ($alert->_anr_tables as $anr_table) {
            if ($anr_table->temp_is_active && $anr_table->triggered_row && $anr_table->_temp_table) {
                $trigger_row = json_decode($anr_table->triggered_row, true);

                $mass_rows = [];
                for ($i = 0; $i < intval($anr_table->temp_qty ?: 1); $i++) {
                    $row = [ 'row_hash' => Uuid::uuid4() ];
                    foreach ($anr_table->_anr_fields as $anr_field) {
                        $tmpField = $anr_field->_temp_field->input_type == 'Formula'
                            ? $anr_field->_temp_field->field . '_formula'
                            : $anr_field->_temp_field->field;

                        $row[$tmpField] = $anr_field->temp_source == 'Input'
                            ? $anr_field->temp_input
                            : $trigger_row[ $anr_field->_temp_inherit_field->field ];
                    }
                    $mass_rows[] = $row;
                }

                if (count($mass_rows) > $this->maxIndividual) {
                    $datarepo->insertMass($anr_table->_temp_table, $mass_rows);
                    $dataservice->recalcTableFormulas($anr_table->_temp_table, $anr_table->_temp_table->user_id, Arr::pluck($mass_rows, 'id'));
                    $dataservice->newTableVersion($anr_table->_temp_table);
                } else {
                    foreach ($mass_rows as $one_row) {
                        $dataservice->insertFromAlert($anr_table->_temp_table, $one_row);
                    }
                }
            }
        }

        $this->clearAntomationsAdd($alert);

        $automationHistory->stopTimerAndSave('ANA', $alert->name, 'Automation', 'ANR');
    }

    /**
     * @param TableAlert $alert
     * @param int|null $row_id
     * @return void
     */
    public function automationCallEmailAddon(TableAlert $alert, int $row_id = null)
    {
        if ($alert->enabled_sending && $alert->automation_email_addon_id && $alert->_email_addon) {
            $automationHistory = new AutomationHistoryRepository($alert->user_id, $alert->table_id);
            $automationHistory->startTimer();

            (new TableEmailAddonService())->sendEmails($alert->_email_addon, $row_id);

            $automationHistory->stopTimerAndSave('ANA', $alert->name, 'Automation', 'Sending Emails');
        }
    }
}
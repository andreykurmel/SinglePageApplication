<?php

namespace Vanguard\Services\Tablda;


use Carbon\Carbon;
use Ramsey\Uuid\Uuid;
use Vanguard\Jobs\AlertAutomationAddJob;
use Vanguard\Jobs\DelayAlertJob;
use Vanguard\Mail\EmailWithSettings;
use Vanguard\Models\Table\TableAlert;
use Vanguard\Repositories\Tablda\TableAlertsRepository;
use Vanguard\Repositories\Tablda\TableData\FormulaEvaluatorRepository;
use Vanguard\Repositories\Tablda\TableData\TableDataQuery;
use Vanguard\Repositories\Tablda\TableData\TableDataRepository;

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
    public function sendEmail(TableAlert $alert, array $active_rows, array $recipients, array $changed_fields, string $type)
    {
        if (!$alert->mail_delay_hour && !$alert->mail_delay_min && !$alert->mail_delay_sec) {
            $this->sendEmailNotificationjob($alert->id, $active_rows, $recipients, $changed_fields, $type); // no Delay
        } else {
            $delayTime = Carbon::now()
                ->addHours($alert->mail_delay_hour)
                ->addMinutes($alert->mail_delay_min)
                ->addSeconds($alert->mail_delay_sec);
            DelayAlertJob::dispatch($alert->id, $active_rows, $recipients, $changed_fields, $type)->delay( $delayTime );
        }
    }


    /**
     * @param int $alert_id
     * @param array $active_rows
     * @param array $recipients
     * @param array $changed_fields
     * @param string $type
     */
    public function sendEmailNotificationjob(int $alert_id, array $active_rows, array $recipients, array $changed_fields, string $type)
    {
        $alert = $this->alertsRepository->justAlert($alert_id);
        $alert->load(['_table', '_col_group']);
        $row = array_first($active_rows) ?: [];

        $formula_parser = new FormulaEvaluatorRepository($alert->_table, $alert->_table->user_id, true);
        $_h_fields = $alert->_table->_fields()->joinOwnerHeader()->get();
        $greeting = $alert->mail_addressee ? $formula_parser->formulaReplaceVars($row, $alert->mail_addressee) : '';
        $subject = $alert->mail_subject
            ? $formula_parser->formulaReplaceVars($row, $alert->mail_subject)
            : $this->getSubject($type, $_h_fields, $row, $alert);

        $fields_arr = $this->service->getFieldsArrayForNotification($alert->_table, $alert->_col_group);

        $params = [
            'from.name' => config('app.name').' ANA',
            'from.account' => 'noreply',
            'subject' => $subject,
            'to.address' => $recipients['to'],
        ];
        $data = [
            'greeting' => $greeting,
            'replace_main_message' => $alert->mail_message ? $formula_parser->formulaReplaceVars($row, $alert->mail_message) : '',
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
            if ($fld->popup_header) {
                $shown_in_header[] = $fld->name . ': ' . ($row[$fld->field] ?? '');
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
        AlertAutomationAddJob::dispatch($alert->id);
    }

    /**
     * @param int $alert_id
     * @param array $trigger_row
     */
    public function automationUpdateRecordsJob(int $alert_id, array $trigger_row)
    {
        $alert = $this->alertsRepository->getAlertAnrUfv($alert_id);
        $dataservice = new TableDataService();

        foreach ($alert->_ufv_tables as $ufv_table) {
            if ($ufv_table->is_active) {
                $target_tb = $ufv_table->table_ref_cond_id ? $ufv_table->_ref_cond->_ref_table : $alert->_table;
                $sql = (new TableDataQuery( $target_tb ))->getQuery();

                $applied_ids = new TableDataQuery( $target_tb );
                if ($ufv_table->table_ref_cond_id) {
                    $applied_ids->applyRefConditionRow($ufv_table->_ref_cond, $trigger_row, false);
                }
                $applied_ids = $applied_ids->getQuery()->select('id')->get()->pluck('id')->toArray();
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
    }

    /**
     * @param int $alert_id
     */
    public function automationAddRecordsJob(int $alert_id)
    {
        $alert = $this->alertsRepository->getAlertAnrUfv($alert_id);
        $datarepo = new TableDataRepository();
        $dataservice = new TableDataService();

        foreach ($alert->_anr_tables as $anr_table) {
            if ($anr_table->temp_is_active && $anr_table->triggered_row && $anr_table->_temp_table) {
                $trigger_row = json_decode($anr_table->triggered_row, true);

                $mass_rows = [];
                for ($i = 0; $i < intval($anr_table->temp_qty ?: 1); $i++) {
                    $row = [ 'row_hash' => Uuid::uuid4() ];
                    foreach ($anr_table->_anr_fields as $anr_field) {
                        $row[ $anr_field->_temp_field->field ] = $anr_field->temp_source == 'Input'
                            ? $anr_field->temp_input
                            : $trigger_row[ $anr_field->_temp_inherit_field->field ];
                    }
                    $mass_rows[] = $row;
                }

                if (count($mass_rows) > $this->maxIndividual) {
                    $datarepo->insertMass($anr_table->_temp_table, $mass_rows);
                    (new TableDataService())->newTableVersion($anr_table->_temp_table);
                } else {
                    foreach ($mass_rows as $one_row) {
                        $dataservice->insertFromAlert($anr_table->_temp_table, $one_row);
                    }
                }
            }
        }

        $this->clearAntomationsAdd($alert);
    }
}
<?php

namespace Vanguard\Repositories\Tablda\TableData;

use Illuminate\Support\Facades\DB;
use Vanguard\Models\Table\Table;
use Vanguard\Models\Table\TableField;
use Vanguard\Repositories\Tablda\Permissions\TableDataRequestRepository;
use Vanguard\Repositories\Tablda\TableAlertsRepository;
use Vanguard\Repositories\Tablda\TableBackupRepository;
use Vanguard\Repositories\Tablda\TableEmailAddonRepository;
use Vanguard\Repositories\Tablda\TableFieldRepository;
use Vanguard\Repositories\Tablda\TableTwilioAddonRepository;

class FormulaUpdaterRepository
{
    /**
     * @param Table $table
     * @param TableField $db_field
     * @param array $field
     * @return array
     */
    public function updateNamesInFormulaes(Table $table, TableField $db_field, array $field)
    {
        $nodef1 = [];
        $nodef2 = [];
        $newname = $field['name'] ?? '';
        if ($newname && $db_field->name && $db_field->name != $newname) {
            $nodef1 = $this->namesUpdater($table, $db_field->name, $newname);
        }
        $newfs = $field['formula_symbol'] ?? '';
        if ($newfs && $db_field->formula_symbol && $db_field->formula_symbol != $newfs) {
            $nodef2 = $this->namesUpdater($table, $db_field->formula_symbol, $newfs);
        }
        return array_merge($nodef1, $nodef2);
    }

    /**
     * @param Table $table
     * @param $from
     * @param $to
     * @return array
     */
    protected function namesUpdater(Table $table, $from, $to)
    {
        $from = preg_replace('/[^\w\d]/i','', $from);
        $to = preg_replace('/[^\w\d]/i','', $to);
        if (! $from || ! $to) {
            return [];
        }
        $updater = [];
        $nodef = [];
        foreach ($table->_fields as $header) {
            if ($header->input_type === 'Formula') {
                $updater[$header->field . '_formula'] = DB::raw("REPLACE(" . $header->field . '_formula' . ", '{" . $from . "}', '{" . $to . "}')");
            }
            if (strpos($header->f_default, $from) !== false) {
                $header->f_default = preg_replace('/\{' . $from . '\}/i', '{' . $to . '}', $header->f_default);
                (new TableFieldRepository())->updateTableField($table, $header->id, $header->toArray());
                $nodef[] = $header->field;
            }
        }

        $this->updateAlerts($table, $from, $to);
        $this->updateDCRs($table, $from, $to);
        $this->updateEmails($table, $from, $to);
        $this->updateTwilios($table, $from, $to);

        if ($updater) {
            (new TableDataQuery($table))->getQuery()->update($updater);
        }
        return $nodef;
    }

    /**
     * @param Table $table
     * @param $from
     * @param $to
     */
    protected function updateAlerts(Table $table, $from, $to)
    {
        $repo = new TableAlertsRepository();
        foreach ($table->_alerts as $alert) {
            if (
                strpos($alert->mail_subject, $from) !== false
                || strpos($alert->mail_addressee, $from) !== false
                || strpos($alert->mail_message, $from) !== false
            ) {
                $array = $alert->toArray();
                $array['mail_subject'] = preg_replace('/\{' . $from . '\}/i', '{' . $to . '}', $array['mail_subject']);
                $array['mail_addressee'] = preg_replace('/\{' . $from . '\}/i', '{' . $to . '}', $array['mail_addressee']);
                $array['mail_message'] = preg_replace('/\{' . $from . '\}/i', '{' . $to . '}', $array['mail_message']);
                $repo->updateAlert($alert->id, $array);
            }
        }
    }

    /**
     * @param Table $table
     * @param $from
     * @param $to
     */
    protected function updateDCRs(Table $table, $from, $to)
    {
        $repo = new TableDataRequestRepository();
        $columns = ['dcr_email_subject', 'dcr_addressee_txt', 'dcr_email_message','dcr_signature_txt','dcr_save_signature_txt',
            'dcr_save_email_subject', 'dcr_save_addressee_txt', 'dcr_save_email_message','dcr_upd_signature_txt',
            'dcr_upd_email_subject', 'dcr_upd_addressee_txt', 'dcr_upd_email_message',];
        foreach ($table->_table_requests as $dcr) {
            $found_from = array_reduce($columns, function ($res, $item) use ($dcr, $from) {
                return $res || strpos($dcr->$item, $from) !== false;
            });
            if ($found_from) {
                $array = $dcr->toArray();
                foreach ($columns as $column) {
                    $array[$column] = preg_replace('/\{' . $from . '\}/i', '{' . $to . '}', $array[$column]);
                }
                $repo->updateDataRequest($dcr->id, $array);
            }
        }
    }

    /**
     * @param Table $table
     * @param $from
     * @param $to
     */
    protected function updateEmails(Table $table, $from, $to)
    {
        $repo = new TableEmailAddonRepository();
        foreach ($table->_email_addon_settings()->get() as $email_settings) {
            if (strpos($email_settings->email_subject, $from) !== false || strpos($email_settings->email_body, $from) !== false) {
                $array = $email_settings->toArray();
                $array['email_subject'] = preg_replace('/\{' . $from . '\}/i', '{' . $to . '}', $array['email_subject']);
                $array['email_body'] = preg_replace('/\{' . $from . '\}/i', '{' . $to . '}', $array['email_body']);
                $repo->updateEmailSett($email_settings->id, $array);
            }
        }
    }

    /**
     * @param Table $table
     * @param $from
     * @param $to
     */
    protected function updateTwilios(Table $table, $from, $to)
    {
        $repo = new TableTwilioAddonRepository();
        foreach ($table->_twilio_addon_settings()->get() as $twilio_settings) {
            if (strpos($twilio_settings->sms_body, $from) !== false) {
                $array = $twilio_settings->toArray();
                $array['sms_body'] = preg_replace('/\{' . $from . '\}/i', '{' . $to . '}', $array['sms_body']);
                $repo->updateTwilioSett($twilio_settings->id, $array);
            }
        }
    }
}
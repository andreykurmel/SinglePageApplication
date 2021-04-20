<?php

namespace Vanguard\Services\Tablda;

use Vanguard\Classes\TabldaEncrypter;
use Vanguard\Jobs\SendgridBackground;
use Vanguard\Mail\TabldaMail;
use Vanguard\Models\Table\Table;
use Vanguard\Models\Table\TableEmailAddonSetting;
use Vanguard\Repositories\Tablda\TableData\FormulaEvaluatorRepository;
use Vanguard\Repositories\Tablda\TableData\TableDataQuery;
use Vanguard\Repositories\Tablda\TableEmailAddonRepository;

class TableEmailAddonService
{
    protected $service;
    protected $addonRepo;

    /**
     * TableEmailAddonService constructor.
     */
    public function __construct()
    {
        $this->service = new HelperService();
        $this->addonRepo = new TableEmailAddonRepository();
    }

    /**
     * @param TableEmailAddonSetting $email
     * @return array
     */
    public function previewEmail(TableEmailAddonSetting $email)
    {
        $table = $email->_table;
        $formula_parser = new FormulaEvaluatorRepository($table, $table->user_id, true);
        $all_rows = $this->getRowsArray($table, $email->limit_row_group_id);
        $response = [];
        foreach ($all_rows as $row) {
            $mailable = $this->getTabldaMail($email, $formula_parser, $row);
            if ($mailable) {
                $response[] = $mailable->for_preview();
            }
        }
        return $response;
    }

    /**
     * @param $table_email_id
     * @return TableEmailAddonSetting
     */
    public function getEmailSett($table_email_id)
    {
        return $this->addonRepo->getEmailSett($table_email_id);
    }

    /**
     * @param Table $table
     */
    public function loadForTable(Table $table)
    {
        $this->addonRepo->loadForTable($table);
    }

    /**
     * @param array $data
     * @return mixed
     */
    public function insertEmailSett(Array $data)
    {
        return $this->addonRepo->insertEmailSett($data);
    }

    /**
     * @param $email_id
     * @param array $data
     * @return mixed
     */
    public function updateEmailSett($email_id, Array $data)
    {
        return $this->addonRepo->updateEmailSett($email_id, $data);
    }

    /**
     * @param $table_email_id
     * @return mixed
     */
    public function deleteEmailSett($table_email_id)
    {
        return $this->addonRepo->deleteEmailSett($table_email_id);
    }

    /**
     * @param Table $table
     * @param int|null $row_group_id
     * @param bool $single
     * @return array
     */
    protected function getRowsArray(Table $table, int $row_group_id = null, bool $single = false)
    {
        if (!$row_group_id) {
            return [];
        }

        $query = new TableDataQuery($table);
        $query->applySelectedRowGroup($row_group_id);

        $query = $query->getQuery();
        $res = $single ? $query->first() : $query->get();

        return $res ? $res->toArray() : [];
    }

    /**
     * @param TableEmailAddonSetting $email
     * @param FormulaEvaluatorRepository $parser
     * @param array $row
     * @return null|TabldaMail
     */
    protected function getTabldaMail(TableEmailAddonSetting $email, FormulaEvaluatorRepository $parser, array $row)
    {
        $reply_source = $email->sender_reply_to_isdif
            ? ($row[$email->_sender_reply_to_field->field??''] ?? '')
            : ($email->sender_reply_to ?? '');
        $reply_to = $this->service->parseRecipients($reply_source);

        $recipients = array_merge(
            $this->service->parseRecipients($row[$email->_recipient_field->field??''] ?? ''),
            $this->service->parseRecipients($email->recipient_email ?? '')
        );

        if ($email->server_type == 'sendgrid') {
            $sender_email = $email->sender_email_isdif
                ? ($row[$email->_sender_email_field->field??''] ?? '')
                : ($email->sender_email ?? '');
            $sender_pass = $email->smtp_key_mode == 'account' ? TabldaEncrypter::decrypt($email->_sendgrid_key->key ?? '') : $email->sendgrid_api_key;
        } else {
            $sender_email = $email->smtp_key_mode == 'account' ? ($email->_google_key->email ?? '') : $email->google_email;
            $sender_pass = $email->smtp_key_mode == 'account' ? TabldaEncrypter::decrypt($email->_google_key->app_pass ?? '') : $email->google_app_pass;
        }

        $sender_email = filter_var($sender_email, FILTER_VALIDATE_EMAIL);
        if (!$recipients || !$sender_email || !$sender_pass) {
            return null;
        }

        $params = [
            'from.email' => $sender_email,
            'from.name' => $email->sender_name ?: config('app.name'),
            'from.account' => 'users_account',
            'subject' => $email->email_subject ? $parser->formulaReplaceVars($row, $email->email_subject, true) : '',
            'to.address' => $recipients,
            'to.name' => '',
            'reply.to' => $reply_to ?? '',
            'users_account' => [
                'email' => $sender_email,
                'pass' => $sender_pass,
            ]
        ];
        $data = [
            'body_data' => $email->email_body ? $parser->formulaReplaceVars($row, $email->email_body, true) : '',
        ];

        return new TabldaMail('tablda.emails.addon_sender', $data, $params, $email);
    }

    /**
     * @param TableEmailAddonSetting $email
     * @return array
     */
    public function sendEmails(TableEmailAddonSetting $email)
    {
        $table = $email->_table;
        $formula_parser = new FormulaEvaluatorRepository($table, $table->user_id, true);
        $msg = $email->notInProgress()
            ? 'Icorrect email settings or empty rows!'
            : 'Emails are in sending process!';
        if ($this->getTabldaMail($email, $formula_parser, []) && $email->notInProgress()) {
            $all_rows = $this->getRowsArray($table, $email->limit_row_group_id);
            foreach ($all_rows as $row) {
                switch ($email->server_type) {
                    case 'google': $this->sendViaGoogleSmtp($email, $formula_parser, $row); break;
                    case 'sendgrid': $this->sendViaSendGridApi($email, $formula_parser, $row); break;
                }
            }
            if ($all_rows) {
                $email->startPrepared(count($all_rows));
                $msg = count($all_rows).' emails are added to query!';
            }
        }
        return [
            'result' => $msg,
            'prepared_emails' => $email->prepared_emails,
            'sent_emails' => $email->sent_emails,
        ];
    }

    /**
     * @param TableEmailAddonSetting $email
     * @return array
     */
    public function cancelEmail(TableEmailAddonSetting $email)
    {
        $email->cancelQueue();
        return [
            'prepared_emails' => $email->prepared_emails,
            'sent_emails' => $email->sent_emails,
        ];
    }

    /**
     * @param TableEmailAddonSetting $email
     * @param FormulaEvaluatorRepository $parser
     * @param array $row
     */
    protected function sendViaGoogleSmtp(TableEmailAddonSetting $email, FormulaEvaluatorRepository $parser, array $row)
    {
        $mailable = $this->getTabldaMail($email, $parser, $row);
        if ($mailable) {
            \Mail::to($mailable->recipients())->send($mailable);
        }
    }

    /**
     * @param TableEmailAddonSetting $email
     * @param FormulaEvaluatorRepository $parser
     * @param array $row
     */
    protected function sendViaSendGridApi(TableEmailAddonSetting $email, FormulaEvaluatorRepository $parser, array $row)
    {
        $mailable = $this->getTabldaMail($email, $parser, $row);
        if (!$mailable) {
            return;
        }

        $sender_email = $email->sender_email;
        $sender_api = $email->smtp_key_mode == 'account' ? TabldaEncrypter::decrypt($email->_sendgrid_key->key ?? '') : $email->sendgrid_api_key;
        $reply = $email->sender_reply_to ?? '';
        $recipients = [];
        foreach ($mailable->recipients() as $eml) {
            $recipients[$eml] = $eml;
        }

        if (!$recipients || !$sender_email || !$sender_api || !$reply) {
            return;
        }

        $sendgrid = new \SendGrid\Mail\Mail();
        $sendgrid->setFrom($sender_email, $email->sender_name ?: config('app.name'));
        $sendgrid->setSubject( $mailable->subjects() );
        $sendgrid->addTos( $recipients );
        $sendgrid->setReplyTo($reply, $reply);
        $sendgrid->addContent("text/html", $mailable->bodys());

        dispatch(new SendgridBackground($sendgrid, $sender_api, $email));
    }
}
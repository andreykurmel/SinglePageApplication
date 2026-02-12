<?php

namespace Vanguard\Services\Tablda;

use Carbon\Carbon;
use Exception;
use Vanguard\Classes\TabldaEncrypter;
use Vanguard\Jobs\DelayEmailAddon;
use Vanguard\Jobs\SendSingleEmail;
use Vanguard\Mail\TabldaMail;
use Vanguard\Models\Table\Table;
use Vanguard\Models\Table\TableEmailAddonHistory;
use Vanguard\Models\Table\TableEmailAddonSetting;
use Vanguard\Models\Table\TableEmailRight;
use Vanguard\Models\Table\TableField;
use Vanguard\Models\Table\TableFieldLink;
use Vanguard\Models\User\UserApiKey;
use Vanguard\Models\User\UserEmailAccount;
use Vanguard\Repositories\Tablda\FileRepository;
use Vanguard\Repositories\Tablda\TableData\FormulaEvaluatorRepository;
use Vanguard\Repositories\Tablda\TableData\TableDataRepository;
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
     * @param int|null $row_id
     * @param string $special
     * @return array
     */
    public function previewEmail(TableEmailAddonSetting $email, int $row_id = null, string $special = '')
    {
        $all_rows = $this->getRowsArray($email->_table, $email->limit_row_group_id, $row_id);
        $previews = [];
        foreach ($all_rows as $i => $row) {
            if (
                ($special && $i == 0) //init -> preview first
                ||
                (!$special && (!$row_id || $row_id == $row['id'])) //preview selected
            ) {
                $mailable = $this->getTabldaMail($email, $row, auth()->id());
                if ($mailable) {
                    $previews[$row['id']] = $mailable->for_preview();
                    $previews[$row['id']]['history'] = $email->_history_emails
                        ->where('row_id', '=', $row['id'])
                        ->sortBy('send_date', SORT_REGULAR, true)
                        ->map(function(TableEmailAddonHistory $item) {
                            return $item->decodeArrays();
                        })
                        ->values();
                }
            }
        }
        return [
            'all_rows' => $all_rows,
            'previews' => $previews ?: null,
        ];
    }

    /**
     * @param Table $table
     * @param int|null $row_group_id
     * @param int|null $single_id
     * @return array
     */
    protected function getRowsArray(Table $table, int $row_group_id = null, int $single_id = null)
    {
        return (new TableDataRepository())
            ->getRowsByRowGroup($table, $row_group_id, $single_id)
            ->toArray();
    }

    /**
     * @param TableEmailAddonSetting $email
     * @param array $row
     * @return null|TabldaMail
     */
    public function getTabldaMail(TableEmailAddonSetting $email, array $row, $uid)
    {
        $table = $email->_table;
        $parser = new FormulaEvaluatorRepository($table, $table->user_id, true);

        $reply_source = $email->sender_reply_to_isdif
            ? ($row[$email->_sender_reply_to_field->field ?? ''] ?? '')
            : ($email->sender_reply_to ?? '');
        $reply_to = $this->service->parseRecipients($reply_source);

        $recipients = ['to' => [], 'cc' => [], 'bcc' => []];

        $recipients['to'] = $this->service->addRecipientsEmails($recipients['to'], $row[$email->_recipient_field->field ?? ''] ?? '');
        $recipients['to'] = $this->service->addRecipientsEmails($recipients['to'], $email->recipient_email ?? '', true);
        $recipients['cc'] = $this->service->addRecipientsEmails($recipients['cc'], $row[$email->_cc_recipient_field->field ?? ''] ?? '');
        $recipients['cc'] = $this->service->addRecipientsEmails($recipients['cc'], $email->cc_recipient_email ?? '', true);
        $recipients['bcc'] = $this->service->addRecipientsEmails($recipients['bcc'], $row[$email->_bcc_recipient_field->field ?? ''] ?? '');
        $recipients['bcc'] = $this->service->addRecipientsEmails($recipients['bcc'], $email->bcc_recipient_email ?? '', true);

        if ($email->server_type == 'sendgrid') {
            $sender_email = $email->sender_email_isdif
                ? ($row[$email->_sender_email_field->field ?? ''] ?? '')
                : ($email->sender_email ?? '');
            $sender_pass = $email->smtp_key_mode == 'account' ? TabldaEncrypter::decrypt($email->_sendgrid_key->key ?? '') : $email->sendgrid_api_key;
        } else {
            $sender_email = $email->smtp_key_mode == 'account' ? ($email->_google_key->email ?? '') : $email->google_email;
            $sender_pass = $email->smtp_key_mode == 'account' ? TabldaEncrypter::decrypt($email->_google_key->app_pass ?? '') : $email->google_app_pass;
        }

        $sender_email = filter_var($sender_email, FILTER_VALIDATE_EMAIL);
        $empty_recipients = !$recipients['to'] && $row;
        if ($empty_recipients || !$sender_email || !$sender_pass) {
            return null;
        }

        $params = [
            'from.email' => $sender_email,
            'from.name' => $email->sender_name ?: config('app.name'),
            'from.account' => 'users_account',
            'subject' => $email->email_subject ? $parser->formulaReplaceVars($row, $email->email_subject) : '',
            'to.address' => $recipients['to'],
            'to.name' => '',
            'cc.address' => $recipients['cc'],
            'bcc.address' => $recipients['bcc'],
            'reply.to' => $reply_to ?? '',
            'users_account' => [
                'email' => $sender_email,
                'pass' => $sender_pass,
            ],
            'attach_files' => $this->getNeededAttachments($email, $row),
            'tablda_row' => $row ? (new TableDataService())->getDirectRow($table, $row['id'], ['files']) : null,
        ];
        $body_data = $email->email_body ? $parser->formulaReplaceVars($row, $email->email_body) : '';
        $body_data = $this->attachLinkTablesToBody($email, $row, $body_data, $uid);
        $data = [
            'body_data' => $body_data,
        ];
        if ($email->email_background_body) {
            $data['styles'] = [
                'body' => "margin: 0; padding: 0; width: 100%; background-color: {$email->email_background_body};",
            ];
        }

        return new TabldaMail('tablda.emails.addon_sender', $data, $params);
    }

    /**
     * @param TableEmailAddonSetting $email
     * @param array $row
     * @return array
     */
    protected function getNeededAttachments(TableEmailAddonSetting $email, array $row): array
    {
        if ($email->field_id_attachments && $row) {
            return (new FileRepository())->getEmailPaths($email->table_id, $email->field_id_attachments, $row['id']);
        } else {
            return [];
        }
    }

    /**
     * @param TableEmailAddonSetting $email
     * @param array $row
     * @param string $body
     * @param int|null $uid
     * @return string
     */
    protected function attachLinkTablesToBody(TableEmailAddonSetting $email, array $row, string $body, int $uid = null): string
    {
        if ($body) {
            $body = preg_replace_callback('/\[Link:([^\/]+)\/([^\/]+)\]/mi', function ($match) use ($email, $row, $uid) {
                $table = $email->_table;
                $field = $link = null;

                $all_fields = $table->_fields()->with('_links')->get();
                foreach ($all_fields as $header) {
                    foreach ($header->_links as $heLink) {
                        if ($heLink->name == $match[1]) {
                            $field = $header;
                            $link = $heLink;
                        }
                    }
                }

                $view = null;
                switch ($match[2]) {
                    case 'Board': $view = 'vertical'; break;
                    case 'List': $view = 'list'; break;
                    case 'Grid':
                    default: $view = 'table'; break;
                }

                if ($field && $link && $link->table_ref_condition_id) {
                    $table = $link->_ref_condition ? $link->_ref_condition->_ref_table : $field->_table;
                    [$rows_count, $rows_arr] = (new TableDataService())->getFieldRows($table, $link->toArray(), $row, 1, 50, ['uid' => $uid]);

                    $flds = $link->email_addon_fields ? json_decode($link->email_addon_fields) : [];
                    $fields_arr = $this->service->getFieldsArrayForEmailAddon($table, $flds);

                    $data = [
                        'table_header' => $field->name.' ('.$link->link_type.')',
                        'mail_format' => $view ?: $email->email_link_viewtype ?: 'table',
                        'fields_arr' => $fields_arr,
                        'all_rows_arr' => $rows_arr,
                        'rows_count' => $rows_count,
                        'has_unit' => $this->service->fldsArrHasUnit($fields_arr),
                        'styles' => $this->extraStyles($email, $fields_arr),
                    ];

                    $partial = new TabldaMail('tablda.emails.templates.email_addon_linked_tb', $data);
                    return $partial->bodys();
                }
                return '[Error while getting rows from link!]';
            }, $body);
        }
        return $body;
    }

    /**
     * @param TableEmailAddonSetting $email
     * @param array $fields_arr
     * @return string[]
     */
    protected function extraStyles(TableEmailAddonSetting $email, array $fields_arr): array
    {
        $extra_styles = [
            'table' => 'color: #000; font-size: 15px; border-collapse: collapse;',
            'table--th' => 'padding: 0 3px; border: 1px solid #000; min-width:50px; background-color: #AAA;',
            'table--td' => 'padding: 0 3px; border: 1px solid #000; min-width:50px; background-color: #FFF;',
            'table--td-changed' => 'padding: 0 3px; border: 1px solid #000; min-width:50px; background-color: #AFA;',
            'table--td-noborder' => 'padding: 0 3px; border: none; min-width:50px; background-color: #FFF;',
        ];
        switch ($email->email_link_width_type) {
            case 'full': $extra_styles['table'] .= 'width: 100%;'; break;
            case 'content': $extra_styles['table'] .= 'width: auto;'; break;
            case 'column_size': $extra_styles['table'] .= 'width: '.($email->email_link_width_size*count($fields_arr)).'px;'; break;
            case 'total_size': $extra_styles['table'] .= 'width: '.($email->email_link_width_size).'px;'; break;
        }
        switch ($email->email_link_align) {
            case 'left': $extra_styles['table'] .= 'text-align: left;';
                $extra_styles['table--th'] .= 'text-align: left;';
                break;
            case 'center': $extra_styles['table'] .= 'text-align: center;';
                $extra_styles['table--th'] .= 'text-align: center;';
                break;
            case 'right': $extra_styles['table'] .= 'text-align: right;';
                $extra_styles['table--th'] .= 'text-align: right;';
                break;
        }
        return $extra_styles;
    }

    /**
     * @param string $str
     * @return string
     */
    protected function prepName(string $str): string
    {
        return preg_replace('/[^\w\d]/i', '', $str);
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
    public function loadForTable(Table $table, int $user_id = null)
    {
        $this->addonRepo->loadForTable($table, $user_id);
    }

    /**
     * @param array $data
     * @return TableEmailAddonSetting
     */
    public function insertEmailSett(array $data)
    {
        return $this->addonRepo->insertEmailSett($data);
    }

    /**
     * @param $email_id
     * @param array $data
     * @return mixed
     */
    public function updateEmailSett($email_id, array $data)
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
     * @param int $from_add_id
     * @param int $to_add_id
     * @return TableEmailAddonSetting
     */
    public function copyAdn(int $from_add_id, int $to_add_id)
    {
        return $this->addonRepo->copyAdn($from_add_id, $to_add_id);
    }

    /**
     * @param TableEmailAddonSetting $email
     * @param int|null $row_id
     * @return array
     */
    public function sendEmails(TableEmailAddonSetting $email, int $row_id = null)
    {
        $msg = $email->notInProgress()
            ? 'Icorrect email settings or empty rows!'
            : 'Emails are in sending process!';
        if ($email->notInProgress()) {
            $all_rows = $this->getRowsArray($email->_table, $email->limit_row_group_id, $row_id);
            foreach ($all_rows as $row) {
                $this->emailJob($email, $row, auth()->id());
            }
            if ($all_rows) {
                $email->startPrepared(count($all_rows), !!$row_id);
                $msg = count($all_rows) . ' email(s) have been added to the queue for sending.';
            }
        }
        return [
            'result' => $msg,
            'prepared_emails' => $email->prepared_emails,
            'sent_emails' => $email->sent_emails,
        ];
    }

    /**
     * @param UserApiKey|UserEmailAccount $key
     * @param array $email_data
     * @param TableField $field
     * @param int $row_id
     * @return string[]
     * @throws \SendGrid\Mail\TypeException
     */
    public function sendSingleEmail($key, array $email_data, TableField $field, int $row_id): array
    {
        $row = (new TableDataRepository())
            ->getDirectRow($field->_table, $row_id)
            ->toArray();
        $hist = (new SendSingleEmail($key, $email_data, $field, $row, auth()->id()))->handle();
        return [
            'result' => 'Email is added to query.',
            'history' => $hist,
        ];
    }

    /**
     * @param TableEmailAddonSetting $email
     * @param $row
     * @param $uid
     */
    protected function emailJob(TableEmailAddonSetting $email, $row, $uid)
    {
        $queued = DelayEmailAddon::dispatch($email, $row, $uid);
        if ($email->email_send_time != 'now') {
            try {
                $interval = $email->email_send_time == 'field_specific'
                    ? ($email->_email_delay_record_fld ? $row[$email->_email_delay_record_fld->field] ?? '' : '')
                    : $email->email_delay_time;

                if (Carbon::make($interval) > Carbon::now()) {
                    $seconds = Carbon::now()->diffInSeconds($interval ?: '');
                    $queued->delay(Carbon::now()->addSeconds($seconds));
                }
            } catch (Exception $e) {
            }
        }
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
     * @param int|null $row_id
     * @param int|null $history_id
     * @return array
     */
    public function clearHistory(TableEmailAddonSetting $email, int $row_id = null, int $history_id = null)
    {
        return [
            'status' => $this->addonRepo->clearHistory($email, $row_id, $history_id)
        ];
    }

    /**
     * @param TableEmailAddonSetting $email
     * @param int $table_permis_id
     * @param $can_edit
     * @return TableEmailRight
     */
    public function toggleEmailRight(TableEmailAddonSetting $email, int $table_permis_id, $can_edit)
    {
        return $this->addonRepo->toggleEmailRight($email, $table_permis_id, $can_edit);
    }

    /**
     * @param TableEmailAddonSetting $email
     * @param int $table_permis_id
     * @return mixed
     */
    public function deleteEmailRight(TableEmailAddonSetting $email, int $table_permis_id)
    {
        return $this->addonRepo->deleteEmailRight($email, $table_permis_id);
    }
}
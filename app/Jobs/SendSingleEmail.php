<?php

namespace Vanguard\Jobs;

use Exception;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Arr;
use SendGrid\Mail\Mail;
use SendGrid\Mail\TypeException;
use Vanguard\Mail\TabldaMail;
use Vanguard\Models\Table\TableField;
use Vanguard\Models\TwilioHistory;
use Vanguard\Models\User\UserApiKey;
use Vanguard\Models\User\UserEmailAccount;
use Vanguard\Repositories\Tablda\TableData\FormulaEvaluatorRepository;
use Vanguard\Repositories\Tablda\TwilioHistoryRepository;
use Vanguard\Services\Tablda\HelperService;

class SendSingleEmail implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * @var UserApiKey|UserEmailAccount
     */
    protected $key;
    /**
     * @var int
     */
    protected $user_id;
    /**
     * @var array
     */
    protected $email_data;
    /**
     * @var TableField
     */
    protected $field;
    /**
     * @var array
     */
    protected $row;
    /**
     * @var HelperService
     */
    protected $service;
    /**
     * @var TwilioHistoryRepository
     */
    protected $twilio_repo;

    /**
     * @param $key
     * @param array $email_data
     * @param TableField $field
     * @param array $row
     * @param int $user_id
     */
    public function __construct($key, array $email_data, TableField $field, array $row, int $user_id)
    {
        $this->user_id = $user_id;
        $this->key = $key;
        $this->email_data = $email_data;
        $this->field = $field;
        $this->row = $row;
        $this->service = new HelperService();
        $this->twilio_repo = new TwilioHistoryRepository();
    }

    /**
     * @return TwilioHistory|null
     * @throws TypeException
     */
    public function handle()
    {
        $mailable = $this->getTabldaMail();
        if (!$mailable) {
            return null;
        }

        if ($this->key instanceof UserApiKey && $this->key->type == 'sendgrid') {
            $this->sendViaSendGridApi($mailable);
        }
        if ($this->key instanceof UserEmailAccount && $this->key->type == 'google') {
            $this->sendViaGoogleApi($mailable);
        }
        return $this->insertEmailHistory($mailable);
    }

    /**
     * @param TabldaMail $mailable
     * @return TwilioHistory
     */
    public function insertEmailHistory(TabldaMail $mailable): TwilioHistory
    {
        return $this->twilio_repo->store(
            $this->user_id,
            $mailable->recipients(),
            TwilioHistory::$EMAIL_TYPE,
            $mailable->history_elem(),
            $this->field->table_id,
            $this->field->id,
            $this->row['id']
        );
    }

    /**
     * @return TabldaMail
     * @throws Exception
     */
    public function getTabldaMail(): TabldaMail
    {
        $table = $this->field->_table;
        $parser = new FormulaEvaluatorRepository($table, $table->user_id, true);

        $recipient = Arr::first( $this->service->parseRecipients($this->email_data['to']) );
        $sender_email = $this->service->parseRecipients($this->email_data['from']);
        $sender_email = Arr::first($sender_email);
        $sender_pass = $this->key->decryptedKey();

        if (!$recipient || !$sender_email || !$sender_pass) {
            throw new Exception('No "from email" or "to email"!');
        }

        $reply_to = $this->service->parseRecipients($this->email_data['reply_to'] ?? '');

        $params = [
            'from.email' => $sender_email,
            'from.name' => $this->email_data['from_name'] ?: config('app.name'),
            'from.account' => 'users_account',
            'subject' => $this->email_data['subject'] ? $parser->formulaReplaceVars($this->row, $this->email_data['subject']) : '',
            'to.address' => $recipient,
            'to.name' => '',
            'reply.to' => Arr::first($reply_to),
            'users_account' => [
                'email' => $sender_email,
                'pass' => $sender_pass,
            ],
        ];
        $data = [
            'body_data' => $this->email_data['body'] ? $parser->formulaReplaceVars($this->row, $this->email_data['body']) : '',
        ];

        return new TabldaMail('tablda.emails.addon_sender', $data, $params);
    }

    /**
     * @param TabldaMail $mailable
     */
    public function sendViaGoogleApi(TabldaMail $mailable)
    {
        $mail = \Mail::to($mailable->recipients());
        $mail->send($mailable);
    }

    /**
     * @param TabldaMail $mailable
     * @throws TypeException
     */
    public function sendViaSendGridApi(TabldaMail $mailable)
    {
        $to_recipients = $mailable->recipients();
        $to_recipients = is_array($to_recipients) ? $to_recipients : [$to_recipients];
        $to_recipients = array_combine($to_recipients, $to_recipients);
        $sendgrid = new Mail();
        $sendgrid->setFrom($mailable->get_from(), $mailable->get_from_name());
        $sendgrid->setSubject($mailable->subjects());
        $sendgrid->addTos($to_recipients);
        if ($mailable->replys()) {
            $sendgrid->setReplyTo($mailable->replys(), $mailable->replys());
        }
        $sendgrid->addContent("text/html", $mailable->bodys());
        dispatch(new SendgridBackground($sendgrid, $this->key->decryptedKey()));
    }

    /**
     *
     */
    public function failed()
    {
        //
    }
}

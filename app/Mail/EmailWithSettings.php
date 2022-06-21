<?php

namespace Vanguard\Mail;

use Illuminate\Mail\PendingMail;
use Illuminate\Support\Facades\Log;
use Mail;
use Vanguard\Jobs\SendgridBackground;
use Vanguard\Models\EmailSettings;
use Vanguard\Models\Table\TableEmailAddonSetting;
use Vanguard\Models\User\UserInvitation;
use Vanguard\Services\Tablda\HelperService;
use Vanguard\User;

class EmailWithSettings
{
    /**
     * @var HelperService 
     */
    protected $service;
    /**
     * @var EmailSettings
     */
    protected $emailSettings;

    /**
     * @var string
     */
    protected $code;
    /**
     * @var array|string
     */
    protected $to;
    /**
     * @var array|string
     */
    protected $cc;
    /**
     * @var array|string
     */
    protected $bcc;

    /**
     * @param string $code
     * @param array|string $to
     * @param array|string $cc
     * @param array|string $bcc
     */
    public function __construct(string $code, $to, $cc = [], $bcc = [])
    {
        $this->service = new HelperService();

        $this->emailSettings = EmailSettings::where('email_code', '=', $code)->first()
            ?: (new EmailSettings());
        $this->code = $code;

        $this->to = $this->setAddress($this->emailSettings->to ?? '', $to, $cc, $bcc);
        $this->cc = $this->setAddress($this->emailSettings->cc ?? '', $to, $cc, $bcc);
        $this->bcc = $this->setAddress($this->emailSettings->bcc ?? '', $to, $cc, $bcc);
    }

    /**
     * @param string $variable
     * @param $to
     * @param $cc
     * @param $bcc
     * @return array|mixed
     */
    protected function setAddress(string $variable, $to, $cc, $bcc)
    {
        switch ($variable) {
            case '$to': return (is_array($to) ? array_filter($to) : $to);
            case '$cc': return (is_array($cc) ? array_filter($cc) : $cc);
            case '$bcc': return (is_array($bcc) ? array_filter($bcc) : $bcc);
            default: return $this->service->parseRecipients($variable);
        }
    }

    /**
     * @param array $params
     * @param array $data
     */
    public function send(array $params, array $data): void
    {
        $params = $this->replaceParams($params);
        $this->prepare()
            ->send(new TabldaMail($this->getView(), $data, $params));
    }

    /**
     * @param array $params
     * @param array $data
     */
    public function queue(array $params, array $data): void
    {
        $params = $this->replaceParams($params);
        $this->prepare()
            ->queue(new TabldaMail($this->getView(), $data, $params));
    }

    /**
     * @param User $user
     * @param UserInvitation $invitation
     */
    public function userInvitation(User $user, UserInvitation $invitation): void
    {
        $invite_link = config('app.url').'/register?invite='.$user->personal_hash;
        $invite_link .= '&mail='.$invitation->email;
        $user_name = $user->full_name();

        $params = $this->replaceParams([
            'to.address' => $invitation->email,
            'from.name' => $user_name.' via '.config('app.name'),
            'subject' => $user_name . ' (' . $user->email . ') invited you to ' . config('app.name'),
        ]);

        Mail::to($params['to.address'])
            ->send(new UsersInvite($params['from.name'], $params['subject'], $user_name, $user->email, $invite_link));
    }

    /**
     * @param TableEmailAddonSetting $email
     * @param TabldaMail $mailable
     * @param string $sender_api
     * @throws \SendGrid\Mail\TypeException
     */
    public function viaSendgrid(TableEmailAddonSetting $email, TabldaMail $mailable, string $sender_api): void
    {
        $this->to = array_filter( is_array($this->to) ? $this->to : [$this->to] );
        $this->cc = array_filter( is_array($this->cc) ? $this->cc : [$this->cc] );
        $this->bcc = array_filter( is_array($this->bcc) ? $this->bcc : [$this->bcc] );

        $reply = $email->sender_reply_to ?? '';
        $prepared = $this->replaceParams([
            'from.name' => $email->sender_name ?: config('app.name'),
            'subject' => $mailable->subjects(),
        ]);
        $to_recipients = array_combine($this->to, $this->to);

        $sendgrid = new \SendGrid\Mail\Mail();
        $sendgrid->setFrom($email->sender_email, $prepared['from.name']);
        $sendgrid->setSubject( $prepared['subject'] );
        $sendgrid->addTos( $to_recipients );
        if ($this->cc) {
            $cc_recipients = array_combine($this->cc, $this->cc);
            $sendgrid->addCc($cc_recipients);
        }
        if ($this->bcc) {
            $bcc_recipients = array_combine($this->bcc, $this->bcc);
            $sendgrid->addCc($bcc_recipients);
        }
        if ($reply) {
            $sendgrid->setReplyTo($reply, $reply);
        }
        $sendgrid->addContent("text/html", $mailable->bodys());

        foreach ($mailable->attachments() as $path => $filename) {
            $sendgrid->addAttachment(base64_encode(file_get_contents($path)), pathinfo($filename, PATHINFO_EXTENSION), $filename);
        }

        dispatch(new SendgridBackground($sendgrid, $sender_api));
    }

    /**
     * @param array $params
     * @return array
     */
    protected function replaceParams(array $params): array
    {
        $params['to.address'] = is_array($this->to) ? array_first($this->to) : $this->to;
        $params['from.name'] = $this->emailSettings->sender_name ?: ($params['from.name'] ?? '');
        $subject = $this->emailSettings->subject ?: '$subject';
        $params['subject'] = str_replace('$subject', $params['subject'] ?? '', $subject);
        return array_filter($params);
    }

    /**
     * @return PendingMail
     */
    protected function prepare(): PendingMail
    {
        $mail = Mail::to($this->to);
        if ($this->cc) {
            $mail->cc($this->cc);
        }
        if ($this->bcc) {
            $mail->bcc($this->bcc);
        }
        return $mail;
    }

    /**
     * @return string
     */
    protected function getView(): string
    {
        switch ($this->code) {
            case 'subscription_pay':
                return 'tablda.emails.payments.subscription_pay';

            case 'credit_pay':
                return 'tablda.emails.payments.credit_pay';

            case 'credit_deduct':
                return 'tablda.emails.payments.credit_deduct';

            case 'transfer_deduct':
                return 'tablda.emails.payments.transfer_deduct';

            case 'transfer_add':
                return 'tablda.emails.payments.transfer_add';

            case 'stim_view_request_feedback':
                return 'tablda.emails.stim_view_feedback';

            case 'alert_notification':
            case 'dcr_notification':
                return 'tablda.emails.row_changed';

            case 'email_from_addon':
                return 'tablda.emails.addon_sender';

            case 'backup_is_created':
                return 'tablda.emails.backup_created';

            case 'user_invitation':
                return 'emails.users_invite';

            case 'confirm_code_to_user':
                return 'tablda.emails.confirm-code';

            case 'registered_notif_to_admin':
            case 'confirmed_notif_to_admin':
                return 'tablda.emails.new-registration';

            case 'confirm_thank_to_user':
                return 'tablda.emails.confirm-thank';

            case 'password_reset':
                return 'tablda.emails.pass-reset';

            default:
                Log::error('EmailWithSettings.php error - code not found: ' . $this->code);
                return '';
        }
    }
}
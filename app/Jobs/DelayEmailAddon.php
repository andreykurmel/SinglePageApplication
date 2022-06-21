<?php

namespace Vanguard\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Vanguard\Classes\TabldaEncrypter;
use Vanguard\Mail\EmailWithSettings;
use Vanguard\Mail\TabldaMail;
use Vanguard\Models\Table\TableAlert;
use Vanguard\Models\Table\TableEmailAddonSetting;
use Vanguard\Repositories\Tablda\TableAlertsRepository;
use Vanguard\Repositories\Tablda\TableData\FormulaEvaluatorRepository;
use Vanguard\Repositories\Tablda\TableData\TableDataQuery;
use Vanguard\Repositories\Tablda\TableEmailAddonRepository;
use Vanguard\Services\Tablda\AlertFunctionsService;
use Vanguard\Services\Tablda\TableEmailAddonService;

class DelayEmailAddon implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    private $service;
    private $repo;
    private $email;
    private $row;
    private $uid;

    /**
     * @param TableEmailAddonSetting $email
     * @param $row
     * @param $uid
     */
    public function __construct(TableEmailAddonSetting $email, $row, $uid)
    {
        $this->email = $email;
        $this->row = $row;
        $this->uid = $uid;
        $this->service = new TableEmailAddonService();
        $this->repo = new TableEmailAddonRepository();
    }

    /**
     * @throws \SendGrid\Mail\TypeException
     */
    public function handle()
    {
        $mailable = $this->service->getTabldaMail($this->email, $this->row, $this->uid);
        if (!$mailable) {
            return;
        }
        switch ($this->email->server_type) {
            case 'google': $this->sendViaGoogleSmtp($mailable); break;
            case 'sendgrid': $this->sendViaSendGridApi($mailable, $this->email, $this->row, $this->uid); break;
        }
        $this->repo->insertEmailHistory($this->email->id, $this->row['id'], $mailable);
        $this->email->oneFinished();
    }

    /**
     * @param TabldaMail $mailable
     */
    public function sendViaGoogleSmtp(TabldaMail $mailable)
    {
        $mail = new EmailWithSettings('email_from_addon', $mailable->recipients(), $mailable->get_cc(), $mailable->get_bcc());
        $mail->send($mailable->get_params(), $mailable->get_data());
    }

    /**
     * @param TabldaMail $mailable
     * @param TableEmailAddonSetting $email
     * @throws \SendGrid\Mail\TypeException
     */
    public function sendViaSendGridApi(TabldaMail $mailable, TableEmailAddonSetting $email)
    {
        $sender_api = $email->smtp_key_mode == 'account' ? TabldaEncrypter::decrypt($email->_sendgrid_key->key ?? '') : $email->sendgrid_api_key;
        if (!$mailable->recipients() || !$email->sender_email || !$sender_api) {
            return;
        }

        $mail = new EmailWithSettings('email_from_addon', $mailable->recipients(), $mailable->get_cc(), $mailable->get_bcc());
        $mail->viaSendgrid($email, $mailable, $sender_api);
    }

    /**
     *
     */
    public function failed()
    {
        //
    }
}

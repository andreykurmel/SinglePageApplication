<?php

namespace Vanguard\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Mail\Mailer;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class TabldaMail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    /**
     * @var string
     */
    protected $view_str;
    /**
     * @var array
     */
    protected $data;
    /**
     * @var array
     */
    protected $params;
    /**
     * @var array
     */
    protected $accounts;
    /**
     * @var mixed|string
     */
    protected $selected_acc;

    /**
     * Send the message using the given mailer.
     *
     * @param  \Illuminate\Contracts\Mail\Mailer  $mailer
     * @return void
     */
    public function send(Mailer $mailer)
    {
        if ($this->selected_acc != 'noreply') {
            $credentials = $this->accounts[ $this->selected_acc ];
            $transport = new \Swift_SmtpTransport(config('mail.host'), config('mail.port'), config('mail.encryption'));
            $transport->setUsername($credentials['email']);
            $transport->setPassword($credentials['pass']);
            $mailer->setSwiftMailer( new \Swift_Mailer($transport) );
        }

        parent::send($mailer);
    }

    /**
     * TabldaMail constructor.
     * 
     * @param string $view
     * @param array $data
     * @param array $params
     */
    public function __construct(string $view, array $data = [], array $params = [])
    {
        $this->accounts = [
            'main' => [
                'email' => config('mail.accounts.main.email'),
                'pass' => config('mail.accounts.main.pass'),
            ],
            'noreply' => [
                'email' => config('mail.accounts.noreply.email'),
                'pass' => config('mail.accounts.noreply.pass'),
            ]
        ];
        if ($params['users_account'] ?? '') {
            $this->accounts['users_account'] = $params['users_account'];
        }
        $this->selected_acc = $params['from.account'] ?? 'noreply';


        $this->view_str = $view;
        $this->data = $data;
        $this->params = array_merge([
            'from.email' => config('mail.from.address'),
            'from.name' => config('mail.from.name'),
            'to.address' => config('mail.receiver.address'),
            'to.name' => '',
            'reply.to' => '',
            'attach_files' => [],
            'tablda_row' => null,
        ], $params);

        $this->data['styles'] = array_merge([
            /* Layout ------------------------------ */

            'body' => 'margin: 0; padding: 0; width: 100%; background-color: #F2F4F6;',
            'email-wrapper' => 'width: 100%; margin: 0; padding: 0; background-color: #F2F4F6; border-top: 5px solid #1c8966;',

            /* Masthead ----------------------- */

            'email-masthead' => 'padding: 25px 0; text-align: center;',
            'email-masthead_name' => 'font-size: 16px; font-weight: bold; color: #2F3133; text-decoration: none; text-shadow: 0 1px 0 white;',

            'email-body' => 'width: 100%; margin: 0; padding: 0; border-top: 1px solid #EDEFF2; border-bottom: 1px solid #EDEFF2; background-color: #FFF;',
            'email-body_inner' => 'width: auto; max-width: 570px; margin: 0 auto; padding: 0;',
            'email-body_cell' => 'padding: 35px;',

            'email-footer' => 'width: auto; max-width: 570px; margin: 0 auto; padding: 0; text-align: center;',
            'email-footer_cell' => 'color: #AEAEAE; padding: 35px; text-align: center;',

            /* Body ------------------------------ */

            'body_action' => 'width: 100%; margin: 30px auto; padding: 0; text-align: center;',
            'body_sub' => 'margin-top: 25px; padding-top: 25px; border-top: 1px solid #EDEFF2;',

            'red-text' => 'color: #F00;',

            /* Type ------------------------------ */

            'anchor' => 'color: #3869D4;',
            'header-1' => 'margin-top: 0; color: #2F3133; font-size: 19px; font-weight: bold; text-align: left;',
            'paragraph' => 'margin-top: 0; color: #74787E; font-size: 16px; line-height: 1.5em;',
            'paragraph-sub' => 'margin-top: 0; color: #74787E; font-size: 12px; line-height: 1.5em;',
            'paragraph-center' => 'text-align: center;',

            /* Buttons ------------------------------ */

            'button' => 'display: block; display: inline-block; width: 200px; min-height: 20px; padding: 10px;
                 background-color: #3869D4; border-radius: 3px; color: #ffffff; font-size: 15px; line-height: 25px;
                 text-align: center; text-decoration: none; -webkit-text-size-adjust: none;',

            'button--green' => 'background-color: #5cb85c;',
            'button--red' => 'background-color: #dc4d2f;',
            'button--blue' => 'background-color: #3869D4;',
            'button--default' => 'background-color: #1c8966;',

            /* Table ------------------------------ */

            'table' => 'width: 100%; color: #000; font-size: 15px; border-collapse: collapse;',
            'table--th' => 'padding: 0 3px; border: 1px solid #000; min-width:100px; background-color: #AAA;',
            'table--td' => 'padding: 0 3px; border: 1px solid #000; min-width:100px; background-color: #FFF;',
            'table--td-changed' => 'padding: 0 3px; border: 1px solid #000; min-width:100px; background-color: #AFA;',
            'table--td-noborder' => 'padding: 0 3px; border: none; min-width:100px; background-color: #FFF;',

            /* List ------------------------------ */

            'list' => 'width: 100%;',
            'list--head' => 'font-weight: bold;color: #333;',
            'list--data' => 'color: #555;',
            'list--data-changed' => 'font-weight: bold;color: #5F5;',
        ], $data['styles']??[]);
        $this->data['fontFamily'] = 'font-family: Arial, \'Helvetica Neue\', Helvetica, sans-serif;';
    }

    /**
     * @return string|array
     */
    public function recipients()
    {
        return $this->params['to.address'];
    }

    /**
     * @return string|array
     */
    public function get_cc()
    {
        return $this->params['cc.address'] ?? [];
    }

    /**
     * @return string|array
     */
    public function get_bcc()
    {
        return $this->params['bcc.address'] ?? [];
    }

    /**
     * @return array
     */
    public function get_params(): array
    {
        return $this->params;
    }

    /**
     * @return array
     */
    public function get_data(): array
    {
        return $this->data;
    }

    /**
     * @return array|null
     */
    public function row_tablda()
    {
        return $this->params['tablda_row'] ?? null;
    }

    /**
     * @param bool $encoded
     * @return array
     */
    public function for_preview(bool $encoded = false)
    {
        return [
            'from' => trim( ($this->params['from.name'] ?? '') . ' <'.$this->params['from.email'].'>' ),
            'reply' => $encoded ? json_encode($this->replys()) : $this->replys(),
            'to' => $encoded ? json_encode($this->recipients()) : $this->recipients(),
            'cc' => $encoded ? json_encode($this->get_cc()) : $this->get_cc(),
            'bcc' => $encoded ? json_encode($this->get_bcc()) : $this->get_bcc(),
            'subject' => $this->subjects(),
            'body' => $this->bodys(),
            'row_tablda' => $encoded ? json_encode($this->row_tablda()) : $this->row_tablda(),
        ];
    }

    /**
     * @return string|array
     */
    public function replys()
    {
        return $this->params['reply.to'];
    }

    /**
     * @return string
     */
    public function subjects()
    {
        return $this->params['subject'] ?? '';
    }

    /**
     * @return string
     */
    public function bodys()
    {
        return $this->data['body_data'] ?? $this->render();
    }

    /**
     * @return array
     */
    public function attachments()
    {
        return $this->params['attach_files'] ?? [];
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        $credentials = $this->accounts[ $this->selected_acc ];

        $this->view($this->view_str, $this->data)
            ->from($credentials['email'], $this->params['from.name'] ?? null)
            ->to($this->params['to.address'], $this->params['to.name'] ?? null);
        if (!empty($this->params['subject'])) {
            $this->subject($this->params['subject']);
        }
        if (!empty($this->params['reply.to'])) {
            $this->replyTo($this->params['reply.to']);
        }

        //attach files
        foreach ($this->params['attach_files'] as $path => $name) {
            $this->attach($path, ['as' => $name]);
        }

        return $this;
    }
}

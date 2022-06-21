<?php

namespace Vanguard\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class UsersInvite extends Mailable
{
    use Queueable, SerializesModels;

    protected $data;

    /**
     * @param string $from
     * @param string $subject
     * @param string $username
     * @param string $useremail
     * @param string $invitelink
     */
    public function __construct(string $from, string $subject, string $username, string $useremail, string $invitelink)
    {
        $this->data = [
            'from' => $from,
            'subject' => $subject,
            'username' => $username,
            'useremail' => $useremail,
            'invitelink' => $invitelink,
        ];
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        return $this->view('emails.users_invite')
            ->from(config('mail.from.address'), $this->data['from'])
            ->subject($this->data['subject'])
            ->with([
                'user_name' => $this->data['username'],
                'user_email' => $this->data['useremail'],
                'logo_url' => config('app.url') . '/assets/img/TablDA_w_text_full.png',
                'link' => $this->data['invitelink'],
            ]);
    }
}

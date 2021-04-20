<?php

namespace Vanguard\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Queue\ShouldQueue;
use Vanguard\Models\User\UserInvitation;
use Vanguard\User;

class UsersInvite extends Mailable
{
    use Queueable, SerializesModels;

    private $user;
    private $invitation;

    /**
     * UsersInvite constructor.
     * @param User $user
     */
    public function __construct(User $user, UserInvitation $invitation)
    {
        $this->user = $user;
        $this->invitation = $invitation;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        $invite_link = config('app.url').'/register?invitation='.$this->user->personal_hash;
        $invite_link .= '&mail='.$this->invitation->email;
        $user_name = ($this->user->first_name ? ($this->user->first_name . ' ' . $this->user->last_name ?: '') : $this->user->username);

        return $this->view('emails.users_invite')
            ->from(config('mail.from.noreply'), $user_name.' via '.config('app.name'))
            ->subject($user_name . ' (' . $this->user->email . ') invited you to ' . config('app.name'))
            ->with([
                'user_name' => $user_name,
                'user_email' => $this->user->email,
                'logo_url' => config('app.url').'/assets/img/TablDA_w_text_full.png',
                'link' => $invite_link,
            ]);
    }
}

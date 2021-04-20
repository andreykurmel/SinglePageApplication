<?php

namespace Vanguard\Listeners\Registration;

use Illuminate\Support\Facades\Mail;
use Vanguard\Events\User\Registered;
use Vanguard\Mail\TabldaMail;
use Vanguard\Repositories\User\UserRepository;

class SendConfirmationEmail
{
    /**
     * @var UserRepository
     */
    private $users;

    public function __construct(UserRepository $users)
    {
        $this->users = $users;
    }

    /**
     * Handle the event.
     *
     * @param  Registered  $event
     * @return void
     */
    public function handle(Registered $event)
    {
        if (! settings('reg_email_confirmation')) {
            return;
        }

        $user = $event->getRegisteredUser();

        $token = str_random(60);
        $this->users->update($user->id, [
            'confirmation_token' => $token
        ]);

        $params = [
            'from.account' => 'noreply',
            'subject' => sprintf("%s", trans('app.registration_confirmation')),
            //'subject' => sprintf("[%s] %s", settings('app_name'), trans('app.registration_confirmation')),
            'to.address' => $user->email
        ];
        $data = [
            'mail_action' => [
                'text' => trans('app.confirm_email'),
                'url' => route('register.confirm-email', $token),
            ]
        ];

        Mail::to($user->email)->queue( new TabldaMail('tablda.emails.confirm-code', $data, $params) );
    }
}

<?php

namespace Vanguard\Listeners\Registration;

use Illuminate\Support\Facades\Mail;
use Vanguard\Events\User\Confirmed;
use Vanguard\Mail\TabldaMail;
use Vanguard\Repositories\User\UserRepository;

class SendThankConfirmationEmail
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
     * @param  Confirmed  $event
     * @return void
     */
    public function handle(Confirmed $event)
    {
        if (! settings('reg_email_confirmation')) {
            return;
        }

        $user = $event->getConfirmedUser();

        $params = [
            'from.account' => 'noreply',
            'subject' => sprintf("%s", trans('app.confirmation_thank')),
            //'subject' => sprintf("[%s] %s", settings('app_name'), trans('app.confirmation_thank')),
            'to.address' => $user->email
        ];

        Mail::to($user->email)->queue( new TabldaMail('tablda.emails.confirm-thank', [], $params) );
    }
}

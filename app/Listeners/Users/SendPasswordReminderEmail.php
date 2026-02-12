<?php

namespace Vanguard\Listeners\Users;

use Vanguard\Events\User\RequestedPasswordResetEmail;
use Vanguard\Mail\EmailWithSettings;
use Vanguard\Repositories\User\UserRepository;

class SendPasswordReminderEmail
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
     * @param RequestedPasswordResetEmail $event
     * @return void
     */
    public function handle(RequestedPasswordResetEmail $event)
    {
        $user = $event->getUser();
        $token = $event->getToken();

        $params = [
            'from.account' => 'noreply',
            'subject' => sprintf("%s", trans('app.reset_password')),
            //'subject' => sprintf("[%s] %s", settings('app_name'), trans('app.reset_password')),
            'to.address' => $user->email
        ];
        $data = [
            'mail_action' => [
                'text' => trans('app.reset_password'),
                'url' => url('password/reset', $token),
            ]
        ];

        $mailer = new EmailWithSettings('password_reset', $user->email);
        $mailer->queue($params, $data);
    }
}

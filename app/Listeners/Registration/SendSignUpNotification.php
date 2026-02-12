<?php

namespace Vanguard\Listeners\Registration;

use Vanguard\Events\User\Registered;
use Vanguard\Mail\EmailWithSettings;
use Vanguard\Modules\Geolocation\GeoLocation;
use Vanguard\Repositories\User\UserRepository;
use Vanguard\Support\Enum\UserStatus;

class SendSignUpNotification
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
     * @param Registered $event
     * @return void
     */
    public function handle(Registered $event)
    {
        if (!settings('notifications_signup_email')) {
            return;
        }

        $registeredUser = $event->getRegisteredUser();

        foreach ($this->users->getUsersWithRole('Admin') as $user) {
            $conf = $registeredUser->status == UserStatus::UNCONFIRMED ? trans('app.unconfirmed_user') : trans('app.confirmed_user');
            $subject = sprintf("%s - %s", trans('app.new_user_registration'), $conf);
            //$subject = sprintf("[%s] %s - %s", settings('app_name'), trans('app.new_user_registration'), $conf);
            $subject .= ' (' . $registeredUser->email . ', ' . $registeredUser->username . ')';

            $params = [
                'subject' => $subject,
                'to.address' => $user->email
            ];
            $data = [
                'user' => $registeredUser,
                'locator' => GeoLocation::make()
            ];

            $mailer = new EmailWithSettings('registered_notif_to_admin', $user->email);
            $mailer->queue($params, $data);
        }
    }
}

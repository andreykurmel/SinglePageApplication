<?php

namespace Vanguard\Listeners\Registration;

use Illuminate\Support\Facades\Mail;
use Vanguard\Events\User\Confirmed;
use Vanguard\Mail\TabldaMail;
use Vanguard\Modules\Geolocation\GeoLocation;
use Vanguard\Repositories\User\UserRepository;
use Vanguard\Support\Enum\UserStatus;

class SendUserConfirmedNotification
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
        if (! settings('notifications_signup_email')) {
            return;
        }

        $confirmedUser = $event->getConfirmedUser();

        foreach ($this->users->getUsersWithRole('Admin') as $user) {
            $conf = $confirmedUser->status == UserStatus::UNCONFIRMED ? trans('app.unconfirmed_user') : trans('app.confirmed_user');
            $subject = sprintf("%s - %s", trans('app.new_user_registration'), $conf);
            //$subject = sprintf("[%s] %s - %s", settings('app_name'), trans('app.new_user_registration'), $conf);
            $subject .= ' ('.$confirmedUser->email.', '.$confirmedUser->username.')';

            $params = [
                'subject' => $subject,
                'to.address' => $user->email
            ];
            $data = [
                'user' => $confirmedUser,
                'locator' => GeoLocation::make()
            ];

            Mail::to($user->email)->queue( new TabldaMail('tablda.emails.new-registration', $data, $params) );
        }
    }
}

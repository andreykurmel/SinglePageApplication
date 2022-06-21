<?php

namespace Vanguard\Listeners\Registration;

use Vanguard\Events\User\Registered;
use Vanguard\Repositories\Tablda\UserRepository;

class SendConfirmationEmail
{
    /**
     * @var UserRepository
     */
    private $users;

    public function __construct()
    {
        $this->users = new UserRepository();
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
        $this->users->sendConfirmationEmail($user);
    }
}

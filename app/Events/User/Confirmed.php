<?php

namespace Vanguard\Events\User;

use Vanguard\User;

class Confirmed
{
    /**
     * @var User
     */
    private $confirmedUser;

    /**
     * confirmed constructor.
     * @param User $confirmedUser
     */
    public function __construct(User $confirmedUser)
    {
        $this->confirmedUser = $confirmedUser;
    }

    /**
     * @return User
     */
    public function getConfirmedUser()
    {
        return $this->confirmedUser;
    }
}

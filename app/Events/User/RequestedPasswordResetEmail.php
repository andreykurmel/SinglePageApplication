<?php

namespace Vanguard\Events\User;

use Vanguard\User;

class RequestedPasswordResetEmail
{
    /**
     * @var User
     */
    private $user;
    private $token;

    public function __construct(User $user, $token)
    {
        $this->user = $user;
        $this->token = $token;
    }

    /**
     * @return User
     */
    public function getUser()
    {
        return $this->user;
    }

    /**
     * @return User
     */
    public function getToken()
    {
        return $this->token;
    }
}

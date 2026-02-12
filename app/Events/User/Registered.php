<?php

namespace Vanguard\Events\User;

use Vanguard\User;

class Registered
{
    /**
     * @var User
     */
    private $registeredUser;
    public $promoValue;

    /**
     * Registered constructor.
     * @param User $registeredUser
     */
    public function __construct(User $registeredUser, string $promo_value = null)
    {
        $this->registeredUser = $registeredUser;
        $this->promoValue = $promo_value;
    }

    /**
     * @return User
     */
    public function getRegisteredUser()
    {
        return $this->registeredUser;
    }
}

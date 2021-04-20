<?php

namespace Vanguard\Ideas;

use Vanguard\Repositories\User\UserRepository;

/**
 * Class RepositoryMaker
 *
 * Just for working code-autocomplete with ServiceProviders
 *
 * @package Vanguard\Ideas
 */
class RepositoryMaker
{
    /**
     * @return UserRepository
     */
    public static function User()
    {
        return app(UserRepository::class);
    }
}
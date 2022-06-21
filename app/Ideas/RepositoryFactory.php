<?php

namespace Vanguard\Ideas;

use Vanguard\Ideas\Repos\CachedTableFieldRepository;
use Vanguard\Ideas\Repos\CachedTableRepository;
use Vanguard\Repositories\User\UserRepository;

/**
 * Class RepositoryFactory
 *
 * Just for working code-autocomplete with ServiceProviders
 *
 * @package Vanguard\Ideas
 */
class RepositoryFactory
{
    /**
     * @return UserRepository
     */
    public static function User()
    {
        return app(UserRepository::class);
    }

    /**
     * @return CachedTableRepository
     */
    public static function Table()
    {
        return new CachedTableRepository();
    }

    /**
     * @return CachedTableFieldRepository
     */
    public static function TableField()
    {
        return new CachedTableFieldRepository();
    }
}
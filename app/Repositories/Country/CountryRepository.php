<?php

namespace Vanguard\Repositories\Country;

interface CountryRepository
{
    /**
     * Create $key => $value array for all available countries.
     *
     * @param string $column
     * @param string $key
     * @return mixed
     */
    public function lists($column = 'name', $key = 'id');

    /**
     * Get phone codes for all available countries.
     *
     * @return mixed
     */
    public function phone_codes();

    /**
     * Get all available countries.
     * @return mixed
     */
    public function all();
}

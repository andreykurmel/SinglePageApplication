<?php

namespace Vanguard\Repositories\Country;

use Vanguard\Country;

class EloquentCountry implements CountryRepository
{
    /**
     * {@inheritdoc}
     */
    public function lists($column = 'name', $key = 'id')
    {
        return Country::orderBy('name')->pluck($column, $key);
    }

    /**
     * {@inheritdoc}
     */
    public function phone_codes($column = 'name', $key = 'id')
    {
        $forbidden = [248,780,16,28,796,44,52,308,60,316,580,581,850,92,630,124,388,136,659,660,662,670,212,214,
            500,638,260,336,831,832,833,239,74,744,652,663,535,334,162,166,574];
        return Country::whereNotIn('id', $forbidden)
            ->orderBy('name')
            ->groupBy('calling_code')
            ->selectRaw('GROUP_CONCAT(`name`) AS `name`, `calling_code` as `code`')
            ->get()
            ->toArray();
    }

    /**
     * {@inheritdoc}
     */
    public function all()
    {
        return Country::all();
    }
}

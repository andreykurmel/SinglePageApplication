<?php

namespace Vanguard\Modules\Geolocation;


class GeoLocation
{
    /**
     * GeoLocation constructor.
     */
    private function __construct()
    {
    }

    /**
     * Resolve GeoLocator
     *
     * @param string $concrete
     * @return GeoLocationInterface
     */
    public static function make($concrete = '') {
        if ($concrete) {
            return new $concrete();
        } else {
            return new IpApiComLocator();
        }
    }
}
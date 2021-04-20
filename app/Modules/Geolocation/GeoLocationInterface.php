<?php

namespace Vanguard\Modules\Geolocation;


interface GeoLocationInterface
{
    //construct
    public function __construct();

    //getters
    public function ip();
    public function country();
    public function city();
    public function timezone();
    public function organisation();
}
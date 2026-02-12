<?php

namespace Vanguard\Modules\Geolocation;


class IpApiComLocator implements GeoLocationInterface
{
    //location is json from http://ip-api.com/
    private $ip = null;
    private $location = null;
    private $undef_string = 'undefined';

    /**
     * IpApiComLocator constructor.
     */
    public function __construct()
    {
        $ip = $_SERVER['HTTP_X_FORWARDED_FOR'] ?? $_SERVER['REMOTE_ADDR'] ?? null;
        if ($ip) {
            $this->ip = $ip;

            $response = $this->getJson($ip);
            if (!$response) {
                //sometimes http://ip-api.com/ lags
                sleep(3);
                $response = $this->getJson($ip);
            }

            if ($response && $response->status == 'success') {
                $this->location = $response;
            }
        }
    }

    /**
     * @param $ip
     * @return mixed
     */
    private function getJson($ip) {
        $curlSession = curl_init();
        curl_setopt($curlSession, CURLOPT_URL, 'http://ip-api.com/json/' . $ip);
        curl_setopt($curlSession, CURLOPT_BINARYTRANSFER, true);
        curl_setopt($curlSession, CURLOPT_RETURNTRANSFER, true);

        $json = curl_exec($curlSession);
        curl_close($curlSession);
        return json_decode($json);
    }

    /**
     * @return string
     */
    public function ip()
    {
        return $this->ip ?: $this->undef_string;
    }

    /**
     * @return string
     */
    public function country()
    {
        return $this->location
            ? $this->location->country
            : $this->undef_string;
    }

    /**
     * @return string
     */
    public function city()
    {
        return $this->location
            ? $this->location->city
            : $this->undef_string;
    }

    /**
     * @return string
     */
    public function timezone()
    {
        return $this->location
            ? $this->location->timezone
            : $this->undef_string;
    }

    /**
     * @return string
     */
    public function organisation()
    {
        return $this->location
            ? $this->location->org
            : $this->undef_string;
    }

}
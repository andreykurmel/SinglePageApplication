<?php

namespace Vanguard\Classes;

class PostCurl
{

    public static function send(string $url, array $headers = [], array $fields = [])
    {
        $ch = curl_init();
        curl_setopt( $ch, CURLOPT_URL, $url );
        curl_setopt( $ch, CURLOPT_POST, 1 );
        curl_setopt( $ch, CURLOPT_RETURNTRANSFER, 1 );
        if ($headers) {
            curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        }
        if ($fields) {
            curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($fields));
        }

        return curl_exec( $ch );
    }
}
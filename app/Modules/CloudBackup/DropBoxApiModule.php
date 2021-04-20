<?php

namespace Vanguard\Modules\CloudBackup;


use GuzzleHttp\Client;

class DropBoxApiModule implements ApiModuleInterface
{
    /**
     * @param int $cloud_id
     * @return string
     */
    public function getCloudActivationUrl(int $cloud_id)
    {
        $app_key = env('DBOX_APP_KEY');
        $redirect = env('DBOX_CLOUD_ACTIVATE_URI');
        $state = json_encode([
            'cloud_id' => $cloud_id
        ]);
        return 'https://www.dropbox.com/oauth2/authorize?client_id='.$app_key
            .'&redirect_uri='.urlencode($redirect)
            .'&state='.urlencode($state)
            .'&response_type=code';
    }

    /**
     * @param string $code
     * @return array
     */
    public function getTokenFromCode(string $code)
    {
        $curl = new \GuzzleHttp\Client();
        $response = $curl->post('https://api.dropbox.com/oauth2/token', [
            'query' => [
                'code' => $code,
                'grant_type' => 'authorization_code',
                'redirect_uri' => env('DBOX_CLOUD_ACTIVATE_URI'),
                'client_id' => env('DBOX_APP_KEY'),
                'client_secret' => env('DBOX_APP_SECRET'),
            ]
        ]);
        return json_decode( $response->getBody()->getContents(), true );
    }

    /**
     * Get Access Token.
     *
     * @param string $token_json
     * @return string
     */
    public function accessToken(string $token_json)
    {
        $token = json_decode($token_json, true);
        return $token['access_token'] ?? '';
    }
}
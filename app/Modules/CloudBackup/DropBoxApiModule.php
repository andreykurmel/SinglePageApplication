<?php

namespace Vanguard\Modules\CloudBackup;


use Illuminate\Support\Facades\Storage;
use Vanguard\Repositories\Tablda\UserCloudRepository;

class DropBoxApiModule implements ApiModuleInterface
{
    /**
     * @param int $cloud_id
     * @return string
     */
    public function getCloudActivationUrl(int $cloud_id): string
    {
        $app_key = env('DBOX_APP_KEY');
        $redirect = env('DBOX_CLOUD_ACTIVATE_URI');
        $state = json_encode([
            'cloud_id' => $cloud_id
        ]);
        return 'https://www.dropbox.com/oauth2/authorize?client_id='.$app_key
            .'&redirect_uri='.urlencode($redirect)
            .'&state='.urlencode($state)
            .'&token_access_type=offline'
            .'&response_type=code';
    }

    /**
     * @param string $code
     * @param bool $is_refresh
     * @return array
     */
    public function getTokenFromCode(string $code, bool $is_refresh = false): array
    {
        $curl = new \GuzzleHttp\Client();
        $arr = [
            'grant_type' => $is_refresh ? 'refresh_token' : 'authorization_code',
            'client_id' => env('DBOX_APP_KEY'),
            'client_secret' => env('DBOX_APP_SECRET'),
        ];
        if ($is_refresh) {
            $arr['refresh_token'] = $code;
        } else {
            $arr['code'] = $code;
            $arr['redirect_uri'] = env('DBOX_CLOUD_ACTIVATE_URI');
        }
        $response = $curl->post('https://api.dropbox.com/oauth2/token', ['query' => $arr]);
        return json_decode( $response->getBody()->getContents(), true );
    }

    /**
     * Get Access Token.
     *
     * @param string $token_json
     * @param int $cloud_id
     * @return string
     */
    public function accessToken(string $token_json, int $cloud_id = 0): string
    {
        $token = json_decode($token_json, true);
        $cloud = (new UserCloudRepository())->getCloud($cloud_id);
        $exp = strtotime($cloud ? $cloud->modified_on : 'now') + intval($token['expires_in'] ?? 0);
        if (!empty($token['refresh_token']) && time() > $exp) {
            $new_token = $this->getTokenFromCode($token['refresh_token'], true);
            $token['access_token'] = $new_token['access_token'] ?? '';

            if ($cloud_id) {
                (new UserCloudRepository())->updateUserCloud($cloud_id, ['token_json' => json_encode($token)]);
            }
        }
        return $token['access_token'] ?? '';
    }

    /**
     * @param int $cloud_id
     * @param array $extensions
     * @return array : [ ['id'=>'h35raw3', 'name'=>'Table1'], ... ]
     */
    public function driveFindFiles(int $cloud_id, array $extensions)
    {
        $curl = new \GuzzleHttp\Client();
        $response = $curl->post('https://api.dropboxapi.com/2/files/search_v2', [
            'headers' => [
                'Authorization' => 'Bearer '.$this->getToken($cloud_id),
                'Content-Type' => 'application/json',
            ],
            'json' => [
                'query' => '.'.array_first($extensions),
                'options' => [
                    'max_results' => 20,
                    'filename_only ' => true,
                    'file_extensions' => $extensions,
                    'order_by' => 'last_modified_time'
                ],
            ]
        ]);
        $data = json_decode( $response->getBody()->getContents(), true );

        return array_map(function ($item) {
            return [
                'id' => $item['metadata']['metadata']['id'],
                'name' => $item['metadata']['metadata']['name'],
            ];
        }, $data['matches']);
    }

    /**
     * @param int $cloud_id
     * @param string $file_id
     * @param string $store_path
     * @return string
     */
    public function storeDropboxFile(int $cloud_id, string $file_id, string $store_path)
    {
        $curl = new \GuzzleHttp\Client();
        $response = $curl->post('https://content.dropboxapi.com/2/files/download', [
            'headers' => [
                'Authorization' => 'Bearer '.$this->getToken($cloud_id),
                'Dropbox-API-Arg' => json_encode([ 'path' => $file_id ]),
            ],
        ]);
        $content = $response->getBody()->getContents();
        Storage::put('tmp_import/'.$store_path, $content);
        return $store_path;
    }

    /**
     * @param int $cloud_id
     * @return string
     */
    protected function getToken(int $cloud_id)
    {
        $token = (new UserCloudRepository())->getCloudToken($cloud_id);
        return $this->accessToken($token, $cloud_id);
    }
}
<?php

namespace Vanguard\Modules\CloudBackup;


use Illuminate\Support\Facades\Storage;
use Vanguard\Repositories\Tablda\UserCloudRepository;

class OneDriveApiModule implements ApiModuleInterface
{
    /**
     * @param int $cloud_id
     * @return string
     */
    public function getCloudActivationUrl(int $cloud_id): string
    {
        $app_key = env('ONEDRIVE_APP_KEY');
        $redirect = env('ONEDRIVE_CLOUD_ACTIVATE_URI');
        $state = json_encode([
            'cloud_id' => $cloud_id
        ]);
        return 'https://login.microsoftonline.com/common/oauth2/v2.0/authorize?client_id=' . $app_key
            . '&redirect_uri=' . urlencode($redirect)
            . '&state=' . urlencode($state)
            . '&scope=' . urlencode(implode(' ', ['offline_access', 'Files.Read', 'Files.ReadWrite']))
            . '&response_mode=query'
            . '&response_type=code';
    }

    /**
     * @param int $cloud_id
     * @param string $extension
     * @return array : [ ['id'=>'h35raw3', 'name'=>'Table1'], ... ]
     */
    public function driveFindFiles(int $cloud_id, string $extension)
    {
        $curl = new \GuzzleHttp\Client();
        $response = $curl->get("https://graph.microsoft.com/v1.0/me/drive/root/search(q='." . $extension . "')", [
            'headers' => [
                'Authorization' => 'Bearer ' . $this->getToken($cloud_id),
            ],
        ]);
        $data = json_decode($response->getBody()->getContents(), true);

        return array_map(function ($item) {
            return [
                'id' => $item['id'],
                'name' => $item['name'],
            ];
        }, $data['value']);
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
     * @param string $code
     * @param bool $is_refresh
     * @return array
     */
    public function getTokenFromCode(string $code, bool $is_refresh = false): array
    {
        $curl = new \GuzzleHttp\Client();
        $response = $curl->post('https://login.microsoftonline.com/common/oauth2/v2.0/token', [
            'form_params' => [
                'code' => $code,
                'refresh_token' => $code,
                'grant_type' => $is_refresh ? 'refresh_token' : 'authorization_code',
                'scope' => implode(' ', ['offline_access', 'Files.Read', 'Files.ReadWrite.AppFolder']),
                'redirect_uri' => env('ONEDRIVE_CLOUD_ACTIVATE_URI'),
                'client_id' => env('ONEDRIVE_APP_KEY'),
                'client_secret' => env('ONEDRIVE_APP_SECRET'),
            ]
        ]);
        return json_decode($response->getBody()->getContents(), true);
    }

    /**
     * @param int $cloud_id
     * @param string $file_id
     * @param string $store_path
     * @return string
     */
    public function storeOneDriveFile(int $cloud_id, string $file_id, string $store_path)
    {
        $curl = new \GuzzleHttp\Client();
        $response = $curl->get('https://graph.microsoft.com/v1.0/me/drive/items/' . $file_id . '/content', [
            'headers' => [
                'Authorization' => 'Bearer ' . $this->getToken($cloud_id),
            ],
        ]);
        $content = $response->getBody()->getContents();
        Storage::put('tmp_import/' . $store_path, $content);
        return $store_path;
    }

    /**
     * @param string $access_token
     * @param string $target_folder
     * @param string $source_file
     * @return string
     */
    public function uploadFile(string $access_token, string $target_folder, string $source_file)
    {
        $curl = new \GuzzleHttp\Client();
        $response = $curl->put('https://graph.microsoft.com/v1.0/me/drive/root:' . $target_folder . ':/content', [
            'headers' => [
                'Authorization' => 'Bearer ' . $access_token,
                'Content-Type' => 'text/plain',
            ],
            'body' => file_get_contents($source_file),
        ]);
        return $response->getBody()->getContents();
    }
}
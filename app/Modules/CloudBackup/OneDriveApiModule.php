<?php

namespace Vanguard\Modules\CloudBackup;


use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Storage;
use Vanguard\Repositories\Tablda\UserCloudRepository;

class OneDriveApiModule implements ApiModuleInterface
{
    use WithAccessToken;

    /**
     * @var string
     */
    protected $type;

    /**
     * @param string $type
     */
    public function __construct(string $type = '')
    {
        $this->type = $type;
    }

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
                'scope' => implode(' ', ['offline_access', 'Files.Read', 'Files.ReadWrite']),
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
        Storage::put($store_path, $content);
        exec('chmod 666 ' . storage_path('app/'.$store_path));
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

    /**
     * @param int $cloud_id
     * @param string $shared_link
     * @return array
     */
    public function listFolderOrFile(int $cloud_id, string $shared_link): array
    {
        $data = $this->sharedMetadata($cloud_id, $shared_link);
        return isset($data['folder'])
            ? $data['children'] ?? []
            : [$data];
    }

    /**
     * @param int $cloud_id
     * @param string $shared_link
     * @return array
     */
    protected function sharedMetadata(int $cloud_id, string $shared_link): array
    {
        $shared_link = base64_encode($shared_link);
        $shared_link = rtrim($shared_link, '=');
        $shared_link = str_replace('/','_', $shared_link);
        $shared_link = "u!" . str_replace('+','-', $shared_link);

        $curl = new \GuzzleHttp\Client();
        $response = $curl->get('https://graph.microsoft.com/v1.0/shares/'.$shared_link.'/driveItem?$expand=children', [
            'headers' => [
                'Authorization' => 'Bearer ' . $this->getToken($cloud_id),
            ],
        ]);
        return json_decode($response->getBody()->getContents(), true);
    }

    /**
     * @param int $cloud_id
     * @param string $shared_link
     * @param string $filename
     * @param string $content
     * @return array
     */
    public function uploadToOwnedParent(int $cloud_id, string $shared_link, string $filename, string $content): array
    {
        $folder_meta = $this->sharedMetadata($cloud_id, $shared_link);
        $folder_id = isset($folder_meta['folder']) ? $folder_meta['id'] : '';

        if ($folder_id) {
            $curl = new \GuzzleHttp\Client();
            $response = $curl->put('https://graph.microsoft.com/v1.0/me/drive/items/' . $folder_id . ':/' . $filename . ':/content', [
                'headers' => [
                    'Authorization' => 'Bearer ' . $this->getToken($cloud_id),
                    'Content-Type' => 'text/plain',
                ],
                'body' => $content,
            ]);
            return json_decode($response->getBody()->getContents(), true);
        }

        return [];
    }

    /**
     * @param int $cloud_id
     * @param string $file_id
     * @return mixed
     */
    public function removeFile(int $cloud_id, string $file_id)
    {
        $curl = new \GuzzleHttp\Client();
        $response = $curl->delete("https://graph.microsoft.com/v1.0/me/drive/items/$file_id", [
            'headers' => [
                'Authorization' => 'Bearer ' . $this->getToken($cloud_id),
            ],
        ]);
        return json_decode($response->getBody()->getContents(), true);
    }

    /**
     * @param int $cloud_id
     * @param string $shared_link
     * @return bool
     */
    public function canEditFolder(int $cloud_id, string $shared_link): bool
    {
        try {
            $file = $this->uploadToOwnedParent($cloud_id, $shared_link, 'can_edit', 'check');
            $this->removeFile($cloud_id, $file['id']);
            return true;
        } catch (\Exception $e) {
            return false;
        }
    }

    /**
     * @param int $cloud_id
     * @param array $one_drive_item
     * @return string
     */
    public function fileLink(int $cloud_id, array $one_drive_item): string
    {
        $curl = new \GuzzleHttp\Client();
        $response = $curl->get("https://graph.microsoft.com/v1.0/me/drive/items/{$one_drive_item['id']}/thumbnails", [
            'headers' => [
                'Authorization' => 'Bearer ' . $this->getToken($cloud_id),
            ],
        ]);
        $data = json_decode($response->getBody()->getContents(), true);
        $thumbnail = Arr::first($data['value'] ?? []);
        return $thumbnail['large']['url'] ?? '';
    }
}
<?php

namespace Vanguard\Modules\CloudBackup;


use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Storage;
use Vanguard\Repositories\Tablda\UserCloudRepository;

class DropBoxApiModule implements ApiModuleInterface
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
        $app_key = $this->type == 'Dropbox' ? env('DBOX_APP_FETCH_KEY') : env('DBOX_APP_BACKUP_KEY');
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
            'client_id' => $this->type == 'Dropbox' ? env('DBOX_APP_FETCH_KEY') : env('DBOX_APP_BACKUP_KEY'),
            'client_secret' => $this->type == 'Dropbox' ? env('DBOX_APP_FETCH_SECRET') : env('DBOX_APP_BACKUP_SECRET'),
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
     * @param int $cloud_id
     * @param array $extensions
     * @return array : [ ['id'=>'h35raw3', 'name'=>'Table1'], ... ]
     */
    public function driveFindFiles(int $cloud_id, array $extensions)
    {
        $curl = new \GuzzleHttp\Client();
        $response = $curl->post('https://api.dropboxapi.com/2/files/search_v2', [
            'headers' => $this->jsonAuth($cloud_id),
            'json' => [
                'query' => '.'.Arr::first($extensions),
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
     * @param string $file_path
     * @param string $file_content
     * @return array
     */
    public function uploadFile(int $cloud_id, string $file_path, string $file_content)
    {
        $curl = new \GuzzleHttp\Client();
        $response = $curl->post('https://content.dropboxapi.com/2/files/upload', [
            'headers' => [
                'Authorization' => 'Bearer '.$this->getToken($cloud_id),
                'Dropbox-API-Arg' => json_encode([ 'path' => $file_path ]),
                'Content-Type' => 'application/octet-stream',
            ],
            'body' => $file_content,
        ]);
        return json_decode( $response->getBody()->getContents(), true );
    }

    /**
     * @param int $cloud_id
     * @param string $file_id
     * @return mixed
     */
    public function removeFile(int $cloud_id, string $file_id)
    {
        $curl = new \GuzzleHttp\Client();
        $response = $curl->post('https://api.dropboxapi.com/2/files/delete_v2', [
            'headers' => $this->jsonAuth($cloud_id),
            'json' => [
                'path' => $file_id,
            ]
        ]);
        return json_decode( $response->getBody()->getContents(), true );
    }

    /**
     * @param int $cloud_id
     * @param string $folder_path
     * @return bool
     */
    public function canEditFolder(int $cloud_id, string $folder_path): bool
    {
        try {
            $file = $this->uploadFile($cloud_id, $folder_path.'can_edit', 'check');
            $this->removeFile($cloud_id, $file['id']);
            return true;
        } catch (\Exception $e) {
            return false;
        }
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
        Storage::put($store_path, $content);
        exec('chmod 666 ' . storage_path('app/'.$store_path));
        return $store_path;
    }

    /**
     * @param int $cloud_id
     * @param string $shared_link
     * @return array
     */
    public function sharedFolderContent(int $cloud_id, string $shared_link): array
    {
        $curl = new \GuzzleHttp\Client();
        $response = $curl->post('https://api.dropboxapi.com/2/files/list_folder', [
            'headers' => $this->jsonAuth($cloud_id),
            'json' => [
                'path' => '',
                'shared_link' => [
                    'url' => $shared_link
                ],
            ]
        ]);
        $data = json_decode( $response->getBody()->getContents(), true );
        return $data['entries'] ?? [];
    }

    /**
     * @param int $cloud_id
     * @param string $shared_link
     * @return array
     */
    public function sharedLinkMetadata(int $cloud_id, string $shared_link): array
    {
        $curl = new \GuzzleHttp\Client();
        $response = $curl->post('https://api.dropboxapi.com/2/sharing/get_shared_link_metadata', [
            'headers' => $this->jsonAuth($cloud_id),
            'json' => [
                'url' => $shared_link
            ]
        ]);
        return json_decode( $response->getBody()->getContents(), true );
    }

    /**
     * @param int $cloud_id
     * @param string $dropbox_file_id
     * @return string
     */
    public function fileLink(int $cloud_id, string $dropbox_file_id): string
    {
        return $this->listSharedlink($cloud_id, $dropbox_file_id)
            ?: $this->createSharedlink($cloud_id, $dropbox_file_id)
                ?: $this->getTempLink($cloud_id, $dropbox_file_id);
    }

    /**
     * @param int $cloud_id
     * @param string $dropbox_file_id
     * @return string
     */
    protected function listSharedlink(int $cloud_id, string $dropbox_file_id): string
    {
        try {
            $curl = new \GuzzleHttp\Client();
            $response = $curl->post('https://api.dropboxapi.com/2/sharing/list_shared_links', [
                'headers' => $this->jsonAuth($cloud_id),
                'json' => [
                    'path' => $dropbox_file_id,
                ]
            ]);
            $data = json_decode($response->getBody()->getContents(), true);
        } catch (\Exception $e) {
            $data = [];
        }
        $url = '';
        foreach ($data['links']??[] as $link) {
            if (preg_match('/dropbox.com\/s\//i', $link['url']??'')
                    || preg_match('/dropbox.com\/scl\/fi\//i', $link['url']??'')) {
                $url = $link['url'];
            }
        }
        return preg_replace('/dropbox.com\/s\//i', 'dropbox.com/s/raw/', $url);//Note: is not working for new files
    }

    /**
     * @param int $cloud_id
     * @param string $dropbox_file_id
     * @return string
     */
    protected function createSharedlink(int $cloud_id, string $dropbox_file_id): string
    {
        try {
            $curl = new \GuzzleHttp\Client();
            $response = $curl->post('https://api.dropboxapi.com/2/sharing/create_shared_link_with_settings', [
                'headers' => $this->jsonAuth($cloud_id),
                'json' => [
                    'path' => $dropbox_file_id,
                ]
            ]);
            $data = json_decode( $response->getBody()->getContents(), true );
        } catch (\Exception $e) {
            $data = [];
        }
        return preg_replace('/dropbox.com\/s\//i', 'dropbox.com/s/raw/', $data['url']??'');//Note: is not working for new files
    }

    /**
     * @param int $cloud_id
     * @param string $dropbox_file_id
     * @return string
     */
    protected function getTempLink(int $cloud_id, string $dropbox_file_id): string
    {
        try {
            $curl = new \GuzzleHttp\Client();
            $response = $curl->post('https://api.dropboxapi.com/2/files/get_temporary_link', [
                'headers' => $this->jsonAuth($cloud_id),
                'json' => [
                    'path' => $dropbox_file_id,
                ]
            ]);
            $data = json_decode( $response->getBody()->getContents(), true );
        } catch (\Exception $e) {
            $data = [];
        }
        return $data['link']??'';
    }

    /**
     * @param int $cloud_id
     * @return string[]
     */
    protected function jsonAuth(int $cloud_id): array
    {
        return [
            'Authorization' => 'Bearer '.$this->getToken($cloud_id),
            'Content-Type' => 'application/json',
        ];
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
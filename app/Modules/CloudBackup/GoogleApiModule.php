<?php

namespace Vanguard\Modules\CloudBackup;


use Illuminate\Support\Facades\Storage;
use Vanguard\Repositories\Tablda\FileRepository;

class GoogleApiModule implements ApiModuleInterface
{
    /**
     * @param int $cloud_id
     * @return string
     */
    public function getCloudActivationUrl(int $cloud_id): string
    {
        $client = $this->getClient();
        $client->setScopes([
            \Google_Service_Sheets::SPREADSHEETS_READONLY,
            \Google_Service_Drive::DRIVE_FILE,
            \Google_Service_Drive::DRIVE_READONLY
        ]);
        $client->setState(json_encode([
            'cloud_id' => $cloud_id
        ]));
        $client->setAccessType('offline');
        $client->setPrompt('select_account consent');
        return $client->createAuthUrl();
    }

    /**
     * @return \Google_Client
     */
    private function getClient()
    {
        $client = new \Google_Client();
        $client->setClientId(env('GOOGLE_CLIENT_ID'));
        $client->setClientSecret(env('GOOGLE_CLIENT_SECRET'));
        $client->setRedirectUri(env('GOOGLE_CLOUD_ACTIVATE_URI'));
        return $client;
    }

    /**
     * @param string|null $token_json
     * @return \Google_Client
     */
    public function clientWithCredentialsOrPublic(string $token_json = null) {
        if ($token_json) {
            $client = $this->getClient();
            $client->setAccessToken( $this->accessToken($token_json) );
        } else {
            $client = new \Google_Client();
            $client->setDeveloperKey( env('GOOGLE_API_KEY') );
            $client->setRedirectUri( env('GOOGLE_CLOUD_ACTIVATE_URI') );
        }
        return $client;
    }

    /**
     * @param string $code
     * @param bool $is_refresh
     * @return array
     */
    public function getTokenFromCode(string $code, bool $is_refresh = false): array
    {
        $client = $this->getClient();
        return $client->fetchAccessTokenWithAuthCode($code);
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
        $result = [];
        $token = json_decode($token_json, true);
        if ($token) {
            $client = $this->getClient();
            $client->setAccessToken($token);

            if ($client->isAccessTokenExpired()) {

                if ($client->getRefreshToken()) {
                    try {
                        $client->fetchAccessTokenWithRefreshToken($client->getRefreshToken());
                        $result = $client->getAccessToken();
                    } catch (\Exception $e) {
                    }
                }

            } else {
                $result = $client->getAccessToken();
            }

        }
        return json_encode($result);
    }

    /**
     * @param string $access_token
     * @param string $name_path
     * @param string $source_path
     * @return \Google_Service_Drive_DriveFile | null
     */
    public function saveFileToDisk(string $access_token, string $name_path, string $source_path)
    {
        $folders = explode(DIRECTORY_SEPARATOR, $name_path);
        $folders = array_filter($folders);
        $name = array_pop($folders);

        $client = $this->getClient();
        $client->setAccessToken($access_token);
        $driveService = new \Google_Service_Drive($client);

        $parentId = null;
        foreach ($folders as $folder) {
            //Search Google Folder
            $present = $this->findFile($driveService, $folder, $parentId, true);
            if (!$present) {
                //Create Google Folder
                $metaData = $this->getMetaFile($folder, $parentId, true);
                $present = $driveService->files->create($metaData, ['fields' => 'id, parents']);
            }
            $parentId = $present->id;
        }

        $metaData = $this->getMetaFile($name, $parentId);
        $content = [
            'data' => file_get_contents($source_path),
            'fields' => 'id, parents'
        ];
        $present = $this->findFile($driveService, $name, $parentId);
        if ($present && $present->id) {
            $info = new \SplFileInfo($source_path);
            $drive = new \Google_Service_Drive_DriveFile(['name' => $name]);
            $result = !in_array(strtolower($info->getExtension()), FileRepository::$img_extensions)
                ? $driveService->files->update($present->id, $drive, ['data' => file_get_contents($source_path)])
                : $present;
        } else {
            $result = $driveService->files->create($metaData, $content);
        }
        return $result;
    }

    /**
     * @param $driveService
     * @param string $name
     * @param null $parentId
     * @param bool $is_folder
     * @return null
     */
    private function findFile($driveService, string $name, $parentId = null, $is_folder = false) {
        $query = "name = '" . $name . "'";
        if ($parentId) {
            $query .= " and '".$parentId."' in parents";
        }
        if ($is_folder) {
            $query .= " and mimeType = 'application/vnd.google-apps.folder'";
        }
        return $driveService->files
                ->listFiles(['q' => $query])
                ->getFiles()[0] ?? null;
    }

    /**
     * @param $name
     * @param null $parentId
     * @param bool $is_folder
     * @return \Google_Service_Drive_DriveFile
     */
    private function getMetaFile($name, $parentId = null, $is_folder = false)
    {
        $metaData = new \Google_Service_Drive_DriveFile(['name' => $name]);
        if ($parentId) {
            $metaData->setParents([$parentId]);
        }
        if ($is_folder) {
            $metaData->setMimeType('application/vnd.google-apps.folder');
        }
        return $metaData;
    }

    /**
     * @param string $token_json
     * @param string $sheet_id
     * @return array : ['Sheet1','Sheet2',...]
     */
    public function getSheetsFromTable(string $token_json, string $sheet_id)
    {
        $client = $this->clientWithCredentialsOrPublic($token_json);
        $service = new \Google_Service_Sheets($client);
        $table = $service->spreadsheets->get($sheet_id);
        $result = [];
        foreach ($table->sheets as $sheet) {
            $result[] = $sheet->properties->title;
        }
        return $result;
    }

    /**
     * @param string $token_json
     * @param string $file_id
     * @param string $store_path
     * @param string $export
     * @return string
     */
    public function storeGoogleFile(string $token_json, string $file_id, string $store_path, string $export = '')
    {
        $client = $this->clientWithCredentialsOrPublic($token_json);
        $service = new \Google_Service_Drive($client);
        $content = $export
            ? $service->files->export($file_id, $export, ["alt" => "media"])
            : $service->files->get($file_id, ["alt" => "media"]);

        Storage::put('tmp_import/'.$store_path, '');
        $handle = fopen(storage_path('app/tmp_import/'.$store_path), "w+");
        while (!$content->getBody()->eof()) {
            fwrite($handle, $content->getBody()->read(1024));
        }
        fclose($handle);
        return $store_path;
    }

    /**
     * @param string $token_json
     * @param string $mime
     * @return array : [ ['id'=>'h35raw3', 'name'=>'Table1'], ... ]
     */
    public function driveFindFiles(string $token_json, string $mime = '')
    {
        $mime = $mime ?: 'application/vnd.google-apps.spreadsheet';
        $client = $this->clientWithCredentialsOrPublic($token_json);
        $driveService = new \Google_Service_Drive($client);
        $file_list = $driveService->files
                ->listFiles(['q' => "mimeType='".$mime."'"])
                ->getFiles();

        return array_map(function ($item) {
            return [
                'id' => $item->id,
                'name' => $item->name,
            ];
        }, $file_list);
    }
}
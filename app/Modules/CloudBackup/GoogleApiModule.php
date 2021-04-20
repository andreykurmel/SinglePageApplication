<?php

namespace Vanguard\Modules\CloudBackup;


class GoogleApiModule implements ApiModuleInterface
{
    /**
     * @param int $cloud_id
     * @return string
     */
    public function getCloudActivationUrl(int $cloud_id)
    {
        $client = $this->getClient();
        $client->setScopes([\Google_Service_Sheets::SPREADSHEETS_READONLY, \Google_Service_Drive::DRIVE_FILE]);
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
     * @return array
     */
    public function getTokenFromCode(string $code)
    {
        $client = $this->getClient();
        return $client->fetchAccessTokenWithAuthCode($code);
    }

    /**
     * Get Access Token.
     *
     * @param string $token_json
     * @return array
     */
    public function accessToken(string $token_json)
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
        return $result;
    }

    /**
     * @param array $access_token
     * @param string $name_path
     * @param string $source_path
     * @return \Google_Service_Drive_DriveFile | null
     */
    public function saveFileToDisk(array $access_token, string $name_path, string $source_path)
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

        if (!$this->findFile($driveService, $name, $parentId)) {
            $metaData = $this->getMetaFile($name, $parentId);
            $result = $driveService->files->create($metaData, [
                'data' => file_get_contents($source_path),
                'fields' => 'id, parents'
            ]);
            return $result;
        } else {
            return null;
        }
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
}
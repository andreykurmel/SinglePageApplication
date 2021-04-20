<?php

namespace Vanguard\Modules\CloudBackup;


use Vanguard\Models\User\UserCloud;
use Vanguard\Repositories\Tablda\UserCloudRepository;

class GoogleStrategy implements BackupStrategy
{
    private $access_token;

    /**
     * GoogleStrategy constructor.
     * @param UserCloud $cloud
     * @throws \Exception
     */
    public function __construct(UserCloud $cloud)
    {
        $this->access_token = (new GoogleApiModule())->accessToken($cloud->gettoken());

        if (!$this->access_token) {
            (new UserCloudRepository())->setInactiveCloud($cloud);
            throw new \Exception('GoogleStrategy:No Access Token');
        }
    }

    /**
     * @param string $source_path
     * @param string $file_name
     * @param string $target_path
     * @return \Google_Service_Drive_DriveFile|null
     */
    public function upload(string $source_path, string $file_name, string $target_path)
    {
        $source = preg_replace('#\/+#i','/', $source_path.'/'.$file_name);
        $target = preg_replace('#\/+#i','/', $target_path.'/'.$file_name);
        return (new GoogleApiModule())->saveFileToDisk($this->access_token, $target, $source);
    }
}
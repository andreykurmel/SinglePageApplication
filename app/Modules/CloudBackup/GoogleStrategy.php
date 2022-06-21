<?php

namespace Vanguard\Modules\CloudBackup;


use Vanguard\Models\User\UserCloud;
use Vanguard\Repositories\Tablda\UserCloudRepository;

class GoogleStrategy implements BackupStrategy
{
    /**
     * @var string
     */
    protected $access_token;

    /**
     * GoogleStrategy constructor.
     * @param UserCloud $cloud
     * @throws \Exception
     */
    public function __construct(UserCloud $cloud)
    {
        $this->access_token = (new GoogleApiModule())->accessToken($cloud->gettoken(), $cloud->id);

        if (!$this->access_token) {
            (new UserCloudRepository())->setInactiveCloud($cloud);
            throw new \Exception('GoogleStrategy:No Access Token');
        }
    }

    /**
     * @param string $source_filepath
     * @param string $target_filepath
     * @return string
     */
    public function upload(string $source_filepath, string $target_filepath)
    {
        $source_file = preg_replace('#\/+#i','/', $source_filepath);
        $target_folder = preg_replace('#\/+#i','/', $target_filepath);
        return (new GoogleApiModule())->saveFileToDisk($this->access_token, $target_folder, $source_file);
    }
}
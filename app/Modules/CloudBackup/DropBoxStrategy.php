<?php

namespace Vanguard\Modules\CloudBackup;


use Vanguard\Models\User\UserCloud;
use Vanguard\Repositories\Tablda\UserCloudRepository;

class DropBoxStrategy implements BackupStrategy
{
    /**
     * DropBoxStrategy constructor.
     * @param UserCloud $cloud
     * @throws \Exception
     */
    public function __construct(UserCloud $cloud)
    {
        $access_token = (new DropBoxApiModule())->accessToken($cloud->gettoken());

        if (!$access_token) {
            (new UserCloudRepository())->setInactiveCloud($cloud);
            throw new \Exception('GoogleStrategy:No Access Token');
        }

        exec('echo "OAUTH_ACCESS_TOKEN='.$access_token.'" > /var/www/.dropbox_uploader');
    }

    /**
     * @param string $source_path
     * @param string $file_name
     * @param string $target_path
     * @return string
     */
    public function upload(string $source_path, string $file_name, string $target_path)
    {
        $source_file = preg_replace('#\/+#i','/', '/'.$source_path.'/'.$file_name);
        $target_folder = preg_replace('#\/+#i','/', '/'.$target_path.'/');
        return exec(env('DBOX_UPLOADER_FILE').' upload '.$source_file.' '.$target_folder.'');
    }
}
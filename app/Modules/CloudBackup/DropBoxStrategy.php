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
        $access_token = (new DropBoxApiModule($cloud->cloud))->accessToken($cloud->gettoken(), $cloud->id);

        if (!$access_token) {
            (new UserCloudRepository())->setInactiveCloud($cloud);
            throw new \Exception('DropBoxStrategy:No Access Token');
        }

        exec('echo "OAUTH_ACCESS_TOKEN='.$access_token.'" > /var/www/.dropbox_uploader');
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
        return exec(env('DBOX_UPLOADER_FILE').' upload '.$source_file.' '.$target_folder.'');
    }
}
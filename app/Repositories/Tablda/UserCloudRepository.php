<?php

namespace Vanguard\Repositories\Tablda;


use Vanguard\Classes\TabldaEncrypter;
use Vanguard\Models\User\UserCloud;
use Vanguard\Modules\CloudBackup\ApiModuleInterface;
use Vanguard\Modules\CloudBackup\DropBoxApiModule;
use Vanguard\Modules\CloudBackup\GoogleApiModule;
use Vanguard\Modules\CloudBackup\OneDriveApiModule;
use Vanguard\Modules\Jira\JiraApiModule;
use Vanguard\Modules\Salesforce\SalesforceApiModule;
use Vanguard\Services\Tablda\HelperService;
use Vanguard\User;

class UserCloudRepository
{
    protected $service;

    /**
     * UserCloudRepository constructor.
     */
    public function __construct()
    {
        $this->service = new HelperService();
    }

    /**
     * Get Cloud
     *
     * @param int $cloud_id
     * @return UserCloud
     */
    public function getCloud(int $cloud_id)
    {
        return UserCloud::where('id', $cloud_id)
            ->first();
    }

    /**
     * Add UserCloud.
     *
     * @param array $data
     * @return mixed
     */
    public function addUserCloud(array $data)
    {
        $data_filter = collect($data)->only([
            'user_id',
            'name',
            'cloud',
            'root_folder',
        ])->toArray();

        $cloud = UserCloud::create( array_merge($data_filter, $this->service->getModified(), $this->service->getCreated()) );

        $this->setInactiveCloud($cloud);

        return $cloud;
    }

    /**
     * setInactiveCloud.
     *
     * @param UserCloud $cloud
     */
    public function setInactiveCloud(UserCloud $cloud)
    {
        $module = $this->apiModuleResolve($cloud->cloud);
        $cloud->update([
            'msg_to_user' => $module->getCloudActivationUrl($cloud->id),
            'token_json' => null,
            'extra_params' => null,
        ]);
    }

    /**
     * @param string $cloud
     * @return ApiModuleInterface
     * @throws \Exception
     */
    private function apiModuleResolve(string $cloud) {
        switch ($cloud) {
            case 'Google': $class = GoogleApiModule::class;
                break;
            case 'Dropbox': $class = DropBoxApiModule::class;
                break;
            case 'OneDrive': $class = OneDriveApiModule::class;
                break;
            case 'Jira': $class = JiraApiModule::class;
                break;
            case 'Salesforce': $class = SalesforceApiModule::class;
                break;
            default: throw new \Exception('UserCloud::apiModuleResolve - Undefined strategy type!');
        }
        return new $class($cloud);
    }

    /**
     * Update UserCloud
     *
     * @param int $user_cloud_id
     * @param array $data
     * @return array
     */
    public function updateUserCloud($user_cloud_id, array $data)
    {
        $data_filter = collect($data)->only([
            'user_id',
            'name',
            'root_folder',
            'token_json',
            'msg_to_user',
            'extra_params',
        ])->toArray();

        if (!empty($data_filter['token_json'])) {
            $data_filter['token_json'] = TabldaEncrypter::encrypt($data_filter['token_json']);
        }
        if (is_array($data_filter['extra_params'] ?? null)) {
            $data_filter['extra_params'] = json_encode($data_filter['extra_params']);
        }

        return UserCloud::where('id', $user_cloud_id)
            ->update( array_merge($data_filter, $this->service->getModified()) );
    }

    /**
     * Delete UserCloud
     *
     * @param int $user_cloud_id
     * @return mixed
     */
    public function deleteUserCloud($user_cloud_id)
    {
        return UserCloud::where('id', $user_cloud_id)
            ->where('user_id', auth()->id())
            ->delete();
    }

    /**
     * @param int $cloud_id
     * @param string $code
     * @return int
     */
    public function setCloudToken(int $cloud_id, string $code)
    {
        $cloud = UserCloud::where('id', $cloud_id)
            ->where('user_id', '=', auth()->id())
            ->first();

        if ($cloud) {
            $module = $this->apiModuleResolve($cloud->cloud);
            $token = $module->getTokenFromCode($code);

            $this->updateUserCloud($cloud_id, [
                'msg_to_user' => null,
                'token_json' => json_encode($token),
            ]);
            if ($cloud->cloud == 'Jira') {
                $module->connectAccessibleResource($cloud->id);
            }

            return 1;
        } else {
            return 0;
        }
    }

    /**
     * @param User $user
     * @param string $type
     * @return UserCloud|null
     */
    public function firstActiveCloud(User $user, string $type = '')
    {
        $sql = UserCloud::where('user_id', '=', $user->id)
            ->whereNotNull('token_json');
        if ($type) {
            $sql->where('cloud', '=', $type);
        }
        return $sql->first();
    }

    /**
     * @param int|null $cloud_id
     * @param string $type
     * @return string|null
     */
    public function getCloudToken(int $cloud_id = null, string $type = '')
    {
        $cloud = UserCloud::where('id', '=', $cloud_id);
        if ($type) {
            $cloud->where('cloud', '=', $type);
        }
        $cloud = $cloud->first();
        return $cloud ? $cloud->gettoken() : '';
    }
}
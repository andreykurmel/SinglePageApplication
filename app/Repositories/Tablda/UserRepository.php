<?php

namespace Vanguard\Repositories\Tablda;


use Ramsey\Uuid\Uuid;
use Vanguard\Models\User\Addon;
use Vanguard\Models\User\UserGroup;
use Vanguard\Models\User\UserSubscription;
use Vanguard\Services\Tablda\HelperService;
use Vanguard\User;

class UserRepository
{
    protected $service;

    /**
     * UserRepository constructor.
     */
    public function __construct()
    {
        $this->service = new HelperService();
    }

    /**
     * @param string $user_id
     * @return mixed
     */
    public function getUserOrGroupInfo(string $user_id) {
        if ($user_id[0] == '_') {
            $ug = UserGroup::where('id', substr($user_id, 1))->first();
            return $ug
                ? [
                    'id' => $ug->id,
                    'first_name' => $ug->name,
                    'last_name' => '(Group)',
                ]
                : null;
        } else {
            return User::where('id', $user_id)
                ->select(['id','email','username','first_name','last_name','avatar'])
                ->first();
        }
    }

    /**
     * @param array $usr_ids
     * @return mixed
     */
    public function findUsersInfo(array $usr_ids)
    {
        return User::whereIn('id', $usr_ids)
            ->select(['id','email','username','first_name','last_name','avatar'])
            ->get();
    }

    /**
     * Users Search by Key
     *
     * @param $key
     * @param array $ids
     * @param null $request_field
     * @return mixed
     */
    public function searchUsers($key, $ids = [], $request_field = null)
    {
        if (count($ids)) {
            $query = User::whereIn('id', $ids)->where(function ($q) use ($key) {
                $q->where('username', 'LIKE', '%'.$key.'%');
                $q->orWhere('email', 'LIKE', '%'.$key.'%');
                $q->orWhere('first_name', 'LIKE', '%'.$key.'%');
                $q->orWhere('last_name', 'LIKE', '%'.$key.'%');
            });
        } else {
            $query = User::where('username', 'LIKE', '%'.$key.'%')
                ->orWhere('email', 'LIKE', '%'.$key.'%')
                ->orWhere('first_name', 'LIKE', '%'.$key.'%')
                ->orWhere('last_name', 'LIKE', '%'.$key.'%');
        }

        $fields = ['id','username','first_name','last_name'];
        if ($request_field) {
            $fields[] = $request_field;
        }

        return $query->select($fields)
            ->limit(5)
            ->get();
    }

    /**
     * Get User only with names.
     *
     * @param $user_id
     * @return mixed
     */
    public function getWithNames($user_id) {
        return User::where('id', '=', $user_id)->select( $this->service->onlyNames(false) )->first();
    }

    /**
     * Get User only with names.
     *
     * @param $user_id
     * @return mixed
     */
    public function getById($user_id) {
        return User::where('id', '=', $user_id)->first();
    }

    /**
     * Get Users.
     *
     * @param array $users_ids
     * @return mixed
     */
    public function getMass(array $users_ids) {
        return User::whereIn('id', $users_ids)->get();
    }

    /**
     * Create Subscription
     *
     * @param array $data: [
     *  +user_id: int,
     *  +active: 1|0,
     *  +plan_code: string,
     *  -left_days: int,
     *  -total_days: int,
     *  -cost_days: float,
     * ]
     * @return mixed
     */
    public function createSubscription(array $data) {
        return UserSubscription::create( $this->service->delSystemFields($data) );
    }

    /**
     * Get Subscription
     *
     * @param $id
     * @return mixed
     */
    public function getSubscription($id) {
        return UserSubscription::where('id', $id)->first();
    }

    /**
     * Add Addon to UserSubscription.
     *
     * @param UserSubscription $subscription
     * @param $addon_code
     * @return int
     */
    public function addAddon(UserSubscription $subscription, $addon_code)
    {
        $addon = Addon::where('code', $addon_code)->first();
        if (! $subscription->_addons()->where('addon_id', $addon->id)->count()) {
            $subscription->_addons()->attach($addon->id);
        }
        return 1;
    }

    /**
     * Del Addon from UserSubscription.
     *
     * @param UserSubscription $subscription
     * @param $addon_code
     * @return int
     */
    public function delAddon(UserSubscription $subscription, $addon_code)
    {
        $addon = Addon::where('code', $addon_code)->first();
        return $subscription->_addons()->detach($addon->id);
    }

    /**
     * Delete All Addons from UserSubscription.
     *
     * @param UserSubscription $subscription
     * @return int
     */
    public function deleteAllAddons(UserSubscription $subscription)
    {
        return $subscription->_addons()->detach();
    }

    /**
     * @param int $user_id
     * @return mixed
     */
    public function newMenutreeHash(int $user_id)
    {
        return User::where('id', $user_id)->update([
            'memutree_hash' => Uuid::uuid4(),
        ]);
    }
}
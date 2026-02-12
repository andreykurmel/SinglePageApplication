<?php

namespace Vanguard\Repositories\Tablda;


use Carbon\Carbon;
use Exception;
use Illuminate\Support\Str;
use Ramsey\Uuid\Uuid;
use Vanguard\Mail\EmailWithSettings;
use Vanguard\Models\User\Addon;
use Vanguard\Models\User\UserGroup;
use Vanguard\Models\User\UserSubscription;
use Vanguard\Services\Tablda\HelperService;
use Vanguard\Services\Tablda\TableAlertService;
use Vanguard\Services\Tablda\TableDataService;
use Vanguard\Services\Tablda\TableService;
use Vanguard\Support\Enum\UserStatus;
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
     * @param int $id
     * @param array $fields
     * @return bool|int
     */
    public function update(int $id, array $fields)
    {
        return User::where('id', '=', $id)->update($fields);
    }

    /**
     * @param string $user_id
     * @return mixed
     */
    public function getUserOrGroupInfo(string $user_id)
    {
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
                ->select(['id', 'email', 'username', 'first_name', 'last_name', 'avatar'])
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
            ->select(['id', 'email', 'username', 'first_name', 'last_name', 'avatar'])
            ->get();
    }

    /**
     * @param array $usr_emails
     * @param bool $first
     * @return User[]|User
     */
    public function getByEmails(array $usr_emails, bool $first = false)
    {
        $array = User::whereIn('email', $usr_emails)
            ->select(['id', 'email', 'username', 'first_name', 'last_name', 'avatar'])
            ->get();
        return $first ? $array->first() : $array;
    }

    /**
     * @param int|null $id
     * @return string
     */
    public function userNameById(int $id = null): string
    {
        $usr = $id ? User::where('id', '=', $id)->first() : null;
        return $usr ? $usr->full_name() : '';
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
                $q->where('email', '=', $key);
//                $q->where('username', 'LIKE', '%' . $key . '%');
//                $q->orWhere('email', 'LIKE', '%' . $key . '%');
//                $q->orWhere('first_name', 'LIKE', '%' . $key . '%');
//                $q->orWhere('last_name', 'LIKE', '%' . $key . '%');
            });
        } else {
            $email_domain = HelperService::usrEmailDomain();
            $query = User::where('email', '=', $key); //direct email
            if ($email_domain) {
                $query->orWhere(function ($sub) use ($email_domain, $key) { // search just in the same subdomain
                    $sub->where('email', 'LIKE', '%@' . $email_domain);
                    $sub->where(function ($insub) use ($key) {
                        $insub->orWhere('username', 'LIKE', '%' . $key . '%');
                        $insub->orWhere('first_name', 'LIKE', '%' . $key . '%');
                        $insub->orWhere('last_name', 'LIKE', '%' . $key . '%');
                    });
                });
            }
        }

        $fields = ['id', 'username', 'first_name', 'last_name', 'email'];
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
    public function getWithNames($user_id)
    {
        return User::where('id', '=', $user_id)->select($this->service->onlyNames(false))->first();
    }

    /**
     * Get User only with names.
     *
     * @param $user_id
     * @return mixed
     */
    public function getById($user_id)
    {
        return User::where('id', '=', $user_id)->first();
    }

    /**
     * Get Users.
     *
     * @param array $users_ids
     * @return mixed
     */
    public function getMass(array $users_ids)
    {
        return User::whereIn('id', $users_ids)->get();
    }

    /**
     * Create Subscription
     *
     * @param array $data : [
     *  +user_id: int,
     *  +active: 1|0,
     *  +plan_code: string,
     *  -left_days: int,
     *  -total_days: int,
     *  -cost_days: float,
     * ]
     * @return mixed
     */
    public function createSubscription(array $data)
    {
        $subscription = UserSubscription::create($this->service->delSystemFields($data));

        //check ANA addon
        $subscription_table = (new TableRepository())->getTableByDB('user_subscriptions');
        $datas = (new TableService())->getSubscriptions([
            'table_id' => $subscription_table->id,
            'page' => 1,
            'rows_per_page' => 0,
            'row_id' => $subscription->id
        ], $subscription->user_id);
        $special = ['user_id' => $subscription_table->user_id, 'permission_id' => null];
        (new TableAlertService())->checkAndSendNotifArray($subscription_table, 'added', $datas['rows'], [], $special);

        return $subscription;
    }

    /**
     * Get Subscription
     *
     * @param $id
     * @return mixed
     */
    public function getSubscription($id)
    {
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
        if (!$subscription->_addons()->where('addon_id', $addon->id)->count()) {
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
     * @param $user_ids
     * @return array
     * @throws Exception
     */
    public function newMenutreeHash($user_ids): array
    {
        if (is_array($user_ids)) {
            $sql = User::whereIn('id', $user_ids);
        } else {
            $sql = User::where('id', $user_ids);
        }
        $arr = [
            'memutree_hash' => Uuid::uuid4(),
        ];
        $sql->update($arr);
        return $arr;
    }

    /**
     * @param $user_ids
     * @return array
     * @throws Exception
     */
    public function getMenutreeHash($user_ids): array
    {
        if (is_array($user_ids)) {
            $sql = User::whereIn('id', $user_ids);
        } else {
            $sql = User::where('id', $user_ids);
        }
        return $sql->get(['memutree_hash'])->toArray();
    }

    /**
     * @return \Illuminate\Database\Eloquent\Collection|User[]
     */
    public function getUnconfirmed()
    {
        return User::where('status', '=', UserStatus::UNCONFIRMED)
            ->where('created_at', '<', Carbon::yesterday()->endOfDay())
            ->where('created_at', '>', Carbon::now()->subDays(5)->startOfDay())
            ->get();
    }

    /**
     * @return \Illuminate\Database\Eloquent\Collection|User[]
     */
    public function getUnconfirmedWarning()
    {
        return User::where('status', '=', UserStatus::UNCONFIRMED)
            ->where('created_at', '<', Carbon::now()->subDays(6)->endOfDay())
            ->where('created_at', '>', Carbon::now()->subDays(7)->startOfDay())
            ->get();
    }

    /**
     * @return bool|null
     * @throws Exception
     */
    public function deleteOldUnconfirmed()
    {
        return User::where('status', '=', UserStatus::UNCONFIRMED)
            ->where('created_at', '<', Carbon::now()->subDays(7)->startOfDay())
            ->delete();
    }

    /**
     * @param float $amount
     * @return mixed
     */
    public function setAdminBalance(float $amount)
    {
        return User::where('id', '=', 1)
            ->update(['avail_credit' => $amount]);
    }

    /**
     * @param User $user
     * @param string $warning
     * @return void
     */
    public function sendConfirmationEmail(User $user, string $warning = '')
    {
        $token = Str::random(60);
        $user->update(['confirmation_token' => $token]);

        $params = [
            'from.account' => 'noreply',
            'subject' => sprintf("%s", trans('app.registration_confirmation')),
            //'subject' => sprintf("[%s] %s", settings('app_name'), trans('app.registration_confirmation')),
            'to.address' => $user->email
        ];
        $data = [
            'greeting' => 'Hello, ' . $user->username . ':',
            'mail_action' => [
                'text' => trans('app.confirm_email'),
                'url' => route('register.confirm-email', $token),
            ],
            'user' => $user,
            'main_message' => $warning
                ? trans('app.confirm_email_on_link_below_warning', ['warning' => $warning])
                : trans('app.confirm_email_on_link_below'),
        ];

        $mailer = new EmailWithSettings('confirm_code_to_user', $user->email);
        $mailer->queue($params, $data);
    }
}
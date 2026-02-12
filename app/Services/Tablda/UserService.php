<?php

namespace Vanguard\Services\Tablda;


use Carbon\Carbon;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use PayPal\Api\Amount;
use PayPal\Api\CreditCard;
use PayPal\Api\CreditCardToken;
use PayPal\Api\FundingInstrument;
use PayPal\Api\Payer;
use PayPal\Api\Payment;
use PayPal\Api\Transaction;
use PayPal\Auth\OAuthTokenCredential;
use PayPal\Rest\ApiContext;
use PayPalCheckoutSdk\Core\PayPalHttpClient;
use PayPalCheckoutSdk\Core\ProductionEnvironment;
use PayPalCheckoutSdk\Core\SandboxEnvironment;
use PayPalCheckoutSdk\Orders\OrdersCaptureRequest;
use PayPalCheckoutSdk\Orders\OrdersCreateRequest;
use PayPalCheckoutSdk\Orders\OrdersGetRequest;
use Vanguard\Jobs\SendInvitations;
use Vanguard\Models\AppSetting;
use Vanguard\Models\AppTheme;
use Vanguard\Models\Import;
use Vanguard\Models\Table\Table;
use Vanguard\Models\User\Addon;
use Vanguard\Models\User\UserCard;
use Vanguard\Models\User\UserGroup;
use Vanguard\Models\User\UserSubscription;
use Vanguard\Models\User\UserSubscription2Addons;
use Vanguard\Repositories\Tablda\DDLRepository;
use Vanguard\Repositories\Tablda\TableData\TableDataRepository;
use Vanguard\Repositories\Tablda\TableRepository;
use Vanguard\Repositories\Tablda\UserRepository;
use Vanguard\Services\Tablda\Permissions\TablePermissionService;
use Vanguard\User;

class UserService
{
    protected $service;
    protected $groupService;
    protected $tableRepository;
    protected $userRepository;
    protected $planService;
    protected $ddlRepo;
    protected $tableDataRepo;

    protected $allAddonsIds;

    /**
     * UserService constructor.
     */
    public function __construct()
    {
        $this->service = new HelperService();
        $this->groupService = new TablePermissionService();
        $this->tableRepository = new TableRepository();
        $this->userRepository = new UserRepository();
        $this->planService = new PlanService();
        $this->ddlRepo = new DDLRepository();
        $this->tableDataRepo = new TableDataRepository();

        $this->allAddonsIds = Addon::query()->select(['id'])->get()->pluck('id');
    }

    /**
     * @param float $amount
     * @return mixed
     */
    public function setAdminBalance(float $amount)
    {
        return $this->userRepository->setAdminBalance($amount);
    }

    /**
     * Check and set default Theme.
     *
     * @param User $user
     */
    public function defaultTheme(User $user)
    {
        if (!$user->app_theme_id) {
            $appTh = AppTheme::where('obj_type', 'system')->first();
            if ($appTh) {
                User::where('id', $user->id)->update([
                    'app_theme_id' => $appTh->id
                ]);
                $user->app_theme_id = $appTh->id;
            }
        }
    }

    /**
     * If registrant is referred by user -> award referal in $10
     *
     * @param string $email
     * @param string $hash
     */
    public function inviteAccepted(string $email, string $hash)
    {
        if ($user = User::where('personal_hash', '=', $hash)->first()) {

            $invit = $this->getInvitationOrCreate($user, $email);

            if (!$invit) {
                return;
            }

            $invit->date_accept = Carbon::now();
            $invit->save();
        }
    }

    /**
     * If registrant is referred by user -> award referal in $10
     *
     * @param string $email
     * @param string $hash
     */
    public function awardReferral(string $email, string $hash)
    {
        if ($user = User::where('personal_hash', '=', $hash)->first()) {

            $invit = $this->getInvitationOrCreate($user, $email);

            if (!$invit || $invit->status == 2) {
                return;
            }

            $invit->status = 2;
            $invit->date_confirm = Carbon::now();
            $invit->date_accept = $invit->date_accept ?: Carbon::now();
            $invit->save();

            $user->invitations_reward += $invit->rewarded;
            $user->save();

            $this->rewardFromAdminCredit($user, $invit->rewarded);
        }
    }

    /**
     * Get Available Features for selected User.
     *
     * @param User $user
     * @return mixed
     */
    public function getAvailableFeatures(User $user)
    {
        $this->checkAndSetPlan($user);
        return $user->_available_features;
    }

    /**
     * Check that User have Plan and PlanFeatures.
     * Set 'Basic' Tariff Plan for User if they don't have Plan.
     *
     * @param User $user
     * @return void
     */
    public function checkAndSetPlan(User $user)
    {
        if (empty($user->_subscription)) {
            $this->userRepository->createSubscription([
                'user_id' => $user->id,
                'active' => 1,
                'plan_code' => 'basic'
            ]);
            $user->load('_subscription');
        }
        if (!$user->plan_feature_id) {
            $this->setPlanFeature($user);
        }
    }

    /**
     * Set User's Plan Feature
     *
     * @param User $user
     * @return void
     */
    public function setPlanFeature(User $user)
    {
        //remove old features
        if ($user->plan_feature_id) {
            $this->planService->removePlanFeaturesForUser($user->id);
        }

        $new_plan = $this->planService->getPlanByCode($user->_subscription->plan_code);
        $plan_features = $this->planService->copyPlanFeaturesForUser($new_plan, $user->id);
        $user->plan_feature_id = $plan_features->id;
        $user->save();
    }

    /**
     * Get Subscription for selected User.
     *
     * @param User $user
     * @return mixed
     */
    public function getSubscription(User $user)
    {
        $this->checkAndSetPlan($user);
        return $user->_subscription;
    }

    /**
     * @param string $user_id
     * @return object
     */
    public function getUserOrGroupInfo(string $user_id) {
        return $this->userRepository->getUserOrGroupInfo($user_id);
    }

    /**
     * @param array $user_ids
     * @return mixed
     */
    public function findUsersInfo(array $user_ids) {
        return $this->userRepository->findUsersInfo($user_ids);
    }

    /**
     * @param $q
     * @param $table_id
     * @param $request_field
     * @param $extras
     * @return array
     */
    public function searchUsers($q, $table_id = null, $request_field = null, $extras = null)
    {
        $users_ids = [];
        if ($table_id) {
            $users_ids = User::whereHas('_tables', function ($t) use ($table_id) {
                $t->where('id', $table_id);
            })
                ->orWhereHas('_member_of_groups', function ($m) use ($table_id) {
                    $m->whereHas('_table_permissions', function ($t) use ($table_id) {
                        $t->where('table_permissions.table_id', $table_id);
                        $t->where('user_groups_2_table_permissions.is_active', 1);
                    });
                })
                ->select('users.id')
                ->get()
                ->pluck('id');
        }
        $users = $this->userRepository->searchUsers($q, $users_ids, $request_field);

        $res = [];
        foreach ($users as $usr) {
            //check that property is present
            $val_field = ($request_field && $usr->{$request_field} ? $request_field : null);

            $extra_field = is_array($extras) && !empty($extras['show_field'])
                ? $extras['show_field']
                : $request_field;

            $res[] = [
                'id' => ($val_field ? $usr->{$val_field} : $usr->id),
                'text' => ($usr->first_name ? $usr->first_name . " " . $usr->last_name : $usr->username)
                    . ($extra_field ? ' (' . $usr->{$extra_field} . ')' : '')
            ];
        }
        return $res;//['results' => $res, 'type' => 'user'];
    }

    /**
     * Users Search by Key
     *
     * @param $q
     * @param $table_id
     * @param bool $spec_result
     * @return array
     */
    public function searchUserGroups($q, $table_id, $spec_result = false)
    {
        $groups = UserGroup::where('name', 'like', '%' . $q . '%')
            ->where('user_id', auth()->id());
        if ($table_id) {
            $groups->whereHas('_table_permissions', function ($tp) use ($table_id) {
                $tp->where('table_permissions.table_id', $table_id);
                $tp->where('user_groups_2_table_permissions.is_active', 1);
            });
        }
        $groups = $groups->limit(5)->get();

        $res = [];
        foreach ($groups as $group) {
            if ($spec_result) {
                $res[] = [
                    'id' => $group->name . '(Group[' . $group->id . '])',
                    'text' => $group->name . '(Group[' . $group->id . '])'
                ];
            } else {
                $res[] = [
                    'id' => '_' . $group->id,
                    'text' => $group->name . ' (Group)'
                ];
            }
        }
        return $res;//['results' => $res, 'type' => 'group'];
    }

    /**
     * @param string $q
     * @param int $table_id
     * @param int|null $ddl_id
     * @return array
     */
    public function searchUsersInUserGroups(string $q, int $table_id, int $ddl_id = null): array
    {
        $tb = (new TableRepository())->getTable($table_id);
        $cur_user_id = auth()->id();

        $groups = $this->getUserGroupsForSearching($q, $tb);

        $res = [];
        foreach ($groups as $group) {
            $usrs = [];
            $mngr = $group->_individuals_all->where('_link.is_edit_added', '=', 1)->first();
            foreach ($group->_individuals_all as $user) {
                $is_owner = $tb->user_id == $cur_user_id;
                $is_self = $user->id == $cur_user_id;
                $is_manager = $mngr && $mngr->id == $cur_user_id;
                if ($is_owner || $is_self || $is_manager) {
                    $usrs[] = [
                        'id' => $user->id,
                        'name' => $user->first_name ? $user->first_name . ' ' . $user->last_name : $user->username,
                        'found' => preg_match('/' . $q . '/i', $user->username)
                            || preg_match('/' . $q . '/i', $user->email)
                            || preg_match('/' . $q . '/i', $user->first_name)
                            || preg_match('/' . $q . '/i', $user->last_name)
                    ];
                }
            }

            $res[] = [
                'id' => '_' . $group->id,
                'name' => $group->name . ' (Group)',
                'found' => !!preg_match('/' . $q . '/i', $group->name),
                'opened' => false,
                '_users' => $usrs
            ];
        }

        if ($ddl_id) {
            $ddl = $this->ddlRepo->getDDL($ddl_id);
            $ddl_datas = $this->tableDataRepo->getDDLvalues($ddl);
            if ($ddl_datas) {
                $ddl_datas = Arr::pluck($ddl_datas, 'value');
                $res = array_filter($res, function ($res_irem) use ($ddl_datas) {
                    $usr_ids = Arr::pluck($res_irem['_users'], 'id');
                    return in_array($res_irem['id'], $ddl_datas) || array_intersect($usr_ids, $ddl_datas);
                });
                foreach ($res as &$it) {
                    $it['_users'] = array_filter($it['_users'], function ($usr) use ($ddl_datas) {
                        return in_array($usr['id'], $ddl_datas);
                    });
                }
            }
        }
        return $res;
    }

    /**
     * @param string $q
     * @param Table $tb
     * @return mixed
     */
    private function getUserGroupsForSearching(string $q, Table $tb)
    {
        $special = $tb->db_name == 'correspondence_stim_3d';

        $cur_user_id = auth()->id();
        $owner_id = $special ? $cur_user_id : $tb->user_id;

        $groups = UserGroup::where('user_id', $owner_id)
            ->with('_individuals_all')
            ->limit(50);
        if ($q) {
            $groups->where(function ($ug) use ($q) {
                $ug->where('name', 'like', '%' . $q . '%');
                $ug->orWhereHas('_individuals_all', function ($usr) use ($q) {
                    $usr->where('username', 'LIKE', '%' . $q . '%');
                    $usr->orWhere('email', 'LIKE', '%' . $q . '%');
                    $usr->orWhere('first_name', 'LIKE', '%' . $q . '%');
                    $usr->orWhere('last_name', 'LIKE', '%' . $q . '%');
                });
            });
        }

        if ($owner_id != $cur_user_id) {
            $groups->whereHas('_table_permissions', function ($tp) use ($tb) {
                $tp->where('table_permissions.table_id', $tb->id);
                $tp->where('user_groups_2_table_permissions.is_active', 1);
            });
            $groups->whereHas('_individuals_all', function ($usr) use ($cur_user_id) {
                $usr->where('users.id', $cur_user_id);
            });
        }

        return $groups->get();
    }

    /**
     * Update Plans and Addons from Fees table.
     *
     * @param $fields
     * @param $id
     * @return mixed
     */
    public function updatedByAdmin($fields, $id)
    {
        $userSubscription = $this->userRepository->getSubscription($id);

        $userSubscription->plan_code = !empty($fields['plan_id']) ? $fields['plan_id'] : '';
        $userSubscription->notes = !empty($fields['notes']) ? $fields['notes'] : '';
        $userSubscription->save();

        $user = $userSubscription->_user;
        $user->avail_credit = !empty($fields['avail_credit']) ? (float)$fields['avail_credit'] : 0;
        $user->recurrent_pay = !empty($fields['recurrent_pay']) ? 1 : 0;
        $user->renew = !empty($fields['renew']) ? $fields['renew'] : 'Monthly';
        $user->save();

        $arr = [
            'add_bi' => 'bi',
            'add_map' => 'map',
            'add_request' => 'request',
            'add_alert' => 'alert',
            'add_kanban' => 'kanban',
            'add_gantt' => 'gantt',
            'add_email' => 'email',
            'add_calendar' => 'calendar',
            'add_twilio' => 'twilio',
            'add_tournament' => 'tournament',
            'add_simplemap' => 'simplemap',
            'add_grouping' => 'grouping',
            'add_report' => 'report',
            'add_ai' => 'ai',
        ];
        foreach ($arr as $key => $code) {
            if (!empty($fields[$key]) && $userSubscription->plan_code != 'basic') {
                $this->userRepository->addAddon($userSubscription, $code);
            } else {
                $this->userRepository->delAddon($userSubscription, $code);
            }
        }

        $this->calcSubscription($userSubscription, $user);

        return $userSubscription->id;
    }

    /**
     * Calc days and costs for User's Subscription.
     *
     * @param UserSubscription $userSubscription
     * @param User $user
     * @param $all_cost
     * @return void
     */
    protected function calcSubscription(UserSubscription $userSubscription, User $user, $all_cost = null)
    {
        $key = $user->renew == 'Yearly' ? 'per_year' : 'per_month';
        $calc_cost = $userSubscription->_plan->{$key};

        $adn_included = in_array($userSubscription->plan_code, ['standard']);
        $all_adn = $userSubscription->_addons->where('is_special', '=', '1')->first();

        if ($userSubscription->plan_code == 'advanced' || $all_adn) {
            $this->activeAllAddons($user);
        }
        if ($adn_included) {
            if ($all_adn) {
                $calc_cost += $all_adn->{$key};
            } else {
                foreach ($userSubscription->_addons as $adn) {
                    $calc_cost += $adn->{$key};
                }
            }
        }

        $userSubscription->cost = $calc_cost;
        $userSubscription->total_days = $user->renew == 'Yearly' ? 365 : 30;
        $userSubscription->left_days = $userSubscription->total_days;
        if (!is_null($all_cost) && $all_cost != $calc_cost) {
            $userSubscription->left_days *= ($all_cost / $calc_cost);
        }
        $userSubscription->save();
    }

    /**
     * Change User's Plan
     *
     * @param User $user
     * @param String $plan_code
     * @param array $all_addons
     */
    public function changePlan(User $user, String $plan_code, Array $all_addons = [])
    {
        $user->_subscription->plan_code = $plan_code;
        $user->_subscription->left_days = 0;
        $user->_subscription->total_days = 1;
        $user->_subscription->cost = 0;
        $user->_subscription->save();

        //update User's features
        $this->setPlanFeature($user);

        //addons are unavailable for 'basic' plan
        if ($plan_code == 'basic') {
            $this->userRepository->deleteAllAddons($user->_subscription);
        } else {
            //add addons
            foreach ($all_addons as $addon) {
                if ($addon['_checked']) {
                    $this->userRepository->addAddon($user->_subscription, $addon['code']);
                }
            }
        }
    }

    /**
     * Transfer Credits from User to other Users in selected UserGroups.
     *
     * @param User $user
     * @param array $groups
     * @param array $users_ids
     * @return array
     */
    public function transferCredits(User $user, array $groups, array $users_ids): array
    {
        $db_groups = $this->groupsForTransferring($groups, $users_ids);

        $total = 0;
        foreach ($db_groups as $gr) {
            $total += $gr->_val * $gr->_individuals_all_count;
        }

        if ($total <= $user->avail_credit) {
            $user->avail_credit -= $total;
            $user->save();

            foreach ($db_groups as $gr) {
                foreach ($gr->_individuals_all as $individ) {
                    $individ->avail_credit += $gr->_val;
                    $this->userRepository->update($individ->id, ['avail_credit' => $individ->avail_credit]);
                }
            }

            $this->planService->addPaymentHistory($user->id, $total, '', 'Other User', $user->full_name(), $db_groups);

            return ['avail_credit' => $user->avail_credit];
        } else {
            return ['error' => 'You do not have sufficient credit.'];
        }
    }

    /**
     * @param array $groups
     * @param array $users_ids
     * @return Collection
     */
    public function groupsForTransferring(array $groups, array $users_ids): Collection
    {
        $groups = collect($groups);
        $users_ids = collect($users_ids);

        $db_groups = UserGroup::whereIn('id', $groups->pluck('id'))
            ->with('_individuals_all')
            ->withCount('_individuals_all')
            ->get()
            ->each(function ($gr) use ($groups) {
                $elem = $groups->where('id', '=', $gr->id)->first();
                $gr->_val = $elem ? floatval($elem['val']) : 0;
            });

        $recipients = User::whereIn('email', $users_ids->pluck('eml'))->get();
        foreach ($recipients as $usr) {
            $elem = $users_ids->where('eml', '=', $usr->email)->first();
            $usr->_val = $elem ? floatval($elem['val']) : 0;

            $gr = new UserGroup(['name' => $usr->full_name()]);
            $gr->_val = $usr->_val;
            $gr->_individuals_all = collect([$usr]);
            $gr->_individuals_all_count = 1;
            $db_groups->push($gr);
        }

        return $db_groups;
    }

    /**
     * @param User $user
     * @param float $amount
     */
    protected function rewardFromAdminCredit(User $user, float $amount)
    {
        $admin = User::where('role_id', '=', 1)->first();
        $admin->avail_credit -= $amount;
        $admin->save();

        $user->avail_credit += $amount;
        $user->save();

        $username = ($admin->first_name ? ($admin->first_name . ' ' . $admin->last_name) : $admin->username);
        $u_group = [ (object)[
            'name' => 'Invitation',
            '_val' => $amount,
            '_individuals_all' => collect([$user]),
            '_individuals_all_count' => 1,
        ] ];

        $this->planService->addPaymentHistory($admin->id, $amount, '', 'Invitation Reward', $username, $u_group);
    }

    /**
     * Prepare object as next User's Subscription when current ends.
     * @param User $user
     * @param String $renew
     * @param String $plan_code
     * @param array $all_addons
     * @return mixed
     */
    public function createOrUpdateNextSubscription(User $user, String $renew, String $plan_code, Array $all_addons = [])
    {
        //create NextSubscription
        if (!$user->_next_subscription) {
            $this->userRepository->createSubscription([
                'user_id' => $user->id,
                'active' => 0,
                'plan_code' => 'basic'
            ]);
            $user->load('_next_subscription');
        }

        $this->userRepository->deleteAllAddons($user->_next_subscription);
        //addons are unavailable for 'basic' plan
        if ($plan_code != 'basic') {
            //add addons
            foreach ($all_addons as $addon) {
                if ($addon['_checked']) {
                    $this->userRepository->addAddon($user->_next_subscription, $addon['code']);
                }
            }
        }

        //update NextSubscription
        $user->_next_subscription->plan_code = $plan_code;
        $this->calcSubscription($user->_next_subscription, $user);

        //user decibe to stay on current subscription
        if (
            ($user->_next_subscription->cost == $user->_subscription->cost)
            &&
            ($user->_next_subscription->plan_code == $user->_subscription->plan_code)
        ) {
            $user->_next_subscription()->delete();
        }

        $user->load('_next_subscription');

        return $user->_next_subscription;
    }

    /**
     * Activate Next Subscription if current ends
     *
     * @param User $user
     * @return bool
     */
    public function activateNextSubscription(User $user)
    {
        if ($user->_next_subscription && $user->_next_subscription->cost <= $user->avail_credit) {
            $user->avail_credit -= $user->_next_subscription->cost;
            $user->save();

            $user->_subscription()->delete();

            $user->_next_subscription->active = 1;
            $user->_next_subscription->save();
            $user->load(['_subscription', '_next_subscription']);
            return true;
        }
        return false;
    }

    /**
     * Activate Next Subscription if current ends
     *
     * @param User $user
     * @return bool
     */
    public function reactivateSubscription(User $user)
    {
        $reactivated = false;
        if ($user->recurrent_pay) {
            $left_cost = $user->_subscription->cost * ($user->_subscription->left_days / $user->_subscription->total_days);
            $used_credit = ($user->use_credit ? min($user->avail_credit, $user->_subscription->cost) : 0);
            $amount = $user->_subscription->cost - $used_credit;
            $result = [];
            $payService = new PaymentService();

            //try to charge via Stripe
            if ($amount && $user->pay_method == 'stripe' && $user->stripe_user_id && $user->selected_card) {
                $result = $payService->Stripe_Charge($user, $amount, config('app.stripe_private'));
            }

            //try to charge via PayPal
            if ($amount && $user->pay_method == 'paypal' && $user->paypal_user_id && $user->paypal_card_id) {
                $result = $payService->PayPal_Charge_Api($user, $amount, env('PAYPAL_CLIENT_ID'), env('PAYPAL_SECRET'), !!env('PAYPAL_PRODUCTION'));
            }

            //subscription cost is covered by 'available credit' or payed via 'Stripe'
            if (empty($result['error']) && (!$amount || count($result))) {
                $result['request_type'] = 'balance';
                $this->planSetSubscriptionPayed($user, $left_cost, $amount, $used_credit, $result);
                $reactivated = true;
            }

        }
        return $reactivated;
    }

    /**
     * User have payed for Plan.
     *
     * @param User $user
     * @param $left_cost
     * @param $amount
     * @param $used_credit
     * @param array $result
     */
    public function planSetSubscriptionPayed(User $user, $left_cost, $amount, $used_credit, array $result)
    {
        $user->renew = $result['request_renew'] ?? $user->renew;
        $user->save();

        //'Making Payment'
        if (($result['request_type'] ?? '') == 'balance') {
            //credit deduct
            $used_credit = ($user->avail_credit >= $used_credit ? $used_credit : $user->avail_credit);
            $user->avail_credit -= $used_credit;
            $user->save();

            //activate subscription
            $this->calcSubscription($user->_subscription, $user, $left_cost + $amount + $used_credit);

            if ($used_credit > 0) {
                //subscription was payed by credit (full or partially)
                $this->planService->addPaymentHistory($user->id, $used_credit, 'Making Payment', 'Available Credit', '');
            }
            if ($amount > 0) {
                //subscription was payed by card
                $this->planService->addPaymentHistory($user->id, $amount, 'Making Payment', $result['from'], $result['from_details']);
            }
        } //'Adding Credit'
        else {
            //credit add
            $user->avail_credit += $amount;
            $user->save();
            //was added available credit
            $this->planService->addPaymentHistory($user->id, $amount, 'Adding Credit', $result['from'], $result['from_details']);
        }
    }

    /**
     * @param User $user
     * @return void
     */
    public function activeAllAddons(User $user): void
    {
        $present_addon_ids = UserSubscription2Addons::where('user_subscription_id', '=', $user->_subscription->id)
            ->select(['addon_id'])
            ->get()
            ->pluck('addon_id');

        foreach ($this->allAddonsIds->diff($present_addon_ids) as $addon_id) {
            UserSubscription2Addons::create([
                'addon_id' => $addon_id,
                'user_subscription_id' => $user->_subscription->id,
            ]);
        }
    }

    /**
     * @param User $user
     * @param string $email
     * @return mixed
     */
    public function getInvitationOrCreate(User $user, string $email)
    {
        $invit = $user->_invitations()->where('email', '=', $email)->first();
        if (!$invit) {
            $this->addInvitations($user, [$email], true);
            $invit = $user->_invitations()->where('email', '=', $email)->first();
        }
        return $invit;
    }

    /**
     * Parse and add Invitations to User.
     *
     * @param User $user
     * @param array $emails
     * @param bool $skip_err
     * @return array
     */
    public function addInvitations(User $user, array $emails, bool $skip_err = false)
    {
        $arr = [];
        $errors = [];
        foreach ($emails as $eml) {
            $eml = $eml ? preg_replace('/[,.;]$/i', '', trim($eml)) : '';

            if (filter_var($eml, FILTER_VALIDATE_EMAIL) === false) {
                $errors[] = $eml . ' - Incorrect Email';
                continue;
            }
            if ($user->_invitations()->where('email', '=', $eml)->count()) {
                $errors[] = $eml . ' - <span style="color:#00F;">Invite resent</span>';
                continue;
            }
            if (!$skip_err && User::where('email', '=', $eml)->count()) {
                $errors[] = $eml . ' - <span style="color:#F00;">Already registered</span>';
                continue;
            }

            $amo = AppSetting::where('key', '=', 'invite_reward_amount')->first();

            $arr[] = [
                'user_id' => $user->id,
                'email' => $eml,
                'rewarded' => $amo ? $amo->val : 10,
            ];
        }

        $user->_invitations()->insert($arr);
        return $errors;
    }

    /**
     * Update Invitation
     *
     * @param User $user
     * @param int $id
     * @param array $fields
     * @return bool
     */
    public function updateInvitation(User $user, int $id, array $fields)
    {
        $inv = $user->_invitations()
            ->where('id', $id)
            ->first();
        return ($inv ? $inv->update($this->service->delSystemFields($fields)) : false);
    }

    /**
     * Delete Invitation
     *
     * @param User $user
     * @param int $id
     * @return bool
     */
    public function delInvitation(User $user, int $id)
    {
        return $user->_invitations()->where('id', $id)->delete();
    }

    /**
     * Send Invitations to provided emails.
     *
     * @param User $user
     * @return int
     */
    public function sendInvitations(User $user, $invit_ids = [])
    {
        $job = Import::create([
            'file' => '',
            'status' => 'initialized',
            'type' => 'SendInvitations',
        ]);

        dispatch(new SendInvitations($user, $invit_ids, $job->id));

        return $job->id;
    }
}
<?php

namespace Vanguard\Services\Tablda;


use Carbon\Carbon;
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
use Vanguard\Models\AppTheme;
use Vanguard\Models\Import;
use Vanguard\Models\Table\Table;
use Vanguard\Models\User\UserCard;
use Vanguard\Models\User\UserGroup;
use Vanguard\Models\User\UserSubscription;
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
    public function awardReferral(string $email, string $hash)
    {
        if ($user = User::where('personal_hash', $hash)->first()) {

            $invit = $user->_invitations()->where('email', $email)->first();
            if ($invit) {
                $invit->update([
                    'status' => 2,
                    'date_accept' => Carbon::now()
                ]);
            }

            $reward = $invit ? $invit->rewarded : 10;

            $user->invitations_reward += $reward;
            $user->avail_credit += $reward;
            $user->save();
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
     * Users Search by Key
     *
     * @param $q
     * @param null $table_id
     * @param null $request_field
     * @return array
     */
    public function searchUsers($q, $table_id = null, $request_field = null)
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

            $res[] = [
                'id' => ($val_field ? $usr->{$val_field} : $usr->id),
                'text' => ($usr->first_name ? $usr->first_name . " " . $usr->last_name : $usr->username)
                    . ($request_field ? ' (' . $usr->{$request_field} . ')' : '')
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
     * Users Search by Key or relating to UserGroups
     *
     * @param string $q
     * @param int $table_id
     * @return array
     */
    public function searchUsersInUserGroups(string $q, int $table_id)
    {
        $tb = (new TableRepository())->getTable($table_id);
        $cur_user_id = auth()->id();

        $groups = $this->getUserGroupsForSearching($q, $tb);

        $res = [];
        foreach ($groups as $group) {
            $usrs = [];
            foreach ($group->_individuals_all as $user) {
                //only owner can view all users in UserGroup
                if ($tb->user_id == $cur_user_id || $user->id == $cur_user_id) {
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
        return $res;
    }

    /**
     * @param string $q
     * @param Table $tb
     * @return mixed
     */
    private function getUserGroupsForSearching(string $q, Table $tb)
    {
        $cur_user_id = auth()->id();

        $groups = UserGroup::where(function ($ug) use ($q) {
                $ug->where('name', 'like', '%' . $q . '%');
                $ug->orWhereHas('_individuals_all', function ($usr) use ($q) {
                    $usr->where('username', 'LIKE', '%' . $q . '%');
                    $usr->orWhere('email', 'LIKE', '%' . $q . '%');
                    $usr->orWhere('first_name', 'LIKE', '%' . $q . '%');
                    $usr->orWhere('last_name', 'LIKE', '%' . $q . '%');
                });
            })
            ->where('user_id', $tb->user_id)
            ->with('_individuals_all')
            ->limit(5);

        if ($tb->user_id != $cur_user_id) {
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
        ];
        foreach ($arr as $key => $code) {
            if (!empty($fields[$key]) && $userSubscription->plan_code != 'basic') {
                $this->userRepository->addAddon($userSubscription, $code);
            } else {
                $this->userRepository->delAddon($userSubscription, $code);
            }
        }

        $this->calcSubscription($userSubscription, $user->renew);

        return $userSubscription->id;
    }

    /**
     * Calc days and costs for User's Subscription.
     *
     * @param UserSubscription $userSubscription
     * @param $all_cost
     * @param $renew
     */
    public function calcSubscription(UserSubscription $userSubscription, $renew, $all_cost = null)
    {
        $key = $renew == 'Yearly' ? 'per_year' : 'per_month';

        $calc_cost = $userSubscription->_plan->{$key};
        $all_adn = $userSubscription->_addons->where('is_special', '=', '1')->first();
        if ($all_adn) {
            $calc_cost += ($userSubscription->plan_code !== 'basic' ? $all_adn->{$key} : 0);
        } else {
            foreach ($userSubscription->_addons as $adn) {
                $calc_cost += ($userSubscription->plan_code !== 'basic' ? $adn->{$key} : 0);
            }
        }

        $userSubscription->cost = $calc_cost;
        $userSubscription->total_days = $renew == 'Yearly' ? 365 : 30;
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
     * User linked Stripe Card.
     *
     * @param User $user
     * @param $token
     * @return mixed
     */
    public function addStripeCard(User $user, $token)
    {
        //store card
        try {
            \Stripe\Stripe::setApiKey(config('app.stripe_private'));

            if (!$user->stripe_user_id) {
                $stripe_user = \Stripe\Customer::create();
                $user->stripe_user_id = $stripe_user->id;
                $user->save();
            }

            $customer = \Stripe\Customer::retrieve($user->stripe_user_id);
            $customer->sources->create(["source" => $token['id']]);
            //\Stripe\Customer::update($user->stripe_user_id, ['source' => $token['id']]);
            $card = UserCard::create([
                'user_id' => $user->id,
                'stripe_card_id' => $token['card']['id'],
                'stripe_card_last' => $token['card']['last4'],
                'stripe_exp_month' => $token['card']['exp_month'],
                'stripe_exp_year' => $token['card']['exp_year'],
                'stripe_card_name' => $token['card']['name'],
                'stripe_card_zip' => $token['card']['address_zip'],
                'stripe_card_brand' => $token['card']['brand']
            ]);

            //first card active by default
            if (!$user->selected_card) {
                $user->selected_card = $card->id;
                $user->save();
            }

            return $user->_cards()->get();
        } catch (\Exception $e) {
            return ['error' => $e->getMessage()];
        }
    }

    /**
     * Transfer Credits from User to other Users in selected UserGroups.
     *
     * @param User $user
     * @param array $groups
     * @return array
     */
    public function transferCredits(User $user, array $groups)
    {
        $db_groups = UserGroup::whereIn('id', array_pluck($groups, 'id'))
            ->with('_individuals_all')
            ->withCount('_individuals_all')
            ->get();

        $total = 0;
        foreach ($db_groups as $gr) {
            $elem = array_first($groups, function ($item) use ($gr) {
                return $item['id'] == $gr->id;
            });
            $gr->_val = $elem ? floatval($elem['val']) : 0;
            $total += $gr->_val * $gr->_individuals_all_count;
        }

        if ($total <= $user->avail_credit) {
            $user->avail_credit -= $total;
            $user->save();

            foreach ($db_groups as $gr) {
                foreach ($gr->_individuals_all as $individ) {
                    $individ->avail_credit += $gr->_val;
                    $individ->save();
                }
            }

            $username = ($user->first_name ? ($user->first_name . ' ' . $user->last_name) : $user->username);
            $this->planService->addPaymentHistory($user->id, $total, '', 'Other User', $username, $db_groups);

            return ['avail_credit' => $user->avail_credit];
        } else {
            return ['error' => 'You do not have sufficient credit.'];
        }
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
        $this->calcSubscription($user->_next_subscription, $renew);

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
            $this->calcSubscription($user->_subscription, $user->renew, $left_cost + $amount + $used_credit);

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
     * Parse and add Invitations to User.
     *
     * @param User $user
     * @param array $emails
     * @return array
     */
    public function addInvitations(User $user, array $emails)
    {
        $arr = [];
        $errors = [];
        foreach ($emails as $eml) {
            $eml = $eml ? preg_replace('/[,.;]$/i', '', trim($eml)) : '';

            if (filter_var($eml, FILTER_VALIDATE_EMAIL) === false) {
                $errors[] = $eml . ' - Incorrect Email';
                continue;
            }
            if ($user->_invitations()->where('email', $eml)->count()) {
                $errors[] = $eml . ' - Already invited';
                continue;
            }
            if (User::where('email', $eml)->count()) {
                $errors[] = $eml . ' - Already registered';
                continue;
            }

            $arr[] = [
                'user_id' => $user->id,
                'email' => $eml
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
            'status' => 'initialized'
        ]);

        dispatch(new SendInvitations($user, $invit_ids, $job->id));

        return $job->id;
    }
}
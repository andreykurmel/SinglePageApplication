<?php

namespace Vanguard\Http\Controllers\Web\Tablda;

use Carbon\Carbon;
use Illuminate\Http\Request;
use Vanguard\Http\Controllers\Controller;
use Vanguard\Http\Requests\Tablda\Invitations\UsersAddInvitationRequest;
use Vanguard\Http\Requests\Tablda\Invitations\UsersDelInvitationRequest;
use Vanguard\Http\Requests\Tablda\Invitations\UsersSendInvitationsRequest;
use Vanguard\Http\Requests\Tablda\Invitations\UsersUpdateInvitationRequest;
use Vanguard\Http\Requests\Tablda\TransferCreditsRequest;
use Vanguard\Http\Requests\Tablda\UserPayRequest;
use Vanguard\Http\Requests\Tablda\UsersNextSubscriptionRequest;
use Vanguard\Http\Requests\Tablda\UsersSearchGroupsRequest;
use Vanguard\Http\Requests\Tablda\UsersSearchRequest;
use Vanguard\Http\Requests\Tablda\UsersUpdateRequest;
use Vanguard\Repositories\Tablda\PlanRepository;
use Vanguard\Repositories\Tablda\TableRepository;
use Vanguard\Services\Tablda\HelperService;
use Vanguard\Services\Tablda\PaymentService;
use Vanguard\Services\Tablda\UserService;
use Vanguard\User;

class UserController extends Controller
{
    private $users;
    private $payments;
    private $planRepository;

    /**
     * UserController constructor.
     * @param UserService $users
     */
    public function __construct()
    {
        $this->users = new UserService();
        $this->payments = new PaymentService();
        $this->planRepository = new PlanRepository();
    }

    /**
     * @return User
     */
    public function curUser()
    {
        $user = auth()->check() ? auth()->user() : new User();
        return $user->only( (new HelperService())->onlyNames(false, false, '') );
    }

    /**
     * @param Request $request
     * @return array
     */
    public function setBalance(Request $request)
    {
        return ['status' => $this->users->setAdminBalance($request->amount)];
    }

    /**
     * @param Request $request
     * @return object
     */
    public function getUserOrGroupInfo(Request $request) {
        return $this->users->getUserOrGroupInfo($request->user_id);
    }

    /**
     * @param Request $request
     * @return mixed
     */
    public function findUsersInfo(Request $request) {
        return $this->users->findUsersInfo($request->users_ids ?: []);
    }

    /**
     * Users Search by Key
     *
     * @param UsersSearchRequest $request
     * @return array
     */
    public function searchUsers(UsersSearchRequest $request)
    {
        if ($request->table_db) {
            $table = (new TableRepository())->getTableByDB($request->table_db);
            $request->table_id = $table ? $table->id : null;
        }
        return ['results' => $this->users->searchUsers($request->q, $request->table_id, $request->request_field, $request->extras)];
    }

    /**
     * Users Search by Key
     *
     * @param UsersSearchRequest $request
     * @return array
     */
    public function searchUsersCanGroup(UsersSearchRequest $request)
    {
        $key = $request->q;
        return [
            'results' => array_merge(
                $this->users->searchUsers($key, $request->table_id, $request->request_field),
                $this->users->searchUserGroups($key, $request->table_id, !!$request->request_field)
            )
        ];
    }

    /**
     * Users Search by Key
     *
     * @param UsersSearchGroupsRequest $request
     * @return array
     */
    public function searchUsersInUserGroups(UsersSearchGroupsRequest $request)
    {
        return $this->users->searchUsersInUserGroups($request->q ?: '', $request->table_id, $request->ddl_id);
    }

    /**
     * setSelTheme
     *
     * @param Request $request
     * @return array
     */
    public function setSelTheme(Request $request)
    {
        $user = auth()->user();
        if ($request->app_theme_id) {
            $user->app_theme_id = $request->app_theme_id;
        }
        return ['status' => $user->save()];
    }

    /**
     * setSelTheme
     *
     * @param Request $request
     * @return array
     */
    public function setEctracttableTerms(Request $request)
    {
        $user = auth()->user();
        if ($request->extracttable_terms) {
            $user->extracttable_terms = $request->extracttable_terms;
        }
        return ['status' => $user->save()];
    }

    /**
     * Change User's Contract Style
     *
     * @param UsersUpdateRequest $request
     * @return array
     */
    public function updateData(UsersUpdateRequest $request)
    {
        $user = auth()->user();
        $user->recurrent_pay = $request->recurrent_pay;
        $user->pay_method = $request->pay_method;
        $user->use_credit = $request->use_credit;
        $user->selected_card = $request->selected_card;
        return ['status' => $user->save()];
    }

    /**
     * @param UserPayRequest $request
     * @return array|string
     */
    public function payPayPalAccount(UserPayRequest $request)
    {
        return $this->pay($request, 'PayPalAccount');
    }

    /**
     * @param Request $request
     * @return mixed
     */
    public function payCompletedPayPalAccount(Request $request)
    {
        $result = $this->pay($request, 'CompletedPayPalAccount');
        if (empty($result['error'])) {
            return redirect( url('/data') );
        } else {
            return $result['error'];
        }
    }

    /**
     * User have payed for Plan with their Avail Credit.
     *
     * @param UserPayRequest $request
     * @return array
     */
    public function payAvailCredit(UserPayRequest $request)
    {
        return $this->pay($request, 'AvailCredit');
    }

    /**
     * User have payed for Plan via Stripe.
     *
     * @param UserPayRequest $request
     * @return array
     */
    public function payStripeCard(UserPayRequest $request)
    {
        return $this->pay($request, 'StripeCard');
    }

    /**
     * User have payed for Plan via PayPal.
     *
     * @param UserPayRequest $request
     * @return array
     */
    public function payPayPalCard(UserPayRequest $request)
    {
        return $this->pay($request, 'PayPalCard');
    }

    /**
     * User linked card from Stripe.
     *
     * @param Request $request
     * @return array
     */
    public function linkCard(Request $request)
    {
        $user = auth()->user();
        return $this->payments->addStripeCard($user, $request->token);
    }

    /**
     * User have payed for Plan via PayPal.
     *
     * @param Request $request
     * @return array
     */
    public function unlinkCard(Request $request)
    {
        $user = auth()->user();
        if ($request->type == 'Stripe' && $request->id) {
            $user->_cards()->where('id', $request->id)->delete();
            if ($request->id == $user->selected_card) {
                $user->selected_card = null;
                $user->save();
            }
        }
        if ($request->type == 'PayPal') {
            $user->paypal_card_id = null;
            $user->paypal_card_last = null;
            $user->save();
        }
        return [
            'user' => $user,
            '_cards' => $user->_cards()->get()
        ];
    }

    /**
     * Transfer Credits from User to other Users in selected UserGroups.
     *
     * @param TransferCreditsRequest $request
     * @return array
     */
    public function transferCredits(TransferCreditsRequest $request)
    {
        $user = auth()->user();
        $res = $this->users->transferCredits($user, $request->groups, $request->users);
        return !empty($res['error']) ? response($res['error'], 500) : $res;
    }

    /**
     * User have payed for Plan via PayPal.
     *
     * @param Request $request
     * @param string $method
     * @return array|string
     */
    private function pay(Request $request, string $method)
    {
        $user = auth()->user();

        if ($user->_subscription) {
            $left_cost = $user->_subscription->cost * ($user->_subscription->left_days / $user->_subscription->total_days);
        } else {
            $left_cost = 0;
        }

        //convert cents to dollars
        $used_credit = $request->used_credit/100;
        $used_credit = min($user->avail_credit, $used_credit);

        //convert cents to dollars
        $amount = $request->amount/100;

        if ($method == 'AvailCredit') {//plan paying with 'Available Credit'
            $result = ['amount' => 0, 'from' => 'Available Credit', 'from_details' => ''];
        } elseif ($method == 'StripeCard') {//plan paying in Stripe
            $result = $this->payments->planPay_StripeCard($user, $amount);
        } elseif ($method == 'PayPalCard') {//plan already payed in PayPal
            $result = $this->payments->planPay_PayPalCard($user, $amount, $request->order_id, $request->paypal_card);
        } elseif ($method == 'PayPalAccount') {//plan already payed in PayPal
            return $this->payments->planPay_PayPalAccountStart($user, $amount, $request->type);
        } elseif ($method == 'CompletedPayPalAccount') {//plan already payed in PayPal
            $result = $this->payments->planPay_PayPalAccountComplete($user, $request->token);
            $amount = $result['amount'] ?? 0;
        } else {
            $result = ['error' => 'Incorrect payment method!'];
        }

        //save and activate Plan
        if (empty($result['error'])) {
            if ($request->type == 'balance') {
                //upgrade plan
                $this->users->changePlan($user, $request->plan_code, $request->all_addons);
            }

            $result['request_type'] = $result['request_type'] ?? $request->type;
            $result['request_renew'] = $result['request_renew'] ?? $request->renew;
            //update subscription days
            $this->users->planSetSubscriptionPayed($user, $left_cost, $amount, $used_credit, $result);

        }
        $user->load('_available_features');
        $result['user'] = $user;
        return $result;
    }

    /**
     * Create/Update next subscription for User (downgrade)
     *
     * @param UsersNextSubscriptionRequest $request
     * @return array
     */
    public function nextSubscription(UsersNextSubscriptionRequest $request)
    {
        $user = auth()->user();
        $user->renew = $request->renew;
        $user->save();

        return ['subscription' => $this->users->createOrUpdateNextSubscription($user, $request->renew, $request->plan_code, $request->all_addons)];
    }

    /**
     * Reload Invitations for User
     *
     * @return array
     */
    public function reloadInvitations()
    {
        return auth()->user()->_invitations()->get();
    }

    /**
     * Add Invitations for User
     *
     * @param UsersAddInvitationRequest $request
     * @return array
     */
    public function addInvitations(UsersAddInvitationRequest $request)
    {
        $user = auth()->user();
        $res = $this->users->addInvitations($user, $request->emails);
        return [
            'errors' => $res,
            'invits' => $user->_invitations()->get()
        ];
    }

    /**
     * Update Invitations for User
     *
     * @param UsersUpdateInvitationRequest $request
     * @return array
     */
    public function updateInvitation(UsersUpdateInvitationRequest $request)
    {
        $user = auth()->user();
        return ['status' => $this->users->updateInvitation($user, $request->invitation_id, $request->fields)];
    }

    /**
     * Update Invitations for User
     *
     * @param UsersDelInvitationRequest $request
     * @return array
     */
    public function delInvitation(UsersDelInvitationRequest $request)
    {
        $user = auth()->user();
        $this->users->delInvitation($user, $request->invitation_id);
        return $user->_invitations()->get();
    }

    /**
     * Send Invitations to provided emails.
     *
     * @return array
     */
    public function sendInvitations(UsersSendInvitationsRequest $request)
    {
        $user = auth()->user();
        $invit_ids = $request->invit_ids ?: [];
        return ['job_id' => $this->users->sendInvitations($user, $invit_ids)];
    }

    /**
     * Set that user accepted Terms of Service.
     *
     * @return string
     */
    public function tosAccepted()
    {
        $user = auth()->user();
        $user->tos_accepted = Carbon::now();
        $user->save();
        return $user->tos_accepted;
    }
}

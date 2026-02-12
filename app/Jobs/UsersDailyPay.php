<?php

namespace Vanguard\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Vanguard\Services\Tablda\PaymentService;
use Vanguard\Services\Tablda\UserService;
use Vanguard\User;

class UsersDailyPay implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $userService;
    protected $paymentService;
    protected $periodNotifDays = 2;

    /**
     * UsersDailyPay constructor.
     */
    public function __construct()
    {
        $this->userService = new UserService();
        $this->paymentService = new PaymentService();
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $users = User::with(['_subscription', '_next_subscription'])->chunk(100, function ($users) {

            foreach ($users as $user) {
                $this->userService->checkAndSetPlan($user);
                if ($user->_subscription->plan_code != 'basic') {

                    //1 day gone for selected subscription
                    if ($user->_subscription->left_days > 0) {
                        $user->_subscription->left_days--;
                        $user->_subscription->save();
                    }

                    if (in_array($user->_subscription->left_days, [1,7])) {
                        $this->paymentService->checkRenewalNotification($user);
                    }

                    //when User's Subscription almost ends and User don't want to 'downgrade'
                    if ($user->_subscription->left_days < $this->periodNotifDays && !$user->_next_subscription) {
                        //try to reactivate current Subscription
                        $this->userService->reactivateSubscription($user);
                    }

                    //when User's Subscription ends
                    if ($user->_subscription->left_days == 0) {
                        //try to activate next Subscription
                        if (!$this->userService->activateNextSubscription($user)) {
                            //fallback User to 'basic' plan
                            $this->userService->changePlan($user, 'basic');
                        }
                    }

                }

                if ($user->_subscription->plan_code == 'advanced') {
                    $this->userService->activeAllAddons($user);
                }
            }
        });
    }

    public function failed()
    {
        //
    }
}

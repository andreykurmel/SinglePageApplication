<?php

namespace Vanguard\Services\Tablda;


use Vanguard\Models\User\Plan;
use Vanguard\Repositories\Tablda\PlanRepository;

class PlanService
{
    private $repository;

    /**
     * PlanService constructor.
     */
    public function __construct()
    {
        $this->repository = new PlanRepository();
    }

    /**
     * Remove old Features For User.
     *
     * @param $user_id
     * @return mixed
     */
    public function removePlanFeaturesForUser($user_id)
    {
        return $this->repository->removePlanFeaturesForUser($user_id);
    }

    /**
     * Copy Plan Features For User.
     *
     * @param \Vanguard\Models\User\Plan $plan
     * @param $user_id
     * @return mixed
     */
    public function copyPlanFeaturesForUser(Plan $plan, $user_id)
    {
        return $this->repository->copyPlanFeaturesForUser($plan, $user_id);
    }

    /**
     * Get Plan By Code
     *
     * @param $code
     * @return mixed
     */
    public function getPlanByCode($code)
    {
        return $this->repository->getPlanByCode($code);
    }

    /**
     * Add Payment History.
     *
     * @param $user_id
     * @param $amount
     * @param $type
     * @param $from
     * @param $from_details
     * @param array $groups
     */
    public function addPaymentHistory($user_id, $amount, $type, $from, $from_details, $groups = [])
    {
        $notifier = new PaymentEmailNotificationService();

        if (count($groups)) {
            foreach ($groups as $gr) {
                $total = $gr->_val * $gr->_individuals_all_count;
                $receivers_ids = $gr->_individuals_all->pluck('id')->toArray();

                //save payment history
                $payment = $this->repository->addPaymentHistory(
                    $user_id,
                    'Transferring Credit',
                    $total,
                    $gr->name
                );

                //notify users
                $notifier->paymentReceived(
                    $user_id,
                    'transfer_deduct',
                    ['credit_receivers' => $receivers_ids, 'transferred' => $total, 'transaction_id' => $payment->transaction_id]
                );

                foreach ($gr->_individuals_all as $usr) {
                    //save payment history
                    $payment = $this->repository->addPaymentHistory(
                        $usr->id,
                        'Receiving Credit',
                        $gr->_val,
                        '',
                        $from,
                        $from_details
                    );

                    //notify users
                    $notifier->paymentReceived(
                        $usr->id,
                        'transfer_add',
                        ['credit_source' => $user_id, 'transferred' => $gr->_val, 'transaction_id' => $payment->transaction_id]
                    );
                }
            }
        } else {
            //save payment history
            $payment = $this->repository->addPaymentHistory($user_id, $type, $amount, '', $from, $from_details);

            //notify users
            $notify_type = $from == 'Available Credit' ? 'credit_deduct'
                : ($type == 'Making Payment' ? 'subscription_pay' : 'credit_pay');
            $notifier->paymentReceived(
                $user_id,
                $notify_type,
                ['from_details' => $from_details, 'transferred' => $amount, 'transaction_id' => $payment->transaction_id]
            );
        }
    }
}
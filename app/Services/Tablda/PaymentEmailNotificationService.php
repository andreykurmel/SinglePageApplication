<?php

namespace Vanguard\Services\Tablda;


use Carbon\Carbon;
use Dompdf\Dompdf;
use Exception;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Vanguard\Mail\EmailWithSettings;
use Vanguard\Mail\TabldaMail;
use Vanguard\Repositories\Tablda\UserRepository;

class PaymentEmailNotificationService
{
    protected $userRepository;

    /**
     * PlanService constructor.
     */
    public function __construct()
    {
        $this->userRepository = new UserRepository();
    }

    /**
     * @param int $user_id
     * @param string $type - available: subscription_pay, credit_pay, credit_deduct, transfer_deduct, transfer_add
     * @param array $add_params
     */
    public function paymentReceived(int $user_id, string $type, array $add_params = [])
    {
        try {
            $user = $this->userRepository->getById($user_id);
            $user->load([
                '_subscription' => function ($q) {
                    $q->with(['_addons', '_plan']);
                }
            ]);

            $adn_included = in_array($user->_subscription->plan_code, ['advanced','enterprise']);
            $all_adn = $user->_subscription->_addons->where('is_special', '=', '1')->first();
            $cost_col = $user->renew == 'Yearly' ? 'per_year' : 'per_month';
            $username = ($user->first_name ? $user->first_name . ' ' . $user->last_name : $user->username);
            $greeting = trans('app.pay_mail__greeting',
                ['username' => $username]
            );

            $params = [
                'from.account' => 'noreply',
                'subject' => 'Transaction notice - ' . $this->subjectMessage($type),
                'to.address' => $user->email,
            ];
            $data = [
                'greeting' => $greeting,
                'user' => $user,
                'username' => $username,
                'adn_included' => $adn_included,
                'all_adn' => $all_adn,
                'cost_col' => $cost_col,
                'from_details' => $add_params['from_details'] ?? '',
                'transferred' => $add_params['transferred'] ?? 0,
                'transaction_id' => $add_params['transaction_id'] ?? '',
                'credit_receivers' => $this->getCreditReceivers($add_params['credit_receivers'] ?? []),
                'credit_source' => $this->getCreditSource($add_params['credit_source'] ?? null),
                'service_date' => Carbon::now()->addDay($user->_subscription->left_days)->format('Y-m-d H:i'),
            ];

            $params['attach_files'] = $this->createPDFreceipt($type, $data, $params);

            $mailer = new EmailWithSettings($type, $user->email, [config('mail.support.receiver')]);
            $mailer->queue($params, $data);

        } catch (Exception $e) {
            Log::info('Payment Email Notification Error - uid:' . $user_id);
            Log::info($e->getMessage());
        }
    }

    /**
     * Get Subject.
     *
     * @param string $type
     * @return string
     * @throws Exception
     */
    protected function subjectMessage(string $type)
    {
        switch ($type) {
            case 'subscription_pay':
                $res = 'payment receipt';
                break;
            case 'credit_pay':
            case 'transfer_add':
                $res = 'credit addition';
                break;
            case 'credit_deduct':
            case 'transfer_deduct':
                $res = 'credit deduction';
                break;
            default:
                throw new Exception('PaymentEmailNotificationService::subjectMessage - incorrect type');
        }
        return $res;
    }

    /**
     * getCreditReceivers
     *
     * @param array $users_ids
     * @return array
     */
    protected function getCreditReceivers(array $users_ids)
    {
        $users = $this->userRepository->getMass($users_ids);

        $receivers = [];
        foreach ($users as $user) {
            $username = ($user->first_name ? $user->first_name . ' ' . $user->last_name : $user->username);
            $receivers[] = $username . ' (' . $user->email . ')';
        }

        return $receivers;
    }

    /**
     * getCreditSource
     *
     * @param int|null $user_id
     * @return string
     */
    protected function getCreditSource(int $user_id = null)
    {
        if ($user_id) {
            $user = $this->userRepository->getById($user_id);
            $username = ($user->first_name ? $user->first_name . ' ' . $user->last_name : $user->username);
            return $username . ' (' . $user->email . ')';
        } else {
            return '';
        }
    }

    /**
     * @param string $type
     * @param array $data
     * @param array $params
     * @return array
     */
    protected function createPDFreceipt(string $type, array $data, array $params)
    {
        if (!in_array($type, ['subscription_pay', 'credit_pay'])) {
            return [];
        }

        $html = (new TabldaMail('tablda.emails.payments.' . $type, $data, $params))->render();

        $pdf = new Dompdf();
        $pdf->setPaper("A4", "portrait");
        $pdf->loadHtml($html);
        $pdf->render();

        $filename = 'Payment_Receipt_' . ($data['user']->username) . '_' . date('Y_m_d') . '.pdf';
        Storage::put('public/mail_receipts/' . $filename, $pdf->output());

        return [storage_path() . '/app/public/mail_receipts/' . $filename => $filename];
    }
}
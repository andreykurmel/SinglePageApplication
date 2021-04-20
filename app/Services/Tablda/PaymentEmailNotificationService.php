<?php

namespace Vanguard\Services\Tablda;


use Carbon\Carbon;
use Dompdf\Dompdf;
use Illuminate\Support\Facades\Storage;
use Vanguard\Mail\TabldaMail;
use Vanguard\Repositories\Tablda\UserRepository;
use Vanguard\User;

class PaymentEmailNotificationService
{
    private $userRepository;

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
        $user = $this->userRepository->getById($user_id);
        $user->load([
            '_subscription' => function($q) {
                $q->with(['_addons', '_plan']);
            }
        ]);

        $cost_col = $user->renew == 'Yearly' ? 'per_year' : 'per_month';
        $username = ($user->first_name ? $user->first_name . ' ' . $user->last_name : $user->username);
        $greeting = trans('app.pay_mail__greeting',
            ['username' => $username]
        );

        $params = [
            'from.account' => 'noreply',
            'subject' => 'Transaction notice - '.$this->subjectMessage($type),
            'to.address' => $user->email,
        ];
        $data = [
            'greeting' => $greeting,
            'user' => $user,
            'username' => $username,
            'cost_col' => $cost_col,
            'from_details' => $add_params['from_details'] ?? '',
            'transferred' => $add_params['transferred'] ?? 0,
            'transaction_id' => $add_params['transaction_id'] ?? '',
            'credit_receivers' => $this->getCreditReceivers($add_params['credit_receivers'] ?? []),
            'credit_source' => $this->getCreditSource($add_params['credit_source'] ?? null),
            'service_date' => Carbon::now()->addDay( $user->_subscription->left_days )->format('Y-m-d H:i'),
        ];

        $params['attach_files'] = $this->createPDFreceipt($type, $data, $params);

        $to = [$user->email];
        $to[] = config('mail.support.receiver');
        \Mail::to($to)->queue(new TabldaMail('tablda.emails.payments.'.$type, $data, $params));
    }

    /**
     * @param string $type
     * @param array $data
     * @param array $params
     * @return array
     */
    private function createPDFreceipt(string $type, array $data, array $params) {
        if (!in_array($type, ['subscription_pay','credit_pay'])) {
            return [];
        }

        $html = (new TabldaMail('tablda.emails.payments.'.$type, $data, $params))->render();

        $pdf = new Dompdf();
        $pdf->setPaper("A4", "portrait");
        $pdf->loadHtml($html);
        $pdf->render();

        $filename = 'Payment_Receipt_'.($data['user']->username).'_'.date('Y_m_d').'.pdf';
        Storage::put('public/mail_receipts/'.$filename, $pdf->output());

        return [ storage_path().'/app/public/mail_receipts/'.$filename => $filename ];
    }

    /**
     * Get Subject.
     *
     * @param string $type
     * @return string
     * @throws \Exception
     */
    private function subjectMessage(string $type) {
        switch ($type) {
            case 'subscription_pay': $res = 'payment receipt';
                    break;
            case 'credit_pay':
            case 'transfer_add': $res = 'credit addition';
                    break;
            case 'credit_deduct':
            case 'transfer_deduct': $res = 'credit deduction';
                    break;
            default:
                throw new \Exception('PaymentEmailNotificationService::subjectMessage - incorrect type');
        }
        return $res;
    }

    /**
     * getCreditReceivers
     *
     * @param array $users_ids
     * @return string
     */
    private function getCreditReceivers(array $users_ids) {
        $users = $this->userRepository->getMass($users_ids);

        $receivers = [];
        foreach ($users as $user) {
            $username = ($user->first_name ? $user->first_name . ' ' . $user->last_name : $user->username);
            $receivers[] = $username . ' ('.$user->email.')';
        }

        return $receivers;
    }

    /**
     * getCreditSource
     *
     * @param int|null $user_id
     * @return string
     */
    private function getCreditSource(int $user_id = null) {
        if ($user_id) {
            $user = $this->userRepository->getById($user_id);
            $username = ($user->first_name ? $user->first_name . ' ' . $user->last_name : $user->username);
            return $username . ' ('.$user->email.')';
        } else {
            return '';
        }
    }
}
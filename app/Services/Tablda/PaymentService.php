<?php

namespace Vanguard\Services\Tablda;


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
use Vanguard\User;

class PaymentService
{

    /**
     * UserService constructor.
     */
    public function __construct()
    {
    }

    /**
     * User have payed for Plan via Stripe Card.
     *
     * @param User $user
     * @param $amount
     * @return array
     */
    public function planPay_StripeCard(User $user, $amount)
    {
        if ($amount > 0) {
            return $this->Stripe_Charge($user, $amount, config('app.stripe_private'));
        }
        return ['amount' => 0];
    }

    /**
     * Charge User via Stripe
     *
     * @param User $user
     * @param $amount
     * @param $stripe_private
     * @return array
     */
    public function Stripe_Charge(User $user, $amount, $stripe_private)
    {
        \Stripe\Stripe::setApiKey($stripe_private);
        //get card
        try {
            $customer = \Stripe\Customer::retrieve($user->stripe_user_id);
            $selected_card = $user->_cards();
            if ($user->selected_card) {
                $selected_card->where('id', $user->selected_card);
            }
            $selected_card = $selected_card->first();
            $card = $customer->sources->retrieve($selected_card->stripe_card_id);
        } catch (\Exception $e) {
            return ['error' => $e->getMessage()];
        }

        //charge user
        try {
            \Stripe\Charge::create([
                'amount' => intval($amount * 100),
                'currency' => 'usd',
                'description' => config('app.name') . ' Subscription Charge',
                'customer' => $customer->id,
                'source' => $card->id,
            ]);

            return ['amount' => $amount, 'from' => 'Credit Card', 'from_details' => '****' . $selected_card->stripe_card_last];

        } catch (\Exception $e) {
            return ['error' => $e->getMessage()];
        }
    }

    /**
     * User have payed for Plan via PayPal Card.
     *
     * @param User $user
     * @param $amount
     * @param $order_id
     * @param $paypal_card
     * @return array
     */
    public function planPay_PayPalCard(User $user, $amount, $order_id, $paypal_card)
    {
        if ($order_id) {
            return $this->PayPal_Charge_Order($user, $order_id, env('PAYPAL_CLIENT_ID'), env('PAYPAL_SECRET'), !!env('PAYPAL_PRODUCTION'));
        }

        if ($paypal_card) {

            //store card
            try {
                if (!$user->paypal_card_id) {
                    $paypal_card['type'] = substr($paypal_card['number'], 0, 1) == '5' ? 'mastercard' : 'visa';
                    $card = new CreditCard($paypal_card);
                    $vault_card = $card->create($apiContext);
                    $user->paypal_card_id = $vault_card->id;
                    $user->paypal_card_last = substr($paypal_card['number'], -4);
                    $user->save();
                }
            } catch (\Exception $e) {
                return ['error' => $e->getMessage()];
            }

            return $this->PayPal_Charge_Api($user, $amount, env('PAYPAL_CLIENT_ID'), env('PAYPAL_SECRET'), !!env('PAYPAL_PRODUCTION'));
        }

        return ['amount' => 0];
    }

    /**
     * @param $user
     * @param $order_id
     * @param string $paypal_client
     * @param string $paypal_secret
     * @param bool $is_live
     * @return array|string[]
     */
    public function PayPal_Charge_Order($user, $order_id, string $paypal_client, string $paypal_secret, bool $is_live)
    {
        $client = $this->paypalClient($paypal_client, $paypal_secret, $is_live);
        $response = $client->execute(new OrdersGetRequest($order_id));

        if ($response->result->status == 'COMPLETED') {
            $ord_amount = $response->result->purchase_units[0]->amount->value;
            return ['amount' => $ord_amount, 'from' => 'PayPal', 'from_details' => $order_id];
        } else {
            return ['error' => 'Incompleted order'];
        }
    }

    /**
     * @param $user
     * @param $amount
     * @param string $paypal_client
     * @param string $paypal_secret
     * @param bool $is_live
     * @return array
     */
    public function PayPal_Charge_Api($user, $amount, string $paypal_client, string $paypal_secret, bool $is_live)
    {
        $apiContext = $this->paypalApiContext($paypal_client, $paypal_secret, $is_live);
        return $this->PayPal_Charge($user, $amount, $apiContext);
    }

    /**
     * @param string $paypal_client
     * @param string $paypal_secret
     * @param bool $is_live
     * @return ApiContext
     */
    protected function paypalApiContext(string $paypal_client, string $paypal_secret, bool $is_live)
    {
        $apiContext = new ApiContext(new OAuthTokenCredential($paypal_client, $paypal_secret));
        $apiContext->setConfig(['mode' => $is_live ? 'live' : 'sandbox']);
        return $apiContext;
    }

    /**
     * @param string $paypal_client
     * @param string $paypal_secret
     * @param bool $is_live
     * @return PayPalHttpClient
     */
    protected function paypalClient(string $paypal_client, string $paypal_secret, bool $is_live)
    {
        $environment = $is_live ?
            new ProductionEnvironment($paypal_client, $paypal_secret) :
            new SandboxEnvironment($paypal_client, $paypal_secret);

        return new PayPalHttpClient($environment);
    }

    /**
     * Charge User via Card saved in PayPal
     *
     * @param User $user
     * @param $amount
     * @param $apiContext
     * @return array
     */
    protected function PayPal_Charge(User $user, $amount, $apiContext)
    {
        //charge user
        try {
            if ($amount > 0) {
                $card_token = new CreditCardToken();
                $card_token->setCreditCardId($user->paypal_card_id);
                $instrument = new FundingInstrument();
                $instrument->setCreditCardToken($card_token);
                $payer = new Payer();
                $payer->setPaymentMethod("credit_card")->setFundingInstruments([$instrument]);
                $pay_amount = new Amount();
                $pay_amount->setCurrency("USD")->setTotal($amount);
                $transaction = new Transaction();
                $transaction->setAmount($pay_amount)->setDescription("Payment description");

                $payment = new Payment();
                $payment->setIntent("sale")
                    ->setPayer($payer)
                    ->setTransactions(array($transaction));
                $payment->create($apiContext);
            }

            return ['amount' => $amount, 'from' => 'PayPal', 'from_details' => $user->paypal_card_id];

        } catch (\Exception $e) {
            return ['error' => $e->getMessage()];
        }
    }

    /**
     * User have payed for Plan via PayPal Account.
     *
     * @param User $user
     * @param $amount
     * @param $req_type
     * @return array
     */
    public function planPay_PayPalAccountStart(User $user, $amount, $req_type)
    {
        if ($amount > 0) {
            $request = new OrdersCreateRequest();
            $request->prefer('return=representation');
            $request->body = [
                "intent" => "CAPTURE",
                "purchase_units" => [[
                    "reference_id" => $req_type,
                    "name" => "TablDA Subscription",
                    "sku" => "tablda01",
                    "amount" => [
                        "value" => number_format($amount, 2, '.', ''),
                        "currency_code" => "USD"
                    ]
                ]],
                "application_context" => [
                    "cancel_url" => env('APP_URL'),
                    "return_url" => env('APP_URL').'/ajax/user/payCompletedPayPalAccount'
                ]
            ];
            $client = $this->paypalClient(env('PAYPAL_CLIENT_ID'), env('PAYPAL_SECRET'), !!env('PAYPAL_PRODUCTION'));
            $response = $client->execute($request);
            $approve = '';
            foreach ($response->result->links as $link) {
                if ($link->rel == 'approve') {
                    $approve = $link->href;
                }
            }
            return ['approve_url' => $approve];

        }
        return ['amount' => 0];
    }

    /**
     * User have payed for Plan via PayPal Account.
     *
     * @param User $user
     * @param $token
     * @return array
     */
    public function planPay_PayPalAccountComplete(User $user, $token)
    {
        $request = new OrdersCaptureRequest($token);
        $request->prefer('return=representation');
        $client = $this->paypalClient(env('PAYPAL_CLIENT_ID'), env('PAYPAL_SECRET'), !!env('PAYPAL_PRODUCTION'));
        $response = $client->execute($request);
        if (
            $response->result->status == 'COMPLETED'
            &&
            $unit = array_first($response->result->purchase_units)
        ) {
            $ord_amount = $unit->amount->value;
            return ['request_type' => $unit->reference_id, 'amount' => $ord_amount, 'from' => 'PayPal', 'from_details' => $token];
        } else {
            return ['error' => 'Paypal Return Url Error'];
        }
    }
}
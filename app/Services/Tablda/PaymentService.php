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
use Vanguard\Models\User\UserCard;
use Vanguard\User;

class PaymentService
{

    protected $customer_id;
    protected $description;

    /**
     * PaymentService constructor.
     * @param string $cust_id
     * @param string $description
     */
    public function __construct(string $cust_id = '', string $description = '')
    {
        $this->customer_id = $cust_id;
        $this->description = $description ?: (config('app.name') . ' Subscription Charge');
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
    public function Stripe_Charge(User $user, $amount, $stripe_private, $card_token = null)
    {
        \Stripe\Stripe::setApiKey($stripe_private);
        //get card
        try {

            if ($card_token) {
                //Make payment between two Users.
                try {
                    $customer = \Stripe\Customer::retrieve($this->customer_id);
                } catch (\Exception $e) {
                    $customer = \Stripe\Customer::create();
                }
                $customer->sources->create(["source" => $card_token['id']]);
                $selected_card = ['stripe_card_last' => $card_token['card']['last4']];
                $card = $customer->sources->retrieve($card_token['card']['id']);
            }
            else {
                //Pay by User's saved card for Tablda.com
                $customer = \Stripe\Customer::retrieve($user->stripe_user_id);
                $selected_card = $user->_cards();
                if ($user->selected_card) {
                    $selected_card->where('id', '=', $user->selected_card);
                }
                $selected_card = $selected_card->first();
                $card = $customer->sources->retrieve($selected_card->stripe_card_id);
            }

        } catch (\Exception $e) {
            return ['error' => $e->getMessage()];
        }

        //charge user
        try {
            \Stripe\Charge::create([
                'amount' => intval($amount * 100),
                'currency' => 'usd',
                'description' => $this->description,
                'customer' => $customer->id,
                'source' => $card->id,
            ]);

            return $this->returnArray(null, $amount,'Credit Card','****' . $selected_card['stripe_card_last'], $customer->id);

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
                    $apiContext = $this->paypalApiContext(env('PAYPAL_CLIENT_ID'), env('PAYPAL_SECRET'), !!env('PAYPAL_PRODUCTION'));
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
            return $this->returnArray(null, $ord_amount,'PayPal',$order_id, $response->result->id);
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
            $payment = new Payment();
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
                $transaction->setAmount($pay_amount)->setDescription($this->description);

                $payment->setIntent("sale")
                    ->setPayer($payer)
                    ->setTransactions(array($transaction));
                $payment->create($apiContext);
            }

            return $this->returnArray(null, $amount,'PayPal', $user->paypal_card_id, $payment->getId());

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
                    "name" => $this->description,
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
            return $this->returnArray($unit->reference_id, $ord_amount,'PayPal',$token, $response->result->id);
        } else {
            return ['error' => 'Paypal Return Url Error'];
        }
    }

    /**
     * @param $req_type
     * @param $amount
     * @param $from
     * @param $from_details
     * @param $customer_id
     * @return array
     */
    protected function returnArray($req_type, $amount, $from, $from_details, $customer_id)
    {
        return [
            'request_type' => $req_type ?: null,
            'amount' => $amount ?: null,
            'from' => $from ?: null,
            'from_details' => $from_details ?: null,
            'customer_id' => $customer_id ?: null,
        ];
    }
}
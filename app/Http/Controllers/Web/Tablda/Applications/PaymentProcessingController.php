<?php

namespace Vanguard\Http\Controllers\Web\Tablda\Applications;

use Illuminate\Http\Request;
use Vanguard\Http\Controllers\Controller;
use Vanguard\Http\Controllers\Web\Tablda\Applications\Transfers\DirectCallInput;
use Vanguard\Http\Controllers\Web\Tablda\Applications\Transfers\DirectCallOut;
use Vanguard\Models\Correspondences\CorrespApp;
use Vanguard\Models\Table\Table;
use Vanguard\Models\Table\TableFieldLink;
use Vanguard\Repositories\Tablda\TableFieldLinkRepository;
use Vanguard\Repositories\Tablda\UserConnRepository;
use Vanguard\Services\Tablda\BladeVariablesService;
use Vanguard\Services\Tablda\PaymentService;
use Vanguard\Services\Tablda\TableDataService;
use Vanguard\Services\Tablda\UserService;
use Vanguard\User;

class PaymentProcessingController extends Controller implements AppControllerInterface
{

    /**
     * @var BladeVariablesService
     */
    protected $bladeVariablesService;
    /**
     * @var PaymentService
     */
    protected $pay_serv;
    /**
     * @var TableDataService
     */
    protected $data_serv;
    /**
     * @var UserConnRepository
     */
    protected $conn_repo;

    /**
     * CallApiDesignController constructor.
     */
    public function __construct()
    {
        $this->data_serv = new TableDataService();
        $this->conn_repo = new UserConnRepository();
        $this->bladeVariablesService = new BladeVariablesService();
    }

    /**
     * @param Request $request
     * @param CorrespApp $correspApp
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function get(Request $request, CorrespApp $correspApp)
    {
        [$errors_present, $link, $row, $table] = $this->getLinkRowTable($request->l, $request->r);

        $lightweight = $correspApp->open_as_popup;
        $stripe_keys = !$errors_present ? $this->conn_repo->userPaymentDecrypt($link->_stripe_user_key) : [];
        $paypal_keys = !$errors_present ? $this->conn_repo->userPaymentDecrypt($link->_paypal_user_key) : [];
        $papp = CorrespApp::where('code', '=', 'payment_processing')->first();
        $no_settings = $papp && $link && $papp->id == $link->table_app_id;
        return view('tablda.applications.payment-processing', array_merge(
            $this->bladeVariablesService->getVariables(null, 0, $lightweight),
            [
                'row' => $row,
                'link' => $link,
                'embed' => $lightweight,
                'errors_present' => $errors_present,
                'stripe_key' => $stripe_keys['public_key']??'',
                'paypal_client' => $paypal_keys['public_key']??'',
                'route_group' => 'payment_processing',
                'no_settings' => $no_settings,
            ]
        ));
    }

    /**
     * @param Request $request
     */
    public function post(Request $request)
    {
        //
    }

    /**
     * @param DirectCallInput $input
     * @return DirectCallOut
     */
    public function direct_call(DirectCallInput $input)
    {
        return new DirectCallOut();
    }

    /**
     * @param $link_id
     * @param $row_id
     * @return array
     */
    protected function getLinkRowTable($link_id, $row_id)
    {
        $errors_present = [];
        $table = $link = $row = null;

        try {
            $link = (new TableFieldLinkRepository())->getLink($link_id);
            $table = $link->_field->_table;
            $link->_paypal_user_key = (new UserConnRepository())->userPaymentDecrypt($link->_paypal_user_key ?: []);
            $link->_stripe_user_key = (new UserConnRepository())->userPaymentDecrypt($link->_stripe_user_key ?: []);
            $link->load('_payment_amount_fld', '_payment_method_fld', '_payment_description_fld');
        } catch (\Exception $e) {
            $errors_present[] = 'Param "link" not present!';
        }

        try {
            $row = (new TableDataService())->getDirectRow($table, $row_id);
        } catch (\Exception $e) {
            $errors_present[] = 'Param "row" not present!';
        }

        if ($link && !$link->_paypal_user_key && !$link->_stripe_user_key) {
            $errors_present[] = 'Payment connection not found!';
        }
        if ($link && !$link->_payment_amount_fld) {
            $errors_present[] = 'Amount field not found!';
        }
        if ($row && $link && $link->_payment_amount_fld && !$row[$link->_payment_amount_fld->field]??'') {
            $errors_present[] = 'Amount is empty!';
        }
        return [$errors_present, $link, $row, $table];
    }

    /**
     * @param User $user
     * @param TableFieldLink $link
     * @param Table $table
     * @param array $pay_result
     * @param $row_id
     * @throws \Exception
     */
    protected function saveResultToRow(User $user, TableFieldLink $link, Table $table, array $pay_result, $row_id)
    {
        $update = [];
        if ($link->_payment_history_amount_fld) {
            $update[ $link->_payment_history_amount_fld->field ] = $pay_result['amount'] ?? 0;
        }
        if ($link->_payment_history_payee_fld) {
            $update[ $link->_payment_history_payee_fld->field ] = $pay_result['customer_id'] ?? '';
        }
        if ($link->_payment_history_date_fld) {
            $update[ $link->_payment_history_date_fld->field ] = now()->format('Y-m-d H:i:s');
        }

        if ($update) {
            $this->data_serv->updateRow($table, $row_id, $update, $user->id);
        }
    }

    /**
     * @param TableFieldLink $link
     * @param $row
     * @return PaymentService
     */
    protected function makePayService(TableFieldLink $link, $row)
    {
        $customer = $link->_payment_customer_fld ? $row[ $link->_payment_customer_fld->field ] : '';
        $description = $link->_payment_description_fld ? $row[ $link->_payment_description_fld->field ] : '';
        return new PaymentService($customer?:'', $description?:'');
    }

    /**
     * @param Request $request
     * @return array|string[]
     * @throws \Exception
     */
    public function viaPayPalCard(Request $request)
    {
        $user = auth()->check() ? auth()->user() : new User();
        [$errors_present, $link, $row, $table] = $this->getLinkRowTable($request->link_id, $request->row_id);
        if (!$errors_present) {
            $pay_serv = $this->makePayService($link, $row);
            $user_keys = $this->conn_repo->userPaymentDecrypt($link->_paypal_user_key);
            $payed = $pay_serv->PayPal_Charge_Order($user, $request->order_id, $user_keys['public_key'], $user_keys['secret_key'], $user_keys['mode']=='live');
            if (empty($payed['error'])) {
                $this->saveResultToRow($user, $link, $table, $payed, $row['id']);
            }
            return $payed;
        } else {
            return ['error' => implode('\\n', $errors_present)];
        }
    }

    /**
     * @param Request $request
     * @return array
     * @throws \Exception
     */
    public function viaStripeCard(Request $request)
    {
        $user = auth()->check() ? auth()->user() : new User();
        [$errors_present, $link, $row, $table] = $this->getLinkRowTable($request->link_id, $request->row_id);
        if (!$errors_present && $request->token) {
            $amount = $row[$link->_payment_amount_fld->field];
            $pay_serv = $this->makePayService($link, $row);
            $user_keys = $this->conn_repo->userPaymentDecrypt($link->_stripe_user_key);
            $payed = $pay_serv->Stripe_Charge($user, $amount, $user_keys['secret_key'], $request->token);
            if (empty($payed['error'])) {
                $this->saveResultToRow($user, $link, $table, $payed, $row['id']);
            }
            return $payed;
        } else {
            return ['error' => implode('\\n', $errors_present)];
        }
    }
}

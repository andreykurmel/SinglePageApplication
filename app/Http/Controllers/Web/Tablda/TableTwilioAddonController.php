<?php

namespace Vanguard\Http\Controllers\Web\Tablda;


use Carbon\Carbon;
use Exception;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Collection;
use Twilio\Exceptions\ConfigurationException;
use Twilio\Exceptions\TwilioException;
use Vanguard\Http\Controllers\Controller;
use Vanguard\Models\Table\TableData;
use Vanguard\Models\Table\TableEmailRight;
use Vanguard\Models\Table\TableTwilioAddonSetting;
use Vanguard\Models\Table\TableTwilioRight;
use Vanguard\Models\TwilioHistory;
use Vanguard\Models\User\UserApiKey;
use Vanguard\Modules\Twilio\TwilioApi;
use Vanguard\Repositories\Tablda\TableRepository;
use Vanguard\Repositories\Tablda\TwilioHistoryRepository;
use Vanguard\Repositories\Tablda\UserConnRepository;
use Vanguard\Services\Tablda\TableService;
use Vanguard\Services\Tablda\TableTwilioAddonService;
use Vanguard\User;

class TableTwilioAddonController extends Controller
{
    use CanEditAddon;

    /**
     * @var TableTwilioAddonService
     */
    protected $twilioService;
    /**
     * @var TwilioHistoryRepository
     */
    protected $twilioHistory;

    /**
     * TableTwilioAddonController constructor.
     */
    public function __construct()
    {
        $this->twilioService = new TableTwilioAddonService();
        $this->twilioHistory = new TwilioHistoryRepository();
    }

    /**
     * @param Request $request
     * @return TableTwilioAddonSetting
     * @throws AuthorizationException
     */
    public function insert(Request $request)
    {
        $table = (new TableService())->getTable($request->table_id);
        $user = auth()->check() ? auth()->user() : new User();
        $this->canEditAddon($table, 'twilio');
        return $this->twilioService->insertTwilioSett(array_merge($request->fields, ['table_id' => $table->id]));
    }

    /**
     * @param Request $request
     * @return array
     * @throws AuthorizationException
     */
    public function update(Request $request)
    {
        $twilio = $this->authTwilio($request->twilio_add_id);
        return [
            'status' => $this->twilioService->updateTwilioSett($request->twilio_add_id, array_merge($request->fields, ['table_id' => $twilio->_table->id]))
        ];
    }

    /**
     * @param $twilio_add_id
     * @return TableTwilioAddonSetting
     * @throws AuthorizationException
     */
    protected function authTwilio($twilio_add_id)
    {
        $twilio = $this->twilioService->getTwilioSett($twilio_add_id);
        $this->canEditAddonItem($twilio->_table, $twilio->_twilio_rights());
        return $twilio;
    }

    /**
     * @param Request $request
     * @return array
     * @throws AuthorizationException
     */
    public function delete(Request $request)
    {
        $twilio = $this->authTwilio($request->twilio_add_id);
        return [
            'status' => $this->twilioService->deleteTwilioSett($request->twilio_add_id)
        ];
    }

    /**
     * @param Request $request
     * @return TableTwilioAddonSetting
     * @throws AuthorizationException
     */
    public function copyAdn(Request $request)
    {
        $fr = $this->twilioService->getTwilioSett($request->from_adn_id);
        $to = $this->twilioService->getTwilioSett($request->to_adn_id);
        $user = auth()->check() ? auth()->user() : new User();
        $this->canEditAddonItem($fr->_table, $fr->_twilio_rights());
        $this->canEditAddonItem($to->_table, $to->_twilio_rights());
        return $this->twilioService->copyAdn($request->from_adn_id, $request->to_adn_id);
    }

    /**
     * @param Request $request
     * @return array
     * @throws AuthorizationException
     */
    public function sendTwilio(Request $request)
    {
        $twilio = $this->authTwilio($request->twilio_add_id);
        return $this->twilioService->sendTwilios($twilio, $request->row_id ?: null);
    }

    /**
     * @param Request $request
     * @return array
     * @throws AuthorizationException
     */
    public function cancelTwilio(Request $request)
    {
        $twilio = $this->authTwilio($request->twilio_add_id);
        return $this->twilioService->cancelTwilio($twilio);
    }

    /**
     * @param Request $request
     * @return array
     * @throws AuthorizationException
     */
    public function previewTwilio(Request $request)
    {
        $twilio = $this->authTwilio($request->twilio_add_id);
        return $this->twilioService->previewTwilio($twilio, $request->row_id ?: null, $request->special ?: '');
    }

    /**
     * @param Request $request
     * @return TableTwilioAddonSetting
     * @throws AuthorizationException
     */
    public function twilioStatus(Request $request)
    {
        return $this->authTwilio($request->twilio_add_id);
    }

    /**
     * @param Request $request
     * @return array
     * @throws AuthorizationException
     */
    public function clearHistory(Request $request)
    {
        $twilio = $this->authTwilio($request->twilio_add_id);
        return $this->twilioService->clearHistory($twilio, $request->history_id ?: null);
    }

    /**
     * @param Request $request
     * @return TwilioHistory
     * @throws AuthorizationException
     * @throws ConfigurationException
     */
    public function sendSMS(Request $request)
    {
        $phone = $request->phone;
        $message = $request->message;

        if ($request->table_id) {
            $table = (new TableRepository())->getTable($request->table_id);
            $user = auth()->check() ? auth()->user() : new User();
            $this->authorizeForUser($user, 'isOwner', [TableData::class, $table]);
            $message = $this->twilioService->replaceSmsBody($table, $request->row_id, $message);
        }

        $api = $this->twApi($request->twilio_acc_id);
        $api->sendSMS($phone, $message);

        return $this->twilioHistory->store(
            auth()->id(),
            $phone,
            TwilioHistory::$SMS_TYPE,
            [
                'sms_from' => $api->fromPhone(),
                'sms_to' => $phone,
                'sms_message' => $message,
            ],
            $request->table_id ?: null,
            $request->field_id ?: null,
            $request->row_id ?: null
        );
    }

    /**
     * @param int $twilio_acc_id
     * @param bool $force
     * @return TwilioApi
     * @throws ConfigurationException
     */
    protected function twApi(int $twilio_acc_id, bool $force = false): TwilioApi
    {
        /**
         * @var User $user
         */
        $user_api = (new UserConnRepository())->getUserApi($twilio_acc_id, $force);
        return new TwilioApi($user_api->id);
    }

    /**
     * @param Request $request
     * @return array
     * @throws TwilioException
     */
    public function sendVoice(Request $request)
    {
        return [
            'status' => $this->twApi($request->twilio_acc_id)->voiceCall($request->to_number, $request->from_number ?: '')->toArray()
        ];
    }

    /**
     * @param Request $request
     * @return array
     * @throws ConfigurationException
     * @throws TwilioException
     */
    public function stopVoice(Request $request)
    {
        return [
            'status' => $this->twApi($request->twilio_acc_id)->stopCall($request->call_sid)->toArray()
        ];
    }

    /**
     * @param Request $request
     * @return string[]
     */
    public function storeCallHistory(Request $request)
    {
        //TwilioApi::storeCallHistory($request->all());
        return [
            'status' => 'ok'
        ];
    }

    /**
     * @param Request $request
     * @return array
     * @throws ConfigurationException
     * @throws TwilioException
     */
    public function browserToken(Request $request)
    {
        return [
            'token' => $this->twApi($request->twilio_acc_id)->browserToken(!!$request->incoming)
        ];
    }

    /**
     * @param Request $request
     * @return string
     * @throws ConfigurationException
     */
    public function incomingSMS(Request $request)
    {
        $accs = $this->twilioUserApi($request, true);
        foreach ($accs as $acc) {
            $this->twilioHistory->store(
                $acc->user_id,
                $request->To,
                TwilioHistory::$SMS_TYPE,
                [
                    "sms_to" => $request->To,
                    "sms_from" => $request->From,
                    "sms_message" => $request->Body
                ],
                null,
                null,
                null,
                $acc->search_key
            );
        }
        return (string)$this->twApi($accs->first()->id, true)->smsResponse();
    }

    /**
     * @param Request $request
     * @return Response
     * @throws ConfigurationException
     */
    public function browserCall(Request $request)
    {
        \Log::info('browserCall!');
        TwilioApi::storeCallHistory($request->all());

        $acc = $this->twilioUserApi($request);
        return response(
            (string)$this->twApi($acc->id, true)->browserCall($request->phoneNumber ?: '', $request->From ?: '')
            , 200, [
            'Content-Type' => 'application/xml'
        ]);
    }

    /**
     * @param Request $request
     * @return Response
     * @throws ConfigurationException
     */
    public function incomingDialFinished(Request $request)
    {
        \Log::info('incomingDialFinished!');
        TwilioApi::storeCallHistory($request->all());

        $missed = ($request['DialCallStatus'] ?? '') == 'no-answer';

        $acc = $this->twilioUserApi($request);
        if ($missed) {
            $this->saveBrowserCallHistory($acc->user_id, [
                'call_from' => $request->From,
                'call_to' => $request->To,
                'call_start' => Carbon::now()->toDateTimeString(),
                'call_duration' => 0,
            ], 1);
        }
        return response(
            (string)$this->twApi($acc->id, true)->incomingDialFinished($missed)
            , 200, [
            'Content-Type' => 'application/xml'
        ]);
    }

    /**
     * @param int $user_id
     * @param array $request
     * @param int $missed
     * @return TwilioHistory
     */
    protected function saveBrowserCallHistory(int $user_id, array $request, int $missed = 0)
    {
        return $this->twilioHistory->store(
            $user_id,
            $request['call_to'],
            TwilioHistory::$CALL_TYPE,
            [
                'call_from' => $request['call_from'],
                'call_to' => $request['call_to'],
                'call_start' => $request['call_start'],
                'call_duration' => $request['call_duration'],
            ],
            $request['table_id'] ?? null,
            $request['field_id'] ?? null,
            $request['row_id'] ?? null,
            null,
            $missed
        );
    }

    /**
     * @param Request $request
     * @param bool $many
     * @return UserApiKey|Collection<UserApiKey>
     * @throws Exception
     */
    protected function twilioUserApi(Request $request, bool $many = false)
    {
        $firstAcc = collect([]);
        $repo = new UserConnRepository();
        if ($request->twilioAccId) {
            $firstAcc[] = $repo->getUserApi($request->twilioAccId, true);
        }
        if (!$firstAcc->count() && $request->AccountSid) {
            $firstAcc = $repo->getTwilioAcc([$request->AccountSid]);
        }

        if (!$firstAcc->count()) {
            throw new Exception("Twilio Api Key not found!", 1);
        }

        return $many ? $firstAcc : $firstAcc->first();
    }

    /**
     * @param Request $request
     * @return TwilioHistory
     */
    public function browserCallHistory(Request $request)
    {
        return $this->saveBrowserCallHistory(auth()->id(), $request->all());
    }

    /**
     * @param Request $request
     * @return false[]
     * @throws Exception
     */
    public function removeHistory(Request $request)
    {
        return [
            'status' => $this->twilioHistory->ownerOf($request->history_id, auth()->id())
                && $this->twilioHistory->remove($request->history_id),
        ];
    }

    /**
     * @param Request $request
     * @return \Illuminate\Support\Collection
     */
    public function getIncomingSms(Request $request)
    {
        return $this->twilioHistory->getIncomingSms($request->incoming_app_id, $request->from_date);
    }

    /**
     * @param Request $request
     * @return TableTwilioRight
     * @throws AuthorizationException
     */
    public function toggleTwilioRight(Request $request)
    {
        $twilio = $this->twilioService->getTwilioSett($request->twilio_id);
        $this->authorizeForUser(auth()->user(), 'isOwner', [TableData::class, $twilio->_table]);
        return $this->twilioService->toggleTwilioRight($twilio, $request->permission_id, $request->can_edit);
    }

    /**
     * @param Request $request
     * @return mixed
     * @throws AuthorizationException
     */
    public function delTwilioRight(Request $request)
    {
        $twilio = $this->twilioService->getTwilioSett($request->twilio_id);
        $this->authorizeForUser(auth()->user(), 'isOwner', [TableData::class, $twilio->_table]);
        return $this->twilioService->deleteTwilioRight($twilio, $request->permission_id);
    }
}

<?php

namespace Vanguard\Modules\Twilio;

use Illuminate\Support\Facades\Log;
use Twilio\Exceptions\ConfigurationException;
use Twilio\Exceptions\TwilioException;
use Twilio\Jwt\AccessToken;
use Twilio\Jwt\ClientToken;
use Twilio\Jwt\Grants\VoiceGrant;
use Twilio\Rest\Api\V2010\Account\CallInstance;
use Twilio\Rest\Api\V2010\Account\MessageInstance;
use Twilio\Rest\Client;
use Twilio\TwiML\MessagingResponse;
use Twilio\TwiML\VoiceResponse;
use Vanguard\Models\User\UserApiKey;
use Vanguard\Repositories\Tablda\UserConnRepository;

class TwilioApi
{
    /**
     * @var UserApiKey
     */
    protected $user_api;
    /**
     * @var Client
     */
    protected $twilio;

    /**
     * @param int $user_api_id
     * @throws ConfigurationException
     */
    public function __construct(int $user_api_id)
    {
        $this->user_api = (new UserConnRepository())->getUserApi($user_api_id, true);
        $this->twilio = new Client($this->user_api->decryptedKey(), $this->user_api->decryptedToken());
    }

    /**
     * @return string|null
     */
    public function fromPhone()
    {
        return $this->user_api->twilio_phone;
    }

    /**
     * @param string $phone_to
     * @param string $message
     * @return MessageInstance
     * @throws \Exception
     */
    public function sendSMS(string $phone_to, string $message): MessageInstance
    {
        try {
            return $this->twilio
                ->messages
                ->create($phone_to, [
                    "body" => $message,
                    "from" => $this->user_api->twilio_phone,
                ]);
        } catch (TwilioException $e) {
            throw new \Exception($e->getMessage(), 1);
        }
    }

    /**
     * @param string $to_number
     * @param string $from_number
     * @return CallInstance
     * @throws \Exception
     */
    public function voiceCall(string $to_number, string $from_number): CallInstance
    {
        try {
            $twiml = new VoiceResponse();
            $twiml->say("Hello!");
            $twiml->say("Calling to number {$to_number}");
            $twiml->dial("{$to_number}");

            return $this->twilio
                ->calls
                ->create($from_number, $this->user_api->twilio_phone, [
                    'statusCallback' => config('app.url').'/twilio/store-call-history',
                    "twiml" => (string)$twiml,
                ]);
        } catch (TwilioException $e) {
            throw new \Exception($e->getMessage(), 1);
        }
    }

    /**
     * @param string $call_sid
     * @return CallInstance
     * @throws TwilioException
     */
    public function stopCall(string $call_sid): CallInstance
    {
        return $this->twilio
            ->calls($call_sid)
            ->update([
                "status" => "completed"
            ]);
    }

    /**
     * @param bool $incoming
     * @return string
     */
    public function browserToken(bool $incoming = false): string
    {
        if ($incoming) {
            $token = new AccessToken(
                $this->user_api->decryptedKey(),
                '',
                '',
                3600,
                'support_agent'
            );

            $voiceGrant = new VoiceGrant();
            $voiceGrant->setOutgoingApplicationSid($this->user_api->twiml_app_id);
            $voiceGrant->setIncomingAllow(true);

            $token->addGrant($voiceGrant);
            return $token->toJWT();
        } else {
            $token = new ClientToken($this->user_api->decryptedKey(), $this->user_api->decryptedToken());
            $token->allowClientOutgoing($this->user_api->twiml_app_id);
            return $token->generateToken();
        }
    }

    /**
     * @param string $phoneNumber
     * @param string $dialFrom
     * @return VoiceResponse
     */
    public function browserCall(string $phoneNumber = '', string $dialFrom = ''): VoiceResponse
    {
        $params = [ 'callerId' => $this->user_api->twilio_phone ];
        if (!$phoneNumber) {
            $params['action'] = config('app.url') . '/twilio/incoming-dial-finished';
        }

        $response = new VoiceResponse();
        $dial = $response->dial(null, $params);

        if ($phoneNumber) {
            $dial->number($phoneNumber);
        } else {
            $client = $dial->client('support_agent');
            if ($dialFrom) {
                $param = $client->parameter();
                $param->setName("dialFrom");
                $param->setValue($dialFrom);
            }
        }

        return $response;
    }

    /**
     * @param bool $missed
     * @return VoiceResponse
     */
    public function incomingDialFinished(bool $missed): VoiceResponse
    {
        $response = new VoiceResponse();
        if ($missed) {
            $dial = $response->say('The party you are calling is not available. Please try again later.');
        }
        return $response;
    }

    /**
     * @return MessagingResponse
     */
    public function smsResponse(): MessagingResponse
    {
        return new MessagingResponse();
    }

    /**
     * @param array $callbackParams
     */
    public static function storeCallHistory(array $callbackParams): void
    {
        \Log::info(json_encode($callbackParams, JSON_PRETTY_PRINT));
    }

    /**
     * @param string $message
     * @return VoiceResponse
     */
    public function incomingCallResponse(string $message): VoiceResponse
    {
        $response = new VoiceResponse();
        $response->say($message);
        return $response;
    }
}

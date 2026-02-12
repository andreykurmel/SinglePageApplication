<?php

namespace Vanguard\Modules\Twilio;

use Exception;
use Twilio\Exceptions\ConfigurationException;
use Twilio\Rest\Api\V2010\Account\MessageInstance;
use Vanguard\Models\Table\TableTwilioAddonSetting;
use Vanguard\Repositories\Tablda\TableData\FormulaEvaluatorRepository;
use Vanguard\Repositories\Tablda\UserConnRepository;
use Vanguard\Services\Tablda\HelperService;

class TwilioSmsObject
{
    /**
     * @var TwilioApi
     */
    protected $api;
    /**
     * @var string|null
     */
    protected $phone_from;
    /**
     * @var array
     */
    protected $phone_to;
    /**
     * @var string|null
     */
    protected $message;
    /**
     * @var array
     */
    protected $row;

    /**
     * @param TableTwilioAddonSetting $twilio
     * @param array $row
     * @throws ConfigurationException
     */
    public function __construct(TableTwilioAddonSetting $twilio, array $row)
    {
        $this->api = new TwilioApi($twilio->acc_twilio_key_id);
        $userApi = (new UserConnRepository())->getUserApi($twilio->acc_twilio_key_id, true);

        $service = new HelperService();
        $recipients = $service->addRecipientsPhones([], $row[$twilio->_recipient_field->field ?? ''] ?? '');
        $recipients = $service->addRecipientsPhones($recipients, $twilio->recipient_phones ?? '');

        $parser = new FormulaEvaluatorRepository($twilio->_table, $twilio->_table->user_id, true);
        $body = $parser->formulaReplaceVars($row, $twilio->sms_body);

        $this->phone_from = $userApi->twilio_phone;
        $this->phone_to = $recipients;
        $this->message = $body;
        $this->row = $row;
    }

    /**
     * @param bool $encode
     * @return array
     */
    public function preview(bool $encode = false): array
    {
        return [
            'preview_from' => $this->phone_from,
            'preview_to' => $encode ? json_encode($this->phone_to) : $this->phone_to,
            'preview_body' => $this->message,
            'preview_row' => $encode ? json_encode($this->row) : $this->row,
        ];
    }

    /**
     * @return MessageInstance
     * @throws Exception
     */
    public function send()
    {
        $response = null;
        foreach ($this->phone_to as $phone) {
            if ($phone && $this->message) {
                $response = $this->api->sendSMS($phone, $this->message);
            }
        }
        return $response;
    }

}
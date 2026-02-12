<?php

namespace Vanguard\Http\Requests\Tablda\Email;

use Illuminate\Foundation\Http\FormRequest;

class SendSingleEmailRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'twilio_google_acc_id' => 'required_without:twilio_sendgrid_acc_id|nullable|integer|exists:user_api_keys,id',
            'twilio_sendgrid_acc_id' => 'required_without:twilio_google_acc_id|nullable|integer|exists:user_api_keys,id',
            'field_id' => 'required|integer|exists:table_fields,id',
            'row_id' => 'required|integer',
            'email_data' => 'required|array',
            'email_data.from' => 'required|string',
            'email_data.from_name' => 'nullable|string',
            'email_data.to' => 'required|string',
            'email_data.reply_to' => 'nullable|string',
            'email_data.subject' => 'required|string',
            'email_data.body' => 'required|string',
        ];
    }
}

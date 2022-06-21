<?php

namespace Vanguard\Http\Requests\Tablda\Import;

use Illuminate\Foundation\Http\FormRequest;

class SendAirtableRequest extends FormRequest
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
            'user_key_id' => 'required|int|exists:user_api_keys,id',
            'table_name' => 'required|string',
            'fromtype' => 'required|string',
        ];
    }
}

<?php

namespace Vanguard\Http\Requests\Tablda\Import;

use Illuminate\Foundation\Http\FormRequest;

class SendWebScrapRequest extends FormRequest
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
            'web_action' => 'required|string',
            'web_url' => 'string|nullable',
            'web_query' => 'string|nullable',
            'web_xpath' => 'string|nullable',
            'web_index' => 'string|nullable',
            'web_xml_file' => 'string|nullable',
            'web_scrap_headers' => 'nullable',
        ];
    }
}

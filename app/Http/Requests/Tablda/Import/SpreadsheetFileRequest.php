<?php

namespace Vanguard\Http\Requests\Tablda\Import;

use Illuminate\Foundation\Http\FormRequest;

class SpreadsheetFileRequest extends FormRequest
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
            'new_path' => 'required_without:spreadsheet_id|string',
            'file_id' => 'required_without:spreadsheet_id|string',
            'spreadsheet_id' => 'required_without:file_id|string',
            'cloud_id' => 'required|integer|exists:user_clouds,id',
        ];
    }
}

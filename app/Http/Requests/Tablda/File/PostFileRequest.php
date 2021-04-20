<?php

namespace Vanguard\Http\Requests\Tablda\File;

use Illuminate\Foundation\Http\FormRequest;

class PostFileRequest extends FormRequest
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
            'table_id' => 'required|integer|exists:tables,id',
            'table_field_id' => 'present',
            'row_id' => 'present',
            'file' => 'required_without:file_link',
            'file_link' => 'required_without:file',
            'file_new_name' => 'string|nullable',
            'clear_before' => 'boolean'
        ];
    }
}

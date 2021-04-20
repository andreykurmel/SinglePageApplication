<?php

namespace Vanguard\Http\Requests\Tablda\File;

use Illuminate\Foundation\Http\FormRequest;

class DeleteFileRequest extends FormRequest
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
            'id' => 'required|integer|exists:files,id',
            'table_id' => 'required|integer|exists:tables,id',
            'table_field_id' => 'present',
            'row_id' => 'present',
        ];
    }
}

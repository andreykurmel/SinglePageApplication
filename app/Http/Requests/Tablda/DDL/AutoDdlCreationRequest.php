<?php

namespace Vanguard\Http\Requests\Tablda\DDL;

use Illuminate\Foundation\Http\FormRequest;

class AutoDdlCreationRequest extends FormRequest
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
            'request_params' => 'required|array',
            'is_ignored' => 'required|boolean',
            'names_fld_id' => 'required|integer|exists:table_fields,id',
            'options_fld_id' => 'required|integer|exists:table_fields,id',
        ];
    }
}

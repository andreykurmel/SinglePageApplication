<?php

namespace Vanguard\Http\Requests\Tablda\DDL;

use Illuminate\Foundation\Http\FormRequest;

class NewOptionRequest extends FormRequest
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
            'ddl_id' => 'required|integer|exists:ddl,id',
            'new_val' => 'required|string',
            'ddl_ref_id' => 'integer',
            'fields' => 'array',
            'extra_options' => 'array',
        ];
    }
}

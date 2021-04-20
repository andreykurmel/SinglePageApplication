<?php

namespace Vanguard\Http\Requests\Tablda;

use Illuminate\Foundation\Http\FormRequest;

class UpdateSettingsRowRequest extends FormRequest
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
            'table_field_id' => 'required|integer|exists:table_fields,id',
            'field' => 'required|string',
            'val' => 'nullable',
            'recalc_ids' => 'nullable|array',
        ];
    }
}

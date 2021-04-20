<?php

namespace Vanguard\Http\Requests\Tablda\Table;

use Illuminate\Foundation\Http\FormRequest;

class GetFilterUrlRequest extends FormRequest
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
            'from_table_id' => 'required|integer|exists:tables,id',
            'table_id' => 'required|integer|exists:tables,id',
            'field_id' => 'required|integer|exists:table_fields,id',
            'value' => 'present',
        ];
    }
}

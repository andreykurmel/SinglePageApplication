<?php

namespace Vanguard\Http\Requests\Tablda\TableData;

use Illuminate\Foundation\Http\FormRequest;

class BatchAutoselectRequest extends FormRequest
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
            'select_field_id' => 'required|integer|exists:table_fields,id',
            'auto_comparison' => 'required|string',
            'row_group_id' => 'nullable|integer|exists:table_row_groups,id',
        ];
    }
}

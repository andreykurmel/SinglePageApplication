<?php

namespace Vanguard\Http\Requests\Tablda\TableRowGroup;

use Illuminate\Foundation\Http\FormRequest;

class TableRowGroupUpdateRegularRequest extends FormRequest
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
            'table_row_group_regular_id' => 'required|integer|exists:table_row_group_regulars,id',
            'fields' => 'required|array',
        ];
    }
}

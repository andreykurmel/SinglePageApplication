<?php

namespace Vanguard\Http\Requests\Tablda\TableColGroup;

use Illuminate\Foundation\Http\FormRequest;

class TableColGroupUpdateRequest extends FormRequest
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
            'table_column_group_id' => 'required|integer|exists:table_column_groups,id',
            'fields' => 'required|array',
        ];
    }
}

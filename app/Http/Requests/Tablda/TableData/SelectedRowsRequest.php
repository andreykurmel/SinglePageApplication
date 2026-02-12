<?php

namespace Vanguard\Http\Requests\Tablda\TableData;

use Illuminate\Foundation\Http\FormRequest;

class SelectedRowsRequest extends FormRequest
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
            'rows_ids' => 'required_without:request_params',
            'request_params' => 'required_without:rows_ids',
            'replaces' => 'nullable|array',
            'only_columns' => 'nullable|array',
            'no_inheritance_ids' => 'nullable|array',
        ];
    }
}

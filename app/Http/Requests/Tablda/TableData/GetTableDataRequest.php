<?php

namespace Vanguard\Http\Requests\Tablda\TableData;

use Illuminate\Foundation\Http\FormRequest;
use Vanguard\Models\Table\Table;

class GetTableDataRequest extends FormRequest
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
            'ref_cond_id' => 'required_without:table_id|nullable|exists:table_ref_conditions,id',
            'table_id' => 'required_without:ref_cond_id|nullable|exists:tables,id',
            'page' => 'required|integer|min:1',
            'rows_per_page' => 'required|integer|min:0',
            'sort' => 'array',
            'special_params' => 'array'
        ];
    }
}

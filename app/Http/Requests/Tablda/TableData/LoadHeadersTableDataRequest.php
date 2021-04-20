<?php

namespace Vanguard\Http\Requests\Tablda\TableData;

use Illuminate\Foundation\Http\FormRequest;

class LoadHeadersTableDataRequest extends FormRequest
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
            'table_id' => 'required_without:ref_cond_id|integer|exists:tables,id',
            'ref_cond_id' => 'required_without:table_id|integer|exists:table_ref_conditions,id',
            'user_id' => 'integer|nullable',
            'special_params' => 'array'
        ];
    }
}

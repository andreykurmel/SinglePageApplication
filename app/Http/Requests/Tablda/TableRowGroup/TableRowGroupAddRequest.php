<?php

namespace Vanguard\Http\Requests\Tablda\TableRowGroup;

use Illuminate\Foundation\Http\FormRequest;

class TableRowGroupAddRequest extends FormRequest
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
            'fields' => 'required|array',
        ];
    }
}

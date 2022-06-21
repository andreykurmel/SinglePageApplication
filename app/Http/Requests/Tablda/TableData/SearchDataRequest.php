<?php

namespace Vanguard\Http\Requests\Tablda\TableData;

use Illuminate\Foundation\Http\FormRequest;

class SearchDataRequest extends FormRequest
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
            'term' => 'required|string',
            'columns' => 'required|array',
            'special_params' => 'required|array',
        ];
    }
}

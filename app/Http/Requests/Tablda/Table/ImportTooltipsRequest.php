<?php

namespace Vanguard\Http\Requests\Tablda\Table;

use Illuminate\Foundation\Http\FormRequest;

class ImportTooltipsRequest extends FormRequest
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
            'options' => 'required|array',
            'table_id' => 'required|integer|exists:tables,id',
        ];
    }
}

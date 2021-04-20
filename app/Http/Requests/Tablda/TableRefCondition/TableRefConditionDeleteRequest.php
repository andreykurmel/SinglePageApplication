<?php

namespace Vanguard\Http\Requests\Tablda\TableRefCondition;

use Illuminate\Foundation\Http\FormRequest;

class TableRefConditionDeleteRequest extends FormRequest
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
            'table_ref_condition_id' => 'required|integer|exists:table_ref_conditions,id',
        ];
    }
}

<?php

namespace Vanguard\Http\Requests\Tablda;

use Illuminate\Foundation\Http\FormRequest;

class GetTableRequest extends FormRequest
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
            'table_id' => 'required_without:id_object|integer|exists:tables,id',
            'id_object' => 'required_without:table_id',
        ];
    }
}

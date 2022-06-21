<?php

namespace Vanguard\Http\Requests\Tablda\TablePermission;

use Illuminate\Foundation\Http\FormRequest;

class LinkedTableDirectRequest extends FormRequest
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
            'table_linked_id' => 'required|integer|exists:dcr_linked_tables,id',
        ];
    }
}

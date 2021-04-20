<?php

namespace Vanguard\Http\Requests\Tablda\TableData;

use Illuminate\Foundation\Http\FormRequest;

class PostTableDataRequest extends FormRequest
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
     * 'fields' must be an array like:
     * {
     *      'table_field': 'value',
     *      'table_field': 'value',
     *      ...
     * }
     *
     * @return array
     */
    public function rules()
    {
        return [
            'table_id' => 'required|integer|exists:tables,id',
            'fields' => 'required|array',
            'get_query' => 'required|array',
            'table_permission_id' => 'integer|exists:table_permissions,id',
            'table_permission_pass' => 'string|nullable',
            'from_link_id' => 'integer|nullable',
        ];
    }
}

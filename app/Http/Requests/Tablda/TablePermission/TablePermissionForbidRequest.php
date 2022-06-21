<?php

namespace Vanguard\Http\Requests\Tablda\TablePermission;

use Illuminate\Foundation\Http\FormRequest;

class TablePermissionForbidRequest extends FormRequest
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
            'table_permission_id' => 'required|integer|exists:table_permissions,id',
            'db_col_name' => 'nullable|string',
        ];
    }
}

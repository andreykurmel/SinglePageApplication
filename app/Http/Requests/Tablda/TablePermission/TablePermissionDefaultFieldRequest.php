<?php

namespace Vanguard\Http\Requests\Tablda\TablePermission;

use Illuminate\Foundation\Http\FormRequest;

class TablePermissionDefaultFieldRequest extends FormRequest
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
            'user_group_id' => 'nullable|integer|exists:user_groups,id',
            'table_field_id' => 'required|integer|exists:table_fields,id',
            'default_val' => 'present',
        ];
    }
}

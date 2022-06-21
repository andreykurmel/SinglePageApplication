<?php

namespace Vanguard\Http\Requests\Tablda\TablePermission;

use Illuminate\Foundation\Http\FormRequest;

class TablePermissionCopyRequest extends FormRequest
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
            'from_permis_id' => 'required|integer|exists:table_permissions,id',
            'to_permis_id' => 'required|integer|exists:table_permissions,id',
        ];
    }
}

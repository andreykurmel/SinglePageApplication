<?php

namespace Vanguard\Http\Requests\Tablda\TablePermission;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class AddonRightUpdateRequest extends FormRequest
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
            'addon_id' => 'required|integer|exists:addons,id',
            'table_permission_id' => 'required|integer|exists:table_permissions,id',
            'fld' => 'required|string',
            'val' => 'nullable',
        ];
    }
}

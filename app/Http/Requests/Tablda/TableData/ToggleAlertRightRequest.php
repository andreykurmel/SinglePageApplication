<?php

namespace Vanguard\Http\Requests\Tablda\TableData;

use Illuminate\Foundation\Http\FormRequest;

class ToggleAlertRightRequest extends FormRequest
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
            'alert_id' => 'present|integer|exists:table_alerts,id',
            'permission_id' => 'present|integer|exists:table_permissions,id',
            'can_edit' => 'required|integer',
            'can_activate' => 'required|integer',
        ];
    }
}

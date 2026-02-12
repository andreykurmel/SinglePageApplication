<?php

namespace Vanguard\Http\Requests\Tablda\TablePermission;

use Illuminate\Foundation\Http\FormRequest;

class TableDcrCopyRequest extends FormRequest
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
            'from_data_request_id' => 'required|integer|exists:table_data_requests,id',
            'to_data_request_id' => 'required|integer|exists:table_data_requests,id',
            'full_copy' => 'integer|nullable',
        ];
    }
}

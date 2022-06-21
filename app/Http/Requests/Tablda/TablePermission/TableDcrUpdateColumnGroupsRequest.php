<?php

namespace Vanguard\Http\Requests\Tablda\TablePermission;

use Illuminate\Foundation\Http\FormRequest;

class TableDcrUpdateColumnGroupsRequest extends FormRequest
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
            'table_data_request_id' => 'required|integer|exists:table_data_requests,id',
            'table_column_group_id' => 'required|integer|exists:table_column_groups,id',
            'view' => 'required|integer',
            'edit' => 'required|integer',
        ];
    }
}

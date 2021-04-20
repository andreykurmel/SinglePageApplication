<?php

namespace Vanguard\Http\Requests\Tablda\TableData;

use Illuminate\Foundation\Http\FormRequest;

class SaveChartRequest extends FormRequest
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
            'id' => 'present|integer|nullable|exists:table_charts,id',
            'table_id' => 'required|integer|exists:tables,id',
            'row_idx' => 'required|integer|min:0',
            'col_idx' => 'required|integer|min:0',
            'all_settings' => 'required|array',
            'should_del' => 'integer|nullable',
            'request_params' => 'array',
            'changed_param' => 'string',
        ];
    }
}

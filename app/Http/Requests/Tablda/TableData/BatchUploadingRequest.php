<?php

namespace Vanguard\Http\Requests\Tablda\TableData;

use Illuminate\Foundation\Http\FormRequest;

class BatchUploadingRequest extends FormRequest
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
            'url_field_id' => 'required|integer|exists:table_fields,id',
            'attach_field_id' => 'required|integer|exists:table_fields,id',
            'row_group_id' => 'nullable|integer|exists:table_row_groups,id',
        ];
    }
}

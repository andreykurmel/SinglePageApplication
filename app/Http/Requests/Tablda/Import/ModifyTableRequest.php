<?php

namespace Vanguard\Http\Requests\Tablda\Import;

use Illuminate\Foundation\Http\FormRequest;

class ModifyTableRequest extends FormRequest
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
            'table_id' => 'required|integer|exists:tables,id',
            'columns' => 'required|array',
            'present_cols_idx' => 'required|integer',
            'import_type' => 'required|string',
            'import_action' => 'required|string',
            'csv_settings' => 'required|array',
            'mysql_settings' => 'required|array',
            'paste_settings' => 'required|array',
            'paste_file' => 'nullable|string',
            'html_xml' => 'nullable|array',
            'g_sheet' => 'nullable|array',
            'referenced_table' => 'nullable',
        ];
    }
}

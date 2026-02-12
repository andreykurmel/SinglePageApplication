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
            'new_table' => 'nullable|array',
            'table_id' => 'required_without:new_table|integer|exists:tables,id',
            'columns' => 'required|array',
            'present_cols_idx' => 'required|integer',
            'import_type' => 'required|string',
            'import_action' => 'required|string',
            'csv_settings' => 'required|array',
            'mysql_settings' => 'nullable|array',
            'paste_settings' => 'nullable|array',
            'paste_file' => 'nullable|string',
            'transpose_item' => 'nullable|array',
            'html_xml' => 'nullable|array',
            'g_sheets' => 'nullable|array',
            'ocr_data' => 'nullable|array',
            'airtable_data' => 'nullable|array',
            'referenced_table' => 'nullable|integer',
        ];
    }
}

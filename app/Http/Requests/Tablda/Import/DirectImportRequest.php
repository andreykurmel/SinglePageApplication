<?php

namespace Vanguard\Http\Requests\Tablda\Import;

use Illuminate\Foundation\Http\FormRequest;

class DirectImportRequest extends FormRequest
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
            'import_type' => 'required|string',
            'csv_settings' => 'nullable|array',
            'mysql_settings' => 'nullable|array',
            'paste_settings' => 'nullable|array',
            'paste_file' => 'nullable|string',
            'g_sheets_settings' => 'nullable|array',
            'g_sheets_link' => 'nullable|string',
            'g_sheets_name' => 'nullable|string',
            'referenced_table' => 'nullable|array',
        ];
    }
}

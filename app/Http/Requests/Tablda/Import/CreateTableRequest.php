<?php

namespace Vanguard\Http\Requests\Tablda\Import;

use Illuminate\Foundation\Http\FormRequest;

class CreateTableRequest extends FormRequest
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
            'name' => 'required|string',
            'rows_per_page' => 'required|integer',
            'notes' => 'nullable|string',
            'folder_id' => 'nullable|integer',
        ];
    }
}

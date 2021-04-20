<?php

namespace Vanguard\Http\Requests\Tablda\TableData;

use Illuminate\Foundation\Http\FormRequest;
use Vanguard\Models\Table\Table;

class GetDDLvaluesRequest extends FormRequest
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
            'ddl_id' => 'required|integer|exists:ddl,id',
            'row' => 'present|array',
            'search' => 'nullable|string',
        ];
    }
}

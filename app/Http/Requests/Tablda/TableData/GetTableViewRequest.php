<?php

namespace Vanguard\Http\Requests\Tablda\TableData;

use Illuminate\Foundation\Http\FormRequest;
use Vanguard\Models\Table\Table;

class GetTableViewRequest extends FormRequest
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
            'hash' => 'required|string|exists:table_views,hash',
            'page' => 'required|integer|min:1',
            'rows_per_page' => 'required|integer|min:0',
        ];
    }
}

<?php

namespace Vanguard\Http\Requests\Tablda\TableView;

use Illuminate\Foundation\Http\FormRequest;

class PassTableViewRequest extends FormRequest
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
            'tb_view_hash' => 'nullable',
            'fld_view_hash' => 'nullable',
            'app_view_hash' => 'nullable',
            'pass' => 'nullable|string',
        ];
    }
}

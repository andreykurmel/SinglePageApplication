<?php

namespace Vanguard\Http\Requests\Tablda\TableView;

use Illuminate\Foundation\Http\FormRequest;

class DeleteViewRightRequest extends FormRequest
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
            'table_view_id' => 'required|integer|exists:table_views,id',
            'table_view_right_id' => 'required|integer|exists:table_view_rights,id',
        ];
    }
}

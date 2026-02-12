<?php

namespace Vanguard\Http\Requests\Tablda\Table;

use Illuminate\Foundation\Http\FormRequest;

class FavoriteToggleRequest extends FormRequest
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
            'parent_id' => 'nullable|integer|exists:folders,id',
            'favorite' => 'required|boolean',
        ];
    }
}

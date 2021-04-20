<?php

namespace Vanguard\Http\Requests\Tablda;

use Illuminate\Foundation\Http\FormRequest;

class MoveNodeRequest extends FormRequest
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
            'id' => 'required|integer',
            'link_id' => 'nullable|integer|exists:folders_2_tables,id',
            'folder_id' => 'nullable|integer|exists:folders,id',
            'position' => 'required|integer'
        ];
    }
}

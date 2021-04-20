<?php

namespace Vanguard\Http\Requests\Tablda\Folder;

use Illuminate\Foundation\Http\FormRequest;

class PostFolderRequest extends FormRequest
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
            'parent_id' => 'nullable|integer',
            'name' => 'required|string',
            'structure' => 'required|string',
            'in_shared' => 'required|integer|in:0,1',
        ];
    }
}

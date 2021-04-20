<?php

namespace Vanguard\Http\Requests\Tablda\FolderPermission;

use Illuminate\Foundation\Http\FormRequest;

class FolderViewAddRequest extends FormRequest
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
            'user_link' => 'nullable|string',
            'folder_id' => 'required|integer|exists:folders,id',
        ];
    }
}

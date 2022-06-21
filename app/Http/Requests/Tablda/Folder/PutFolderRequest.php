<?php

namespace Vanguard\Http\Requests\Tablda\Folder;

use Illuminate\Foundation\Http\FormRequest;

class PutFolderRequest extends FormRequest
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
            'folder_id' => 'required|integer',
            'fields' => 'required'
        ];
    }
}

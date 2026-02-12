<?php

namespace Vanguard\Http\Requests\Tablda\FolderPermission;

use Illuminate\Foundation\Http\FormRequest;

class FolderViewChangeRequest extends FormRequest
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
            'folder_view_id' => 'required|integer|exists:folder_views,id',
            'fields' => 'required|array',
        ];
    }
}

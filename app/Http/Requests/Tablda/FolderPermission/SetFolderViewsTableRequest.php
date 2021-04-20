<?php

namespace Vanguard\Http\Requests\Tablda\FolderPermission;

use Illuminate\Foundation\Http\FormRequest;

class SetFolderViewsTableRequest extends FormRequest
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
            'table_id' => 'required|integer|exists:tables,id',
            'assigned_view_id' => 'nullable|integer|exists:table_views,id',
        ];
    }
}

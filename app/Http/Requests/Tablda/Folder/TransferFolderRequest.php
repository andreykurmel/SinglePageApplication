<?php

namespace Vanguard\Http\Requests\Tablda\Folder;

use Illuminate\Foundation\Http\FormRequest;

class TransferFolderRequest extends FormRequest
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
            'id' => 'required|integer|exists:folders,id',
            'new_user_id' => 'required|integer|exists:users,id',
        ];
    }
}

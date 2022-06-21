<?php

namespace Vanguard\Http\Requests\Tablda\Import;

use Illuminate\Foundation\Http\FormRequest;

class OneDriveFileRequest extends FormRequest
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
            'extension' => 'required_without:file_id|string',
            'new_path' => 'required_without:extension|string',
            'file_id' => 'required_without:extension|string',
            'cloud_id' => 'required|integer|exists:user_clouds,id',
        ];
    }
}

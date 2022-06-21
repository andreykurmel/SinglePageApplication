<?php

namespace Vanguard\Http\Requests\Tablda\Import;

use Illuminate\Foundation\Http\FormRequest;

class DropboxFileRequest extends FormRequest
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
            'extensions' => 'required_without:file_id|array',
            'new_path' => 'required_without:extensions|string',
            'file_id' => 'required_without:extensions|string',
            'cloud_id' => 'required|integer|exists:user_clouds,id',
        ];
    }
}

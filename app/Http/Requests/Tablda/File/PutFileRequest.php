<?php

namespace Vanguard\Http\Requests\Tablda\File;

use Illuminate\Foundation\Http\FormRequest;

class PutFileRequest extends FormRequest
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
            'id' => 'required|integer|exists:files,id',
            'filename' => 'nullable|string',
            'notes' => 'nullable|string',
        ];
    }
}

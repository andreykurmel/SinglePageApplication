<?php

namespace Vanguard\Http\Requests\Tablda\StaticPages;

use Illuminate\Foundation\Http\FormRequest;

class PageMoveRequest extends FormRequest
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
            'page_id' => 'required|integer|exists:static_pages,id',
            'folder_id' => 'required|integer|exists:static_pages,id',
            'position' => 'required|integer',
            'type' => 'required|string',
        ];
    }
}

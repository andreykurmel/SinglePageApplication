<?php

namespace Vanguard\Http\Requests\Tablda\TableFieldLink;

use Illuminate\Foundation\Http\FormRequest;

class TableFieldLinkUpdateRequest extends FormRequest
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
            'table_link_id' => 'required|integer|exists:table_field_links,id',
            'fields' => 'required|array',
        ];
    }
}

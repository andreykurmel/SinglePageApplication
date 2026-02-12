<?php

namespace Vanguard\Http\Requests\Tablda\Table;

use Illuminate\Foundation\Http\FormRequest;

class FillMrvUrlRequest extends FormRequest
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
            'link_id' => 'required|integer|exists:table_field_links,id',
            'mrv_id' => 'required|integer|exists:table_views,id',
            'target_field_id' => 'required|integer|exists:table_fields,id',
            'custom_url_field_id' => 'nullable|integer|exists:table_fields,id',
        ];
    }
}

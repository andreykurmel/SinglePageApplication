<?php

namespace Vanguard\Http\Requests\Tablda\CondFormat;

use Illuminate\Foundation\Http\FormRequest;

class CondFormatDeleteRequest extends FormRequest
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
            'cond_format_id' => 'required|integer|exists:cond_formats,id',
        ];
    }
}

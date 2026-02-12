<?php

namespace Vanguard\Http\Requests\Tablda\App;

use Illuminate\Foundation\Http\FormRequest;

class AppThemeEditRequest extends FormRequest
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
            'theme_id' => 'required|integer|exists:app_themes,id',
            'fields' => 'required|array'
        ];
    }
}

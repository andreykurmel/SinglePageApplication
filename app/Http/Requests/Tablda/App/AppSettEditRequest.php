<?php

namespace Vanguard\Http\Requests\Tablda\App;

use Illuminate\Foundation\Http\FormRequest;

class AppSettEditRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return auth()->user() && auth()->user()->canEditStatic();
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'app_key' => 'required|string',
            'app_val' => 'present'
        ];
    }
}

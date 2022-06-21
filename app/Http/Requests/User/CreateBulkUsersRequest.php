<?php

namespace Vanguard\Http\Requests\User;

use Vanguard\Http\Requests\Request;
use Vanguard\User;

class CreateBulkUsersRequest extends Request
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'csv_emails' => 'required_without:pasted_emails|file',
            'pasted_emails' => 'required_without:csv_emails|string',
        ];
    }
}

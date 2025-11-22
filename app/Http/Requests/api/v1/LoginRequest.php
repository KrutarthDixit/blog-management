<?php

namespace App\Http\Requests\api\v1;

class LoginRequest extends BaseApiRequest
{
    public function rules(): array
    {
        return [
            'email' => ['required', 'email', 'max:255'],
            'password' => ['required', 'string', 'min:6', 'max:255'],
        ];
    }
}

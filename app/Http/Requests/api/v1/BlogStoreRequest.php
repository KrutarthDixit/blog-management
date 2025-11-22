<?php

namespace App\Http\Requests\api\v1;

use App\Http\Requests\api\v1\BaseApiRequest;

class BlogStoreRequest extends BaseApiRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'title' => ['required', 'string', 'max:255'],
            'content' => ['required', 'string'],
            'image' => ['required', 'image', 'max:2048'],
        ];
    }
}

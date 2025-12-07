<?php

namespace App\Modules\Auth;

use Illuminate\Foundation\Http\FormRequest;


class VerifyCodeRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'email' => ['required', 'email', 'exists:users,email'],
            'code'  => ['required', 'string'],
        ];
    }
}



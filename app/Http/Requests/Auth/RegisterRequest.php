<?php

namespace App\Http\Requests\Auth;

use Illuminate\Foundation\Http\FormRequest;

class RegisterRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name'             => ['required', 'string', 'max:255'],
            'email' => [
                'required',
                'email',
                function ($attribute, $value, $fail) {
                    if (\App\Models\User::where('email', $value)->exists() || \App\Models\Mentor::where('email', $value)->exists()) {
                        $fail('Email sudah terdaftar. Silakan gunakan email lain.');
                    }
                },
            ],
            'password'         => ['required', 'string', 'min:8', 'confirmed'],
            'university'       => ['nullable', 'string', 'max:255'],
            'beasiswa_diampu'  => ['nullable', 'array'],
            'beasiswa_diampu.*'=> ['string'],
        ];
    }
}

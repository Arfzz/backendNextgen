<?php

namespace App\Http\Requests\Mentor;

use Illuminate\Foundation\Http\FormRequest;

class StoreMentoringRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        return [
            'title'        => ['required', 'string', 'max:255'],
            'session_date' => ['required', 'date', 'after:now'],
            'link'         => ['required', 'url'],
        ];
    }
}

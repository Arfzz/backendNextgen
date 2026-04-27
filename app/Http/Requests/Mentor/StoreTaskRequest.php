<?php

namespace App\Http\Requests\Mentor;

use Illuminate\Foundation\Http\FormRequest;

class StoreTaskRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        return [
            'title'         => ['required', 'string', 'max:255'],
            'description'   => ['required', 'string'],
            'deadline_date' => ['required', 'date', 'after:today'],
        ];
    }
}

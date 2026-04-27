<?php

namespace App\Http\Requests\Mentor;

use Illuminate\Foundation\Http\FormRequest;

class UpdateTaskRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        return [
            'title'         => ['sometimes', 'string', 'max:255'],
            'description'   => ['sometimes', 'string'],
            'deadline_date' => ['sometimes', 'date', 'after:today'],
        ];
    }
}

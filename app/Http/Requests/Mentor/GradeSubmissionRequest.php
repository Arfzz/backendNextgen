<?php

namespace App\Http\Requests\Mentor;

use Illuminate\Foundation\Http\FormRequest;

class GradeSubmissionRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        return [
            'score'    => ['required', 'integer', 'min:0', 'max:100'],
            'feedback' => ['nullable', 'string', 'max:2000'],
        ];
    }
}

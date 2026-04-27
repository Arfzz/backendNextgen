<?php

namespace App\Http\Requests\Student;

use Illuminate\Foundation\Http\FormRequest;

class SubmitTaskRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'file' => ['required', 'file', 'mimes:pdf,doc,docx', 'max:10240'],
        ];
    }
}

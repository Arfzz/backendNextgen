<?php

namespace App\Http\Requests\Mentor;

use Illuminate\Foundation\Http\FormRequest;

class StoreDocumentRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        return [
            'title' => ['required', 'string', 'max:255'],
            'file'  => ['required', 'file', 'mimes:pdf,doc,docx,jpg,png,jpeg', 'max:20480'],
        ];
    }
}

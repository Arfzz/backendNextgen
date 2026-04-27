<?php

namespace App\Http\Requests\Mentor;

use Illuminate\Foundation\Http\FormRequest;

class StoreCheckpointRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        return [
            'title'         => ['required', 'string', 'max:255'],
            'schedule_date' => ['required', 'date'],
            'order_index'   => ['required', 'integer', 'min:1'],
        ];
    }
}

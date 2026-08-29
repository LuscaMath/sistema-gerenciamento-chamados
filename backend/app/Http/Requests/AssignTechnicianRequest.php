<?php

namespace App\Http\Requests;

use App\Enums\UserRole;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class AssignTechnicianRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'technician_id' => [
                'required',
                'integer',
                Rule::exists('users', 'id')
                    ->where('role', UserRole::TECHNICIAN->value),
            ],
        ];
    }
}

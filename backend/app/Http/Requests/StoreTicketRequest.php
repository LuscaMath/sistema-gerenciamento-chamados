<?php

namespace App\Http\Requests;

use App\Enums\TicketPriority;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreTicketRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'category_id' => [
                'required',
                'integer',
                Rule::exists('categories', 'id')
                    ->where('is_active', true),
            ],
            'title' => ['required', 'string', 'max:255'],
            'description' => ['required', 'string'],
            'priority' => [
                'required',
                Rule::enum(TicketPriority::class),
            ],
        ];
    }
}
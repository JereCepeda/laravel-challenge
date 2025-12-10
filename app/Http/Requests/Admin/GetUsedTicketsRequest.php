<?php

namespace App\Http\Requests\Admin;
use Illuminate\Foundation\Http\FormRequest;

class GetUsedTicketsRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'event_name' => 'nullable|string|max:255',
            'sector' => 'nullable|string|max:50',
            'event_date' => 'nullable|date',
            'from_date' => 'nullable|date|before_or_equal:today',
            'to_date' => 'nullable|date|after_or_equal:from_date',
            'per_page' => 'nullable|integer|min:5|max:100',
        ];
    }
    
    public function messages(): array
    {
        return [
            'event_name.string' => 'The event name must be a string.',
            'event_name.max' => 'The event name may not be greater than 255 characters.',
            'sector.string' => 'The sector must be a string.',
            'sector.max' => 'The sector may not be greater than 50 characters.',
            'event_date.date' => 'The event date must be a valid date.',
            'from_date.date' => 'The from date must be a valid date.',
            'from_date.before_or_equal' => 'The from date must be a date before or equal to today.',
            'to_date.date' => 'The to date must be a valid date.',
            'to_date.after_or_equal' => 'The to date must be a date after or equal to from date.',
            'per_page.integer' => 'The per page must be an integer.',
            'per_page.min' => 'The per page must be at least 5.',
            'per_page.max' => 'The per page may not be greater than 100.',
        ];
    }
}
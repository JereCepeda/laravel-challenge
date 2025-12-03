<?php

namespace App\Http\Requests\Admin;
use Illuminate\Foundation\Http\FormRequest;

class GetRedemptionHistoryRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() && $this->user()->hasRole('admin'); 
    }

    public function rules(): array
    {
        return [
            'event_name' => 'sometimes|string|max:255',
            'event_date' => 'sometimes|date',
            'sector' => 'sometimes|string|max:100',
            'guest_count' => 'sometimes|integer|min:1',
            'tickets_generated' => 'sometimes|integer|min:1',
            'redeemed_at' => 'sometimes|date',
            'from_date' => 'sometimes|date',
            'to_date' => 'sometimes|date|after_or_equal:from_date',
            'per_page'=>'sometimes|integer|min:1|max:100',
            'page'=>'sometimes|integer|min:1',
        ];
    }
    public function messages()
    {
        return [
            'from_date.date' => 'The from date must be a valid date.',
            'to_date.date' => 'The to date must be a valid date.',
            'to_date.after_or_equal' => 'The to date must be a date after or equal to from date.',
            'event_name.string' => 'The event name must be a string.',
            'event_name.max' => 'The event name may not be greater than 255 characters.',
            'sector.string' => 'The sector must be a string.',
            'sector.max' => 'The sector may not be greater than 100 characters.',
            'guest_count.integer' => 'The guest count must be an integer.',
            'guest_count.min' => 'The guest count must be at least 1.',
            'tickets_generated.integer' => 'The tickets generated must be an integer.',
            'tickets_generated.min' => 'The tickets generated must be at least 1.',
            'redeemed_at.date' => 'The redeemed at must be a valid date.',
            'event_date.date' => 'The event date must be a valid date.',
            'per_page.integer' => 'The per page must be an integer.',
            'per_page.min' => 'The per page must be at least 1.',
            'per_page.max' => 'The per page may not be greater than 100.',
            'page.integer' => 'The page must be an integer.',
            'page.min' => 'The page must be at least 1.',
            'page.max' => 'The page may not be greater than 100.',
        ];
    }
}
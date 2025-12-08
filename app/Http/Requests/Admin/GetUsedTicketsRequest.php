<?php

namespace App\Http\Requests\Admin;
use Illuminate\Foundation\Http\FormRequest;

class GetUsedTicketsRequest extends FormRequest
{
    public function authorize(): bool
    {
        // El middleware 'admin:admin' ya validó el rol
        return true;
    }

    public function rules(): array
    {
        return [
            // Filtros de negocio
            'event_name' => 'nullable|string|max:255',
            'sector' => 'sometimes|string|max:50',
            'event_date' => 'nullable|date',
            'from_date' => 'sometimes|date|before_or_equal:today',
            'to_date' => 'sometimes|date|after_or_equal:from_date',
            'per_page' => 'sometimes|integer|min:5|max:100',
            
            // Parámetros de DataTables Server-Side Processing
            'draw' => 'sometimes|integer',
            'start' => 'sometimes|integer|min:0',
            'length' => 'sometimes|integer|min:1|max:100',
            'search' => 'sometimes|array',
            'search.value' => 'nullable|string',
            'search.regex' => 'nullable|boolean',
            'order' => 'sometimes|array',
            'order.*.column' => 'sometimes|integer',
            'order.*.dir' => 'sometimes|in:asc,desc',
            'columns' => 'sometimes|array',
            'columns.*.data' => 'nullable|string',
            'columns.*.name' => 'nullable|string',
            'columns.*.searchable' => 'nullable|boolean',
            'columns.*.orderable' => 'nullable|boolean',
            'columns.*.search' => 'sometimes|array',
            'columns.*.search.value' => 'nullable|string',
            'columns.*.search.regex' => 'nullable|boolean',
            '_' => 'sometimes|integer' // Timestamp cache buster
        ];
    }
    
    public function messages()
    {
        return [
            'sector.string' => 'The sector must be a string.',
            'sector.max' => 'The sector may not be greater than 50 characters.',
            'from_date.before_or_equal' => 'The from date must be a date before or equal to today.',
            'from_date.date' => 'The from date must be a valid date.',
            'to_date.date' => 'The to date must be a valid date.',
            'to_date.after_or_equal' => 'The to date must be a date after or equal to from date.',
            'per_page.integer' => 'The per page must be an integer.',
            'per_page.min' => 'The per page must be at least 5.',
            'per_page.max' => 'The per page may not be greater than 100.',
        ];
    }
}
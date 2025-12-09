<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class GetUsedTicketsDataTableRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // El middleware 'admin:admin' ya validó el rol
    }

    public function rules(): array
    {
        return [
            // Filtros de negocio personalizados
            'event_name' => 'nullable|string|max:255',
            'sector' => 'nullable|string|max:50',
            'event_date' => 'nullable|date',
            
            // Parámetros DataTables Server-Side Processing
            'draw' => 'required|integer',
            'start' => 'required|integer|min:0',
            'length' => 'required|integer|min:1|max:100',
            
            // Search global
            'search' => 'sometimes|array',
            'search.value' => 'nullable|string',
            'search.regex' => 'nullable|string|in:true,false', // ← String, no boolean
            
            // Order
            'order' => 'sometimes|array',
            'order.*.column' => 'sometimes|integer',
            'order.*.dir' => 'sometimes|string|in:asc,desc',
            
            // Columns (validación completa)
            'columns' => 'sometimes|array',
            'columns.*.data' => 'nullable|string',
            'columns.*.name' => 'nullable|string',
            'columns.*.searchable' => 'nullable|string|in:true,false', // ← String
            'columns.*.orderable' => 'nullable|string|in:true,false',  // ← String
            'columns.*.search' => 'sometimes|array',
            'columns.*.search.value' => 'nullable|string',
            'columns.*.search.regex' => 'nullable|string|in:true,false', // ← String
            
            // Cache buster
            '_' => 'sometimes|integer'
        ];
    }

    public function messages(): array
    {
        return [
            'event_name.string' => 'El nombre del evento debe ser texto.',
            'event_name.max' => 'El nombre del evento no puede exceder 255 caracteres.',
            'sector.string' => 'El sector debe ser texto.',
            'sector.max' => 'El sector no puede exceder 50 caracteres.',
            'event_date.date' => 'La fecha del evento debe ser válida.',
            'draw.required' => 'El parámetro draw es requerido.',
            'draw.integer' => 'El parámetro draw debe ser un entero.',
            'start.required' => 'El parámetro start es requerido.',
            'start.integer' => 'El parámetro start debe ser un entero.',
            'length.required' => 'El parámetro length es requerido.',
            'length.integer' => 'El parámetro length debe ser un entero.',
        ];
    }
}
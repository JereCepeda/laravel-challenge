<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Contracts\Validation\Validator;

class ValidateTicketRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'ticket_code' => [
                'required',
                'string',
                'regex:/^TCK-[A-Z0-9]{8}$/'
            ]
        ];
    }

    public function messages(): array
    {
        return [
            'ticket_code.required' => 'Ticket code is required.',
            'ticket_code.string' => 'Ticket code must be a string.',
            'ticket_code.regex' => 'Invalid ticket code format. Expected format: TCK-XXXXXXXX'
        ];
    }

    protected function failedValidation(Validator $validator)
    {
        throw new HttpResponseException(
            response()->json([
                'error' => 'Invalid ticket data',
                'details' => $validator->errors()
            ], 422)
        );
    }
}
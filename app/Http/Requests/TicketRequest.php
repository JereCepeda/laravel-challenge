<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Contracts\Validation\Validator;

class TicketRequest extends FormRequest
{
    private const VALID_HASHES = [
        'a8f22d', 'a8f22e', 'a8f22f', 'b9g33e', 'b9g33f', 'b9g33g', 
        'c0h44f', 'c0h44g', 'c0h44h', 'd1i55h', 'd1i55g', 'd1i55i', 
        'e2j66h', 'e2j66i', 'f3k77i', 'f3k77j', 'f3k77k', 'g4l88j', 
        'g4l88k', 'h5m99k', 'h5m99l', 'h5m99m', 'i6n00l', 'i6n00m', 
        'j7o11m', 'j7o11n', 'k8p22n', 'k8p22o', 'k8p22p', 'l9q33o', 'l9q33p'
    ];

    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $isValidationEndpoint = str_contains($this->path(), 'tickets/validate');

        if ($isValidationEndpoint) {
            return [
                'ticket_code' => ['required','string','regex:/^TCK-[A-Z0-9]{8}$/'
                ]
            ];
        }

        return [
            'hash' => ['required','string','size:6','regex:/^[a-z0-9]+$/',
                function ($attribute, $value, $fail) {
                    if (!in_array($value, self::VALID_HASHES)) {
                        $fail('The provided invitation hash is not valid.');
                    }
                }
            ]
        ];
    }

    public function messages(): array
    {
        return [
            'hash.required' => 'Invitation hash is required.',
            'hash.string' => 'Invitation hash must be a string.',
            'hash.size' => 'Invitation hash must be exactly 6 characters.',
            'hash.regex' => 'Invitation hash format is invalid.',
            
            'ticket_code.required' => 'Ticket code is required.',
            'ticket_code.string' => 'Ticket code must be a string.',
            'ticket_code.regex' => 'Invalid ticket code format. Expected format: TCK-XXXXXXXX'
        ];
    }

    protected function failedValidation(Validator $validator)
    {
        throw new HttpResponseException(
            response()->json([
                'error' => 'Invalid request data',
                'details' => $validator->errors()
            ], 422)
        );
    }

    protected function prepareForValidation(): void
    {
        if ($this->route('hash')) {
            $this->merge([
                'hash' => $this->route('hash')
            ]);
        }
    }
}
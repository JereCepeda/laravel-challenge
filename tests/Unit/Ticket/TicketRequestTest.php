<?php

namespace Tests\Unit\Ticket;
use Tests\TestCase;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

use Illuminate\Foundation\Testing\RefreshDatabase;

class TicketRequestTest extends TestCase
{
    use RefreshDatabase;

    public function test_validates_hash_for_redeem_invitation()
    {
        $request = new \App\Http\Requests\RedeemInvitationRequest();
        $data = [
            'hash' => 'c0h44h'
        ];
        $validator = Validator::make($data, $request->rules(), $request->messages());
        
        $this->assertTrue($validator->passes());
        $this->assertFalse($validator->fails());
    }
    public function test_fails_with_invalid_hash_for_redeem_invitation()
    {
        $request = new \App\Http\Requests\RedeemInvitationRequest();
        $data = [
            'hash' => 'invalid!'
        ];

        $validator = Validator::make($data, $request->rules(), $request->messages());

        $this->assertTrue($validator->fails());
        $this->assertTrue($validator->errors()->has('hash'));
    }
    public function test_validates_ticket_code_for_validate_endpoint()
    {
        $request = new \App\Http\Requests\ValidateTicketRequest();          
        $data = [
            'ticket_code' => 'TCK-ABCD1234'
        ];        
        $validator = Validator::make($data, $request->rules(), $request->messages());
        log::info('Validation Errors: ', $validator->errors()->toArray());
        $this->assertTrue($validator->passes());
        $this->assertFalse($validator->fails());
    }
    public function test_rejects_invalid_hash_format()
    {
        $request = new \App\Http\Requests\RedeemInvitationRequest();
        $data = [
            'hash' => '123' 
        ];
        $validator = Validator::make($data, $request->rules(), $request->messages());
        log::info('Validation Errors: ', $validator->errors()->toArray());

        $this->assertTrue($validator->fails());
        $this->assertTrue($validator->errors()->has('hash'));
    }
    public function test_rejects_invalid_ticket_code_format()
    {
        $request = new \App\Http\Requests\ValidateTicketRequest();
        $data = [
            'ticket_code' => 'INVALIDCODE' 
        ];
        $validator = Validator::make($data, $request->rules(), $request->messages());
        $this->assertTrue($validator->fails());
        $this->assertTrue($validator->errors()->has('ticket_code'));
    }
}
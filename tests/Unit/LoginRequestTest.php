<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Http\Requests\LoginRequest;
use App\Models\User;
use App\Models\Role;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Validator;

class LoginRequestTest extends TestCase
{
    use RefreshDatabase;

    protected $activeRole;
    protected $user;

    protected function setUp(): void
    {
        parent::setUp();
        
        $this->activeRole = Role::create([
            'name' => 'Admin',
            'slug' => 'admin',
            'description' => 'Admin role',
            'permissions' => ['manage_users'],
            'is_active' => true
        ]);

        $this->user = User::factory()->create([
            'email' => 'test@example.com',
            'role_id' => $this->activeRole->id
        ]);
    }

    public function test_login_request_validates_correct_data()
    {
        $request = new LoginRequest();
        $data = [
            'email' => 'test@example.com',
            'password' => 'password123'
        ];

        $validator = Validator::make($data, $request->rules(), $request->messages());

        $this->assertTrue($validator->passes());
    }

    public function test_login_request_fails_with_missing_email()
    {
        $request = new LoginRequest();
        $data = [
            'password' => 'password123'
        ];

        $validator = Validator::make($data, $request->rules(), $request->messages());

        $this->assertTrue($validator->fails());
        $this->assertTrue($validator->errors()->has('email'));
        $this->assertEquals('El campo de correo electrónico es obligatorio.', $validator->errors()->first('email'));
    }

    public function test_login_request_fails_with_invalid_email_format()
    {
        $request = new LoginRequest();
        $data = [
            'email' => 'invalid-email',
            'password' => 'password123'
        ];

        $validator = Validator::make($data, $request->rules(), $request->messages());

        $this->assertTrue($validator->fails());
        $this->assertTrue($validator->errors()->has('email'));
        $this->assertEquals('El correo electrónico debe ser una dirección válida.', $validator->errors()->first('email'));
    }

    public function test_login_request_fails_with_non_existent_email()
    {
        $request = new LoginRequest();
        $data = [
            'email' => 'nonexistent@example.com',
            'password' => 'password123'
        ];

        $validator = Validator::make($data, $request->rules(), $request->messages());

        $this->assertTrue($validator->fails());
        $this->assertTrue($validator->errors()->has('email'));
        $this->assertEquals('No existe una cuenta con este correo electrónico.', $validator->errors()->first('email'));
    }

    public function test_login_request_fails_with_missing_password()
    {
        $request = new LoginRequest();
        $data = [
            'email' => 'test@example.com'
        ];

        $validator = Validator::make($data, $request->rules(), $request->messages());

        $this->assertTrue($validator->fails());
        $this->assertTrue($validator->errors()->has('password'));
        $this->assertEquals('El campo de contraseña es obligatorio.', $validator->errors()->first('password'));
    }

    public function test_login_request_fails_with_short_password()
    {
        $request = new LoginRequest();
        $data = [
            'email' => 'test@example.com',
            'password' => '123'
        ];

        $validator = Validator::make($data, $request->rules(), $request->messages());

        $this->assertTrue($validator->fails());
        $this->assertTrue($validator->errors()->has('password'));
        $this->assertEquals('La contraseña debe tener al menos 6 caracteres.', $validator->errors()->first('password'));
    }

    public function test_login_request_fails_with_too_long_email()
    {
        $request = new LoginRequest();
        $longEmail = str_repeat('a', 250) . '@example.com'; // > 255 characters
        
        $data = [
            'email' => $longEmail,
            'password' => 'password123'
        ];

        $validator = Validator::make($data, $request->rules(), $request->messages());

        $this->assertTrue($validator->fails());
        $this->assertTrue($validator->errors()->has('email'));
    }

    public function test_login_request_authorize_method_returns_true()
    {
        $request = new LoginRequest();
        $this->assertTrue($request->authorize());
    }

    public function test_login_request_has_correct_custom_messages()
    {
        $request = new LoginRequest();
        $messages = $request->messages();

        $this->assertArrayHasKey('email.required', $messages);
        $this->assertArrayHasKey('password.required', $messages);
        $this->assertArrayHasKey('email.email', $messages);
        $this->assertArrayHasKey('password.min', $messages);
    }
}

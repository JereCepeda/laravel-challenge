<?php

namespace App\Services;

use App\Models\User;

class UserValidationService
{
    public function validateUserCredentials(string $email, string $password)
    {
        if(!$this->isValidEmail($email)) {
            return [
                'valid' => false,
                'error_type' => 'invalid_email',
                'message' => 'invalid email format',
                'user' => null,
            ];
        }
        $user = User::where('email', $email)->with('role')->first();
        if (!$user) {
            return [
                'valid' => false,
                'error_type' => 'invalid_credentials',
                'message' => 'invalid credentials',
                'user' => null,
            ];
        }
        if(!$this->verifyPassword($password, $user->password)) {
            return [
                'valid' => false,
                'error_type' => 'invalid_credentials',
                'message' => 'invalid credentials',
                'user' => null,
            ];
        }
        if(!$this->isUserActive($user)) {
            return [
                'valid' => false,
                'error_type' => 'inactive_user',
                'message' => 'user is inactive',
                'user' => null,
            ];
        }
        return [
            'valid' => true,
            'error_type' => null,
            'message' => 'valid credentials',
            'user' => $user,
        ];
    }

    public function isValidEmail(string $email): bool
    {
        return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
    }

    public function isUserActive(User $user): bool
    {
        return $user->role && $user->role->is_active;
    }

    public function verifyPassword(string $password, string $hashedPassword): bool
    {
        return password_verify($password, $hashedPassword);
    }
}
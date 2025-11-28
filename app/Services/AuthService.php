<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;

class AuthService
{
    public function authenticate(string $email, string $password)
    {
        Log::info('Attempting to authenticate user', ['email' => $email, 'password_provided' => !empty($password)]);
        $user = User::where('email', $email)->with('role')->first();

        if (!$user) {
            Log::warning('Authentication failed - User not found', ['email' => $email]);
            return null;
        }

        if (!$this->verifyPassword($password, $user->password)) {
            Log::warning('Authentication failed - Invalid password', [
                'email' => $email,
                'user_id' => $user->id
            ]);
            return null;
        }

        if (!$this->isUserActive($user)) {
            Log::warning('Authentication failed - Inactive user', [
                'email' => $email,
                'user_id' => $user->id,
                'role_active' => $user->role?->is_active ?? false
            ]);
            return null;
        }

        Log::info('Authentication successful', [
            'email' => $email,
            'user_id' => $user->id,
            'role' => $user->role?->slug ?? 'no_role'
        ]);

        return $user;
    }

    public function verifyPassword(string $plainPassword, string $hashedPassword): bool
    {
        return Hash::check($plainPassword, $hashedPassword);
    }

    public function isUserActive(User $user): bool
    {
        return $user->role && $user->role->is_active;
    }

    public function generateToken(User $user, string $tokenName = 'API Token'): string
    {
        return $user->createToken($tokenName)->accessToken;
    }

    public function revokeUserToken(User $user): bool
    {
        try {
            $user->tokens()->delete();
            return true;
        } catch (\Exception $e) {
            Log::error('Token revocation failed', [
                'user_id' => $user->id,
                'error' => $e->getMessage()
            ]);
            return false;
        }
    }

    public function formatUserResponse(User $user): array
    {
        return [
            'id' => $user->id,
            'name' => $user->name,
            'email' => $user->email,
            'role' => [
                'name' => $user->role->name,
                'slug' => $user->role->slug,
                'permissions' => $user->role->permissions
            ]
        ];
    }
}
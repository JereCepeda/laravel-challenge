<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use App\Services\AuthService;
use Illuminate\Http\Response;
use App\Http\Requests\LoginRequest;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Controller;
use App\Services\UserValidationService;
use Illuminate\Support\Facades\Auth;

class AuthController extends Controller
{
    public function __construct(private AuthService $authService, private UserValidationService $userValidationService)
    {}

    public function index()
    {
        Log::info('Accediendo a la vista de login API');
        return view('auth.login');
    }

    public function login(LoginRequest $request)
    {
        try{

            $credentials = $request->only('email', 'password');
            
            $validationResult = $this->userValidationService->validateUserCredentials($credentials['email'], $credentials['password']);
            if (!$validationResult['valid']) {
                Log::warning('Intento de inicio de sesión fallido', [
                    'email' => $credentials['email'],
                    'error_type' => $validationResult['error_type'],
                ]);
                
                return $this->handleAuthenticationError($validationResult,$request->ip());
            }
            Log::info('Usuario autenticado correctamente',['email' => $credentials['email'], 'user_id' => $validationResult['user']->id]);
            
            $user = $this->authService->authenticate($credentials['email'], $credentials['password']);
            
            $token = $this->authService->generateToken($user, 'API Token');
            
            // Autenticar usuario en la sesión web
            Auth::login($user);
            
            Log::info('Inicio de sesión exitoso', [
                'email' => $credentials['email'],
                'user_id' => $user->id,
                'role' => $user->role->slug
            ]);

            // Si es una petición AJAX/API, devolver JSON
            if ($request->expectsJson() || $request->is('api/*')) {
                return response()->json([
                    'message' => 'Login successful',
                    'user' => $user,
                    'token' => $token,
                    'redirect_url' => route('dashboard')
                ], Response::HTTP_OK);
            }

            // Si es una petición web, redirigir al dashboard
            return redirect()->route('dashboard')->with('success', 'Login successful');

        }
        catch (\Exception $e) {
            Log::error('Error interno en login', [
                'email' => $request->validated()['email'] ?? 'unknown',
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            if ($request->expectsJson() || $request->is('api/*')) {
                return response()->json([
                    'message' => 'Internal server error',
                    'errors' => ['server' => ['An unexpected error occurred.']]
                ], Response::HTTP_INTERNAL_SERVER_ERROR);
            }

            return back()->withErrors(['email' => 'An unexpected error occurred.']);
        }
    }
    private function handleAuthenticationError(array $validationResult, string $ipAddress)
    {
        switch ($validationResult['error_type']) {
            case 'invalid_email':
                $message = 'Invalid email format';
                $status = Response::HTTP_BAD_REQUEST;
                break;
            case 'invalid_credentials':
                $message = 'Invalid credentials';
                $status = Response::HTTP_UNAUTHORIZED;
                break;
            case 'inactive_account':
                $message = 'User is inactive';
                $status = Response::HTTP_FORBIDDEN;
                break;
        }
        Log::warning('Authentication error handled', [
            'ip_address' => $ipAddress,
            'error_type' => $validationResult['error_type'],
            'message' => $validationResult['message']
        ]);

        return response()->json([
            'message' => $message,
            'errors' => ['authentication' => [$validationResult['message']]]
        ], $status);
    } 

    public function me(Request $request)
    {
        try {
            $user = $request->user()->load('role');

            return response()->json([
                'user' => $this->authService->formatUserResponse($user)
            ], Response::HTTP_OK);

        } catch (\Exception $e) {
            Log::error('Error al obtener usuario autenticado', [
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'message' => 'User data unavailable'
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function logout(Request $request)
    {
        try {
            $success = $this->authService->revokeUserToken($request->user());
            
            if (!$success) {
                return response()->json([
                    'message' => 'Logout failed'
                ], Response::HTTP_INTERNAL_SERVER_ERROR);
            }

            Log::info('Logout exitoso', [
                'user_id' => $request->user()->id,
                'email' => $request->user()->email
            ]);

            return response()->json([
                'message' => 'Logout successful'
            ], Response::HTTP_OK);

        } catch (\Exception $e) {
            Log::error('Error en logout', [
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'message' => 'Logout failed'
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}

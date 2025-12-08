<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use App\Services\AuthService;
use Illuminate\Http\Response;
use App\Http\Requests\LoginRequest;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Controller;
use App\Services\UserValidationService;

class AuthController extends Controller
{
    public function __construct(
        private AuthService $authService, 
        private UserValidationService $userValidationService
    ) {}

    public function login(LoginRequest $request)
    {
        try {
            $credentials = $request->only('email', 'password');
            
            // Validar credenciales
            $validationResult = $this->userValidationService->validateUserCredentials(
                $credentials['email'], 
                $credentials['password']
            );
            
            if (!$validationResult['valid']) {
                Log::warning('Intento de inicio de sesión fallido', [
                    'email' => $credentials['email'],
                    'error_type' => $validationResult['error_type'],
                ]);
                
                return $this->handleAuthenticationError($validationResult, $request->ip());
            }
            
            Log::info('Usuario autenticado correctamente', [
                'email' => $credentials['email'], 
                'user_id' => $validationResult['user']->id
            ]);
            
            // Autenticar y generar token
            $user = $this->authService->authenticate($credentials['email'], $credentials['password']);
            $token = $this->authService->generateToken($user, 'API Token');
            
            Log::info('Inicio de sesión exitoso', [
                'email' => $credentials['email'],
                'user_id' => $user->id,
                'role' => $user->role->slug
            ]);

            return response()->json([
                'message' => 'Login successful',
                'user' => $user->load('role'),
                'token' => $token
            ], Response::HTTP_OK);

        } catch (\Exception $e) {
            Log::error('Error interno en login', [
                'email' => $request->validated()['email'] ?? 'unknown',
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'message' => 'Internal server error',
                'errors' => ['server' => ['An unexpected error occurred.']]
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    private function handleAuthenticationError(array $validationResult, string $ipAddress): \Illuminate\Http\JsonResponse
    {
        $errorMessages = [
            'invalid_email' => [
                'message' => 'Invalid email format',
                'status' => Response::HTTP_BAD_REQUEST
            ],
            'invalid_credentials' => [
                'message' => 'Invalid credentials',
                'status' => Response::HTTP_UNAUTHORIZED
            ],
            'inactive_account' => [
                'message' => 'User is inactive',
                'status' => Response::HTTP_FORBIDDEN
            ]
        ];

        $errorType = $validationResult['error_type'];
        $error = $errorMessages[$errorType] ?? [
            'message' => 'Authentication failed',
            'status' => Response::HTTP_UNAUTHORIZED
        ];
        
        Log::warning('Authentication error handled', [
            'ip_address' => $ipAddress,
            'error_type' => $errorType,
            'message' => $validationResult['message']
        ]);

        return response()->json([
            'message' => $error['message'],
            'errors' => ['authentication' => [$validationResult['message']]]
        ], $error['status']);
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
                'message' => 'User data unavailable',
                'errors' => ['server' => ['Could not retrieve user data']]
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function logout(Request $request)
    {
        try {
            $user = $request->user();
            
            if (!$user) {
                return response()->json([
                    'message' => 'User not authenticated'
                ], Response::HTTP_UNAUTHORIZED);
            }
            
            $success = $this->authService->revokeUserToken($user);
            
            if (!$success) {
                Log::error('Error al revocar token en logout', [
                    'user_id' => $user->id
                ]);
                
                return response()->json([
                    'message' => 'Logout failed',
                    'errors' => ['server' => ['Could not revoke token']]
                ], Response::HTTP_INTERNAL_SERVER_ERROR);
            }

            Log::info('Logout exitoso', [
                'user_id' => $user->id,
                'email' => $user->email
            ]);

            return response()->json([
                'message' => 'Logged out successfully'
            ], Response::HTTP_OK);

        } catch (\Exception $e) {
            Log::error('Error en logout', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'message' => 'Logout failed',
                'errors' => ['server' => ['An unexpected error occurred']]
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}

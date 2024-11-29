<?php

namespace App\Http\Controllers;

use App\Services\AuthService;
use Illuminate\Http\Request;

class AuthController extends Controller
{
    protected $authService;
    public function __construct(AuthService $authService)
    {
        $this->authService = $authService;
    }
    public function register(Request $request, AuthService $authService)
    {
        $request->validate([
            'name' => 'required|unique:users,name',
            'email' => 'required|unique:users,email,',
            'password' => 'required',
        ]);
        try {
            $token = $authService->register($request);

            return response()->json([
                'message' => 'user registered successfully',
                'token' => $token
            ], 200);
        } catch (\Throwable $e) {
            return response()->json(['error' => $e->getMessage()], 400);
        }
    }

    public function login(Request $request, AuthService $authService)
    {
        $request->validate([
            'email' => 'required|',
            'password' => 'required',
        ]);
        try {
            $token = $authService->login($request);
            return response()->json([
                'message' => 'user logged in successfully',
                'token' => $token,
                'user' => auth()->user()
            ], 200);
        } catch (\Throwable $e) {
            return response()->json(['error' => $e->getMessage()], 400);
        }
    }

    public function logout(AuthService $authService)
    {
        try {
            $authService->logout();
            return response()->json(['message' => 'Successfully logged out'], 200);
        } catch (\Throwable $e) {
            return response()->json(['error' => $e->getMessage()], 400);
        }
    }
}

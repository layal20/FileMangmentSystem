<?php
namespace App\Services;

use App\Repositories\AuthRepository;
use Exception;
use Illuminate\Support\Facades\Auth;

class AuthService{

    protected $authRepository;
    public function __construct(AuthRepository $authRepository) {
        $this->authRepository = $authRepository;
    }

    public function register($request)
    {
        return $this->authRepository->register($request);
    }

   
    public function login($request)
    {
        if (!Auth::attempt(['email' => $request->email, 'password' => $request->password])) {
            throw new Exception('UnAuthorized');
        }
        $user = $request->user();
        $token = $user->createToken('Access Token')->plainTextToken;
        return $token;
    }
    

    public function logout()
    {
        $user = Auth::user();
        if (!$user) {
            throw new Exception('Not authenticated user');
        }
        $tokens = $user->tokens;
        foreach ($tokens as $token) {
            $token->delete();
        }
    }


}
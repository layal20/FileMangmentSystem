<?php

namespace App\Http\Controllers;

use App\Services\UserService;

class UserController extends Controller
{
    protected $userService;
    public function __construct(UserService $userService)
    {
        $this->userService = $userService;
    }

    public function allUsers()
    {
        $users = $this->userService->allUsers();
        if ($users->isEmpty()) {
            return response()->json([
                'message' => 'there is not any users yet'
            ], 404);
        }
        return response()->json([
            'users' => $users
        ]);
    }

    public function getUsersInGroup($groupId)
    {
        $users = $this->userService->getUsersInGroup($groupId);
        if ($users->isEmpty()) {
            return response()->json([
                'message' => 'there is not any users in this group yet'
            ], 404);
        }
        return response()->json([
            'users' => $users
        ]);
    }
}

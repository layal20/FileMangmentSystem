<?php

namespace App\Services;

use App\Repositories\UserRepository;

class UserService
{

    protected $userRepository;
    public function __construct(UserRepository $userRepository)
    {
        $this->userRepository = $userRepository;
    }

    public function allUsers()
    {
        return $this->userRepository->allUsers();
    }

    public function getUsersInGroup($groupId)
    {
        return $this->userRepository->getUsersInGroup($groupId);
    }
}

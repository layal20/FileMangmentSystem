<?php

namespace App\Repositories;

use App\Models\Group;
use App\Models\User;
use Illuminate\Support\Facades\Auth;

class UserRepository
{
    public function allUsers()
    {
        return User::all();
    }

    public function getUsersInGroup($groupId)
    {
        $users = User::query()->whereHas('groups', function ($query) use ($groupId) {
            $query->where('group_id', $groupId);
        })->get();
        return $users;
    }

    public function isUserInGroup($groupId , $userId)
    {
        return User::query()->where('id' , $userId)->whereHas('groups' , function($query) use($groupId){
            $query->where('group_id' , $groupId);
        })->exists();
    }


}

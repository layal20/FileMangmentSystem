<?php

namespace App\Repositories;

use App\Models\Group;
use App\Models\User;
use Illuminate\Support\Facades\Auth;

class GroupRepository
{

    public function getGroupOwner($groupId)
    {
        return
        $this->getGroupById($groupId)->where('owner_id' , Auth::id())->first();
    }

    public function createGroup($data)
    {
        return
            Group::create($data);
    }
    public function getUserJoiningGroups($userId)
    {
        return
            Group::with('owner')->whereHas('users', function ($query) use ($userId) {
                $query->where('user_id', $userId);
            })->get();
    }
    public function getUserGroups($ownerId)
    {
        return
            Group::query()->where('owner_id' , $ownerId)->get();
    }

    public function getAllGroups()
    {
        return Group::with('users')->get();
    }
    public function getGroupById($id)
    {
        return
            Group::with(['owner' , 'users'])->find($id);
    }
    public function findUserInGroup($groupId, $userName)
    {
        return
            User::query()->whereHas('groups', function ($query) use ($groupId) {
                $query->where('groups.id', $groupId);
            })->where('name', 'LIKE', "%{$userName}%")->get();
   
    }

    

    
   
}

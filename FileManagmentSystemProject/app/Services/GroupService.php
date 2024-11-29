<?php

namespace App\Services;

use App\Models\User;
use App\Notifications\InvitationNotification;
use App\Repositories\GroupRepository;
use Exception;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Notification;

class GroupService
{
    protected $groupRepository;


    public function __construct(GroupRepository $groupRepository)
    {
        $this->groupRepository = $groupRepository;
    }

    public function createGroupAndInviteUsers($request)
    {
        $group = $this->groupRepository->createGroup($request);


        return
            $group;
    }


    public function getUserJoiningGroups($userId)
    {
        $groups =
            $this->groupRepository->getUserJoiningGroups($userId);

        if ($groups->isEmpty()) {
            return
                response()->json([
                    'message' => 'You Do not have any groups'
                ], 200);
        }

        return $groups;
    }

    public function getUserGroups($ownerId)
    {
        $groups = $this->groupRepository->getUserGroups($ownerId);
        if ($groups->isEmpty()) {
            return
                throw new Exception("You Do not have any groups");
        }
        return $groups;
    }
    public function getAllGroups()
    {
        $groups = $this->groupRepository->getAllGroups();
        if ($groups->isEmpty()) {
            throw new Exception("there is any groups yet");
        }
        return $groups;
    }
    public function getGroupById($id)
    {
        $group = $this->groupRepository->getGroupById($id);
        if (!$group) {
            throw new Exception("group not found");
        }
        return $group;
    }
    public function joinGroup($id)
    {
        $group = $this->getGroupById($id);
        if ($group->users()->where('user_id', Auth::id())->exists()) {
            throw new Exception('You are already in this group!');
        }
        $group->users()->attach(Auth::id());
    }

    // public function findUser($groupId, $userName, $ownerId)
    // {
    //     $group = $this->getGroupById($groupId);
    //     if (!$group) {
    //         throw new Exception('Group Not Found', 404);
    //     }
    //     $groupOwner = $this->groupRepository->getGroupOwner($groupId);
    //     if ($groupOwner->id !== $ownerId) {
    //         throw new Exception('Unauthorized');
    //     }

    //     $users =
    //         $this->groupRepository->findUser($groupId, $userName, $ownerId);
    //     if ($users->isEmpty()) {
    //         throw new Exception('User Not Found', 404);
    //     }
    //     return $users;
    // }

    public function findUserInGroup($groupId, $userName, $userId)
    {
        $group = $this->groupRepository->getGroupById($groupId);
        $group = $this->getGroupById($groupId);
        if (!$group) {
            throw new Exception('Group Not Found', 404);
        }
        if (!$group->users->contains($userId)) {
            throw new Exception('Unauthorized');
        }

        $users = $this->groupRepository->findUserInGroup($groupId, $userName);
        if ($users->isEmpty()) {
            throw new Exception('User Not Found', 404);
        }
        return $users;
    }
}

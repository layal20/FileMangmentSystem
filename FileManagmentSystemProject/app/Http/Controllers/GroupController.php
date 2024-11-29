<?php

namespace App\Http\Controllers;

use App\Services\GroupService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class GroupController extends Controller
{
    protected $groupService;
    public function __construct(GroupService $groupService)
    {
        $this->groupService = $groupService;
    }

    public function show($id)
    {
        $group = $this->groupService->getGroupById($id);
        return response()->json([
            'id' => $group->id,
            'name' => $group->name,
            'owner_id' => $group->owner_id,
            'owner_name' => optional($group->owner)->name,
            'users' => $group->users->pluck('id'),
            'created_at' => $group->created_at,
            'updated_at' => $group->updated_at,
        ]);
    }

    public function getUserJoiningGroups()
    {
        $groups = $this->groupService->getUserJoiningGroups(Auth::id());
        $formattedGroups = $groups->map(function ($group) {
            return [
                'id' => $group->id,
                'name' => $group->name,
                'owner_id' => $group->owner_id,
                'owner_name' => optional($group->owner)->name,
                'users' => $group->users->pluck('id'),
                'created_at' => $group->created_at,
                'updated_at' => $group->updated_at,
            ];
        });
        return response()->json([
            'groups' => $formattedGroups
        ], 200);
    }

    public function getUserGroups()
    {
        $groups = $this->groupService->getUserGroups(Auth::id());
        $formattedGroups = $groups->map(function ($group) {
            return [
                'id' => $group->id,
                'name' => $group->name,
                'owner_id' => $group->owner_id,
                'owner_name' => optional($group->owner)->name,
                'users' => $group->users->pluck('id'),
                'created_at' => $group->created_at,
                'updated_at' => $group->updated_at,
            ];
        });
        return response()->json([
            'groups' => $formattedGroups
        ], 200);
    }
    public function getAllGroups()
    {

        $groups = $this->groupService->getAllGroups();

        $formattedGroups = $groups->map(function ($group) {
            return [
                'id' => $group->id,
                'name' => $group->name,
                'owner_id' => $group->owner_id,
                'owner_name' => optional($group->owner)->name,
                'users' => $group->users->pluck('id'),
                'created_at' => $group->created_at,
                'updated_at' => $group->updated_at,
            ];
        });
        return response()->json([
            'groups' => $formattedGroups
        ], 200);
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|min:4|unique:groups,name',
            'users' => 'sometimes|exists:users,id|array'
        ]);
        try {
            $this->groupService->createGroupAndInviteUsers($request->all());
            return response()->json([
                'message' => 'Group Created successfully'
            ], 200);
        } catch (\Throwable $e) {
            return response()->json(['error' => $e->getMessage()], 400);
        }
    }
    public function joinGroup($id)
    {
        try {
            $this->groupService->joinGroup($id);
            return response()->json([
                'message' => 'You joined the group Successfully'
            ]);
        } catch (\Throwable $e) {
            return response()->json(['error' => $e->getMessage()], 400);
        }
    }

    public function findUserInGroup($groupId, $userName)
    {
        try {
            $users = $this->groupService->findUserInGroup($groupId, $userName, Auth::id());
            return   response()->json([
                'users' => $users
            ]);
        } catch (\Throwable $e) {
            return response()->json(['error' => $e->getMessage()], 400);
        }
    }
}

<?php

namespace App\Observers;

use App\Models\Group;
use App\Models\User;
use App\Notifications\InvitationNotification;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Notification;

class GroupObserver
{
    public function created(Group $group): void
    {
        $group->users()->attach(Auth::id());
        if (request()->has('users')) {
            $userIds = request()->input('users', []);
            foreach ($userIds as $userId) {
                $invitedUser = User::query()->find($userId);
                if ($invitedUser && $invitedUser->id != $group->owner_id) {
                    try {
                        Notification::route('mail', $invitedUser->email)->notify(new InvitationNotification($group));
                    } catch (\Exception $e) {
                        Log::error('Error sending test notification: ' . $e->getMessage());
                    }
                }
            }
        }
    }



    public function creating(Group $group): void
    { {
            $group->owner_id = Auth::id();
        }
    }
}

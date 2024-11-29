<?php

namespace App\Observers;

use App\Models\File;
use App\Models\Group;
use Illuminate\Support\Facades\Auth;

class FileObserver
{
    public function creating(File $file): void
    {
        $file->owner_id = Auth::id();
        $group = Group::find($file->group_id);
        if ($group->owner_id == Auth::id()) {
            $file->request_status = 'accepted';
        }
    }
}

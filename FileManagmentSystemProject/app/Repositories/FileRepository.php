<?php

namespace App\Repositories;

use App\Models\File;
use App\Models\Group;
use App\Notifications\FileApprovalNotification;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Str;

class FileRepository
{

    public function getLatestFiles()
    {
        // return File::query()
        //     ->whereIn('id', function ($query) {
        //         $query->select(DB::raw('MAX(id)'))
        //         ->from('files')
        //             ->groupBy('version_id');
        //     })
        //     ;
        $latestVersions = DB::table('files')
        ->select('version_id', DB::raw('MAX(id) as max_id'))
        ->groupBy('version_id');
        $latestFiles = File::query()
        ->joinSub($latestVersions, 'latest_files', function ($join) {
            $join->on('files.id', '=', 'latest_files.max_id');
        });
        return $latestFiles;
    }
    
    public function getFileByVersionId($versionId)
    {
        return File::query()->where('version_id', $versionId)->get();

    }
    
    public function getAcceptedFiles()
    {

        return
            $this->getLatestFiles()->where('request_status', 'accepted');
    }


    public function getGroupsFiles($groupIds)
    {

        return
            $this->getAcceptedFiles()->whereIn('group_id', $groupIds)->get();
    }

    public function getGroupFiles($groupId)
    {

        return
            $this->getAcceptedFiles()->where('group_id', $groupId)->get();
    }

    public function getOwnerFiles($ownerId)
    {
        return
            File::query()->where('owner_id', $ownerId)->get();
    }
    public function findFileById($id)
    {
        return File::query()->find($id);
    }


    public function findAcceptedFile($id)
    {
        return
            File::query()->where('id', $id)->where('request_status', 'accepted')->first();
    }


    public function createFile($data, $path)
    {
        $file = File::create($data);
        $file->version_id = (string) Str::uuid();
        $file->version_number = 1;
        $file->path = $path;
        $file->save();
        $group = Group::find($file->group_id);
        if ($group && $group->owner_id !== Auth::id()) {
            Notification::route('mail', $group->owner->email)->notify(new FileApprovalNotification($file->name, $file->path, $file->group_id));
            throw new \Exception("Approval required from the group owner.");
        }
    }




    public function updateFileStatus($file, $status)
    {
        $file->status = $status;
        return $file->save();
    }

    public function updateFileRequestStatus($file, $status)
    {
        $file->request_status = $status;
        $file->save();
    }

    public function updateFileBookedBy($file, $userId = null)
    {
        $file->booked_by = $userId;
        return $file->save();
    }

    public function updateFile($fileId, $data)
    {
        $file = $this->findAcceptedFile($fileId);
        return
            $file->update($data);
    }

   
    public function deleteFile($fileId)
    {

        $file = $this->findAcceptedFile($fileId);
        return $file->delete();
        // return $file ? $file->delete() : null;
    }

    public function approveFile($file)
    {
        return $this->updateFileRequestStatus($file, 'accepted');
    }

    public function selectAcceptedFiles($fileIds)
    {
        if (is_array($fileIds) && count($fileIds) > 0) {
            return $this->getAcceptedFiles()->whereIn('id', $fileIds);
        } else {
            return File::query()->whereRaw('0 = 1');
        }
    }






    public function checkOut($file, $newFile, $originalExtension)
    {
        if ($newFile === null) {
            throw new \Exception('The new file is missing or not properly uploaded.');
        }

        $path = $newFile->storeAs('files', uniqid() . '.' . $originalExtension, 'uploads');
        return File::create([
            'name' => $file->name,
            'path' => $path,
            'status' => 'free',
            'version_id' => $file->version_id,
            'version_number' => $file->version_number + 1,
            'status' => 'accepted',
            'owner_id' => $file->owner_id,
            'group_id' => $file->group_id
        ]);
    }
}

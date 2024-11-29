<?php

namespace App\Services;

use App\Repositories\FileRepository;
use App\Repositories\GroupRepository;
use App\Repositories\UserRepository;
use Exception;
use GuzzleHttp\Psr7\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use ZipArchive;

use function PHPUnit\Framework\throwException;

class FileService
{
    protected $fileRepository, $groupRepository, $userRepository;

    public function __construct(FileRepository $fileRepository, GroupRepository $groupRepository, UserRepository $userRepository)
    {
        $this->fileRepository = $fileRepository;
        $this->groupRepository = $groupRepository;
        $this->userRepository = $userRepository;
    }



    // public function getAllFiles()
    // {
    //     return $this->fileRepository->getLatestFiles();
    // }

    public function fileVersions($fileId)
    {
        //dd('lll');
        $file = $this->findAcceptedFile($fileId);
        if (!$file) {
            throw new \Exception('File Not Found!.');
        }
        $files = $this->fileRepository->getFileByVersionId($file->version_id);
        if ($files->isEmpty()) {
            throw new \Exception('No files found for this version.');
        }
        return $files;
    }
    public function getGroupsFiles($groupIds)
    {
        return $this->fileRepository->getGroupsFiles($groupIds);
    }

    public function getUserFiles($userId)
    {
        $groupIds = $this->groupRepository->getUserJoiningGroups($userId)->pluck('id');
        if ($groupIds->isEmpty()) {
            throw new \Exception('User is not part of any groups.');
        }

        $files = $this->fileRepository->getGroupsFiles($groupIds);
        if ($files->isEmpty()) {
            throw new \Exception('No files available for these groups.');
        }

        return $files;
    }




    public function getOwnerFiles($ownerId)
    {
        $files = $this->fileRepository->getOwnerFiles($ownerId);
        if ($files->isEmpty()) {
            throw new \Exception('there is not any files yet.');
        }
        return $files;
    }
    public function getFilesInGroup($groupId)
    {
        $group = $this->groupRepository->getGroupById($groupId);
        if (!$group) {
            throw new Exception("Group Not found!");
        }
        $isUserInGroup = $this->userRepository->isUserInGroup($groupId, Auth::id());
        if (!$isUserInGroup) {
            throw new Exception("You are not in this group");
        }
        $files = $this->fileRepository->getGroupFiles($groupId);
        if ($files->isEmpty()) {
            throw new \Exception('there is not any files yet.');
        }
        return $files;
    }

    public function findAcceptedFile($id)
    {
        return
            $this->fileRepository->findAcceptedFile($id);
    }
    public function createFile($request, $path)
    {
        return
            $this->fileRepository->createFile($request, $path);
    }
    public function uploadFile(array $data, $userId)
    {
        $groupIds = $this->groupRepository->getUserJoiningGroups($userId)->pluck('id')->toArray();

        if (!in_array($data['group_id'], $groupIds)) {
            throw new Exception("You cannot add files in this group");
        }

        $file = $data['path'];
        $filename = $file->getClientOriginalName();
        $path = $file->storeAs('files', $filename, 'uploads');
        $this->createFile($data, $path);
    }

    public function updateFileStatus($file, $status, $userId = null)
    {
        return $this->fileRepository->updateFileStatus($file, $status, $userId = null);
    }

    public function updateFile($groupId, $fileId, $data)
    {
        $groupOwner = $this->groupRepository->getGroupOwner($groupId);
        if (!$groupOwner) {
            throw new Exception("Only the group owner can update files.");
        }
        return
            $this->fileRepository->updateFile($fileId, $data);
    }
    public function deleteFile($groupId, $fileId)
    {
        $groupOwner = $this->groupRepository->getGroupOwner($groupId);
        if (!$groupOwner) {
            throw new Exception("Only the group owner can delete files.");
        }
        return $this->fileRepository->deleteFile($fileId);
    }

    public function approveFile($groupId, $fileId)
    {
        $group = $this->groupRepository->getGroupById($groupId);
        if (!$group) {
            throw new Exception("this group does not exist.");
        }
        $groupOwner = $this->groupRepository->getGroupOwner($groupId);
        if (!$groupOwner) {
            throw new Exception("Only the group owner can approve files.");
        }
        $file = $this->fileRepository->findFileById($fileId);
        if (!$file) {
            throw new Exception("this file does not exist.");
        }
        if ($file->request_status !== 'pending') {
            throw new Exception("this file is already accepted.");
        }
        return
            $this->fileRepository->approveFile($file);
    }


    public function checkIn($fileIds, $status, $userId)
    {
        $files = $this->fileRepository->selectAcceptedFiles($fileIds)->lockForUpdate()->get();

        if ($files->count() !== count($fileIds)) {
            throw new Exception('File count mismatch.');
        }

        foreach ($files as $file) {
            if (!Auth::user()->groups->contains($file->group_id)) {
                throw new Exception('You do not have permission to check in this file', 403);
            }

            if ($file->status !== 'free') {
                throw new Exception('One or more files are not free.');
            }

            $this->fileRepository->updateFileStatus($file, $status);
            $this->fileRepository->updateFileBookedBy($file, $userId);
        }

        return $files;
    }




    public function processCheckInAndCreateZip($fileIds, $status, $userId)
    {
        DB::beginTransaction();
        try {
            $files = $this->checkIn($fileIds, $status, $userId);

            DB::commit();

            $filename = storage_path('downloaded_files_' . time() . '.zip');
            $zip = new ZipArchive;
            if ($zip->open($filename, ZipArchive::CREATE) !== TRUE) {
                throw new \Exception("Could not create ZIP file at " . $filename . " with error code " . $zip->status);
            }

            foreach ($files as $file) {
                $filePath = public_path('uploads/' . $file->path);
                if (!file_exists($filePath)) {
                    throw new \Exception("File does not exist at: " . $filePath);
                }
                $zip->addFile($filePath, basename($filePath));
            }

            $zip->close();

            if (!file_exists($filename)) {
                throw new \Exception("Failed to create ZIP file at: " . $filename);
            }

            return $filename;
        } catch (\Throwable $e) {
            DB::rollBack();
            throw $e;
        }
    }


    public function checkFilePermission($file)
    {
        if ($file->status !== 'booked' || $file->booked_by !== Auth::id() || !Auth::user()->groups->contains($file->group_id)) {
            throw new Exception('You do not have permission to check out this file', 403);
        }
    }
    public function checkFileExtension($request, $file)
    {
        $newFile = $request->file('path');
        $originalExtension = pathinfo($file->path, PATHINFO_EXTENSION);
        $newFileExtension = $newFile->getClientOriginalExtension();

        if ($newFileExtension !== $originalExtension) {
            throw new Exception('Uploaded file must have the same extension as the original file', 400);
        }

        return [
            $originalExtension,
            $newFile
        ];
    }

    public function checkOut($request, $status, $fileId)
    {
        try {
            $file = $this->fileRepository->findAcceptedFile($fileId);
            if (!$file) {
                throw new Exception('File not found!', 404);
            }
            $this->checkFilePermission($file);
            [$originalExtension, $newFile] = $this->checkFileExtension($request, $file);
            $file1Content = file_get_contents(public_path('uploads/' . $file->path));
            $file2Content = file_get_contents($newFile->getRealPath());
            if ($file1Content !== $file2Content) {
                $this->fileRepository->checkOut($file, $newFile, $originalExtension);
            }

            if (!$this->updateFileStatus($file, $status)) {
                throw new Exception('Failed to update file status', 500);
            }
            if (!$this->fileRepository->updateFileBookedBy($file, null)) {
                throw new Exception('Failed to update file booked by', 500);
            }
            return response()->json(['message' => 'File checked out successfully'], 200);
        } catch (Exception $e) {
            return response()->json(['error' => $e->getMessage()], $e->getCode() ?: 500);
        }
    }
}

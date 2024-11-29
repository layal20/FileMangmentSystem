<?php

namespace App\Http\Controllers;

use App\Services\FileService;
use App\Services\GroupService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class FileController extends Controller
{
    protected $fileService, $groupService;

    public function __construct(FileService $fileService, GroupService $groupService)
    {
        $this->fileService = $fileService;
        $this->groupService = $groupService;
    }


    public function index()
    {
        try {
            $files = $this->fileService->getUserFiles(Auth::id());
            return response()->json([
                'files' => $files
            ], 200);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 400);
        }
    }

    // public function getAllFiles()
    // {
    //     try {
    //         return
    //             $this->fileService->getAllFiles();
    //     } catch (\Exception $e) {
    //         return response()->json(['error' => $e->getMessage()], 400);
    //     }
    // }

    public function fileVersions(Request $request, $fileId)
    {
        try {
            $files = $this->fileService->fileVersions($fileId);
            return response()->json(['files' => $files], 200);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 404);
        }
    }

    public function findAcceptedFile($id)
    {
        try {
            return
                $this->fileService->findAcceptedFile($id);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 400);
        }
    }

    public function getOwnerFiles()
    {
        try {
            $files = $this->fileService->getOwnerFiles(Auth::id());

            return response()->json([
                'files' => $files
            ], 200);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 400);
        }
    }

    public function getFilesInGroup($groupId)
    {
        try {
            $files = $this->fileService->getFilesInGroup($groupId);
            return response()->json([
                'files' => $files
            ], 200);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 400);
        }
    }
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|min:5',
            'path' => 'required',
            'group_id' => 'required|exists:groups,id'
        ]);

        try {

            $this->fileService->uploadFile($request->all(), Auth::id());


            return response()->json(['message' => 'File uploaded successfully'], 200);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 400);
        }
    }

    public function approveFile($groupId, $fileId)
    {
        try {
            $this->fileService->approveFile($groupId, $fileId);
            return response()->json(['message' => 'File approved successfully.'], 200);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 400);
        }
    }



    // public function checkIn(Request $request)
    // {
    //     $request->validate([
    //         'fileIds' => 'required|array',
    //         'fileIds.*' => 'exists:files,id'
    //     ]);

    //     $fileIds = $request->input('fileIds');

    //     DB::beginTransaction();
    //     try {
    //         $files = $this->fileService->checkIn($fileIds, 'booked', Auth::id());

    //         $downloadLinks = [];
    //         foreach ($files as $file) {
    //             $filePath = public_path('uploads/' . $file->path);

    //             if (file_exists($filePath)) {
    //                 $downloadLinks[] = url('uploads/' . $file->path);
    //             } else {
    //                 throw new Exception("File {$file->name} not found.");
    //             }
    //         }

    //         DB::commit();

    //         return response()->json([
    //             'message' => 'Files checked in successfully',
    //             'download_links' => $downloadLinks
    //         ], 200);
    //     } catch (\Exception $e) {
    //         DB::rollBack();
    //         return response()->json(['error' => $e->getMessage()], 400);
    //     }
    // }




    public function checkIn(Request $request)
    {
        $request->validate([
            'fileIds' => 'required|array',
            'fileIds.*' => 'exists:files,id'
        ]);

        $fileIds = $request->input('fileIds');

        try {
            $zipFilePath = $this->fileService->processCheckInAndCreateZip($fileIds, 'booked', Auth::id());

            return response()->json([
                'message' => 'checked in successfully'
            ]);
            //return response()->download($zipFilePath)->deleteFileAfterSend(true);
        } catch (\Throwable $e) {
            return response()->json([
                'error' => $e->getMessage(),
            ], 400);
        }
    }



    public function checkOut(Request $request, $fileId)
    {
        $request->validate([
            'path' => 'required|file',
        ]);
        //DB::beginTransaction();
        try {
            return $this->fileService->checkOut($request, 'free', $fileId);
            //DB::commit();
            return response()->json(['message' => 'File checked out successfully'], 200);
        } catch (\Exception $e) {
            //DB::rollBack();
            return response()->json(['error' => $e->getMessage()], 400);
        }
    }


    public function update(Request $request, $groupId, $fileId)
    {
        $data = $request->validate([
            'name' => 'sometimes|string|min:5',
            'path' => 'sometimes',
            'status' => 'sometimes|in:free,booked'
        ]);
        $this->fileService->updateFile($groupId, $fileId, $data);
    }

    public function delete($groupId, $fileId)
    {
        try {
            $this->fileService->deleteFile($groupId, $fileId);
            return response()->json(['message' => 'File deleted successfully'], 200);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 400);
        }
    }
}

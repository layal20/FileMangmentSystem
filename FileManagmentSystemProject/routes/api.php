<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\FileController;
use App\Http\Controllers\GroupController;
use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/
Route::middleware('cros')->group(function () {
    Route::get('allUsers', [UserController::class, 'allUsers']);
    Route::post('register', [AuthController::class, 'register']);
    Route::post('login', [AuthController::class, 'login']);
});

Route::middleware(['auth:sanctum', 'cros'])->group(function () {

    Route::post('logout', [AuthController::class, 'logout']);
    Route::get('groups', [GroupController::class, 'getUserJoiningGroups']);
    Route::get('userGroups', [GroupController::class, 'getUserGroups']);
    Route::get('allGroups', [GroupController::class, 'getallGroups']);
    Route::post('group/join/{id}', [GroupController::class, 'joinGroup']);
    Route::get('group/{id}', [GroupController::class, 'show']);
    Route::post('group/store', [GroupController::class, 'store']);

    
    Route::get('findUserInGroup/{groupId}/{userName}', [GroupController::class, 'findUserInGroup']);


    Route::post('groups/{groupId}/approve-file', [FileController::class, 'approveFile']);
    Route::get('groupFiles/{groupId}', [FileController::class, 'getGroupFiles']);
    Route::get('fileVersions/{fileId}', [FileController::class, 'fileVersions']);
    Route::get('ownerFiles', [FileController::class, 'getOwnerFiles']);
    Route::get('acceptedFiles', [FileController::class, 'index']);
    Route::get('getFilesInGroup/{groupId}', [FileController::class, 'getFilesInGroup']);
    Route::get('acceptedFile/show/{id}', [FileController::class, 'findAcceptedFile']);
    Route::post('file/store', [FileController::class, 'store']);
    Route::post('approveFile/{groupId}/{fileId}', [FileController::class, 'approveFile']);
    Route::post('checkIn', [FileController::class, 'checkIn']);
    Route::post('checkOut/{id}', [FileController::class, 'checkOut']);



    Route::get('usersInGroup/{groupId}', [UserController::class, 'getUsersInGroup']);
});

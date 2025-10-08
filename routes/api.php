<?php

use App\Http\Controllers\PostAccessController;
use App\Http\Controllers\CommentController;
use App\Http\Controllers\FollowController;
use App\Http\Controllers\PostController;
use App\Models\Post;
use Illuminate\Http\Request;
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

Route::group(['middleware' => 'auth:sanctum'], function () {
    Route::get('/user', function (Request $request) {
        return $request->user();
    });
    Route::post('/posts', [PostController::class, 'store']);
    Route::put('/posts/{post}', [PostController::class, 'update']);
    Route::post('/posts/{post}/comments', [CommentController::class, 'store']);
    Route::post('/users/{user}/follow', [FollowController::class, 'follow']);
    Route::post('/users/{user}/unfollow', [FollowController::class, 'unfollow']);

    Route::post('/posts/{post}/request-access', [PostAccessController::class, 'requestAccess']);
    Route::post('/post-access-requests/{accessRequest}/approve', [PostAccessController::class, 'approveAccess']);
    Route::post('/post-access-requests/{accessRequest}/deny', [PostAccessController::class, 'denyAccess']);
    Route::get('/posts/{post}/access-requests', [PostAccessController::class, 'listRequests']);
});

Route::group(['middleware' => 'web'], function () {
    Route::get('/posts', [PostController::class, 'index']);
    Route::get('/posts/{post:slug}', [PostController::class, 'show']);
    Route::get('/posts/{post}/comments', [CommentController::class, 'postComments']);
});



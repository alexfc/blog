<?php

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

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

Route::middleware('auth:sanctum')->post('/posts', [PostController::class, 'store']);
Route::middleware('auth:sanctum')->put('/posts/{post}', [PostController::class, 'update']);
Route::middleware('auth:sanctum')->post('/posts/{post}/comments', [CommentController::class, 'store']);

Route::middleware('auth:sanctum')->post('/users/{user}/follow', [FollowController::class, 'follow']);
Route::middleware('auth:sanctum')->post('/users/{user}/unfollow', [FollowController::class, 'unfollow']);

Route::group(['middleware' => 'web'], function () {
    Route::get('/posts', [PostController::class, 'index']);
    Route::get('/posts/{post:slug}', [PostController::class, 'show']);
    Route::get('/posts/{post}/comments', function (Post $post) {
        return response()->json($post->comments()->with('user')->latest()->get());
    });
});



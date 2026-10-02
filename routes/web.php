<?php

use App\Http\Controllers\AdminPostController;
use App\Http\Controllers\CommentController;
use App\Http\Controllers\NewsletterController;
use App\Http\Controllers\PostController;
use App\Http\Controllers\SessionController;
use Illuminate\Support\Facades\Route;

Route::get('/', [PostController::class, 'index']);
Route::get('posts/{post:slug}', [PostController::class, 'show']);

// max 5 reacties per minuut tegen spam
Route::post('posts/{post:slug}/comments', [CommentController::class, 'store'])->middleware('throttle:5,1');

// nieuwsbrief inschrijving (formulier in de footer)
Route::post('newsletter', [NewsletterController::class, 'store'])->middleware('throttle:5,1');

// inloggen / uitloggen
Route::get('login', [SessionController::class, 'create'])->middleware('guest')->name('login');
Route::post('login', [SessionController::class, 'store'])->middleware(['guest', 'throttle:5,1']);
Route::post('logout', [SessionController::class, 'destroy'])->middleware('auth');

// cms (alleen als je ingelogd bent)
Route::middleware('auth')->group(function () {
    Route::get('admin/posts', [AdminPostController::class, 'index']);
    Route::get('admin/posts/create', [AdminPostController::class, 'create']);
    Route::post('admin/posts', [AdminPostController::class, 'store']);
    Route::get('admin/posts/{post}/edit', [AdminPostController::class, 'edit']);
    Route::patch('admin/posts/{post}', [AdminPostController::class, 'update']);
    Route::delete('admin/posts/{post}', [AdminPostController::class, 'destroy']);
});

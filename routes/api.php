<?php

use App\Http\Controllers\Api\ArticleController;
use App\Http\Controllers\Api\ProjectController;
use App\Http\Controllers\Api\TagController;
use Illuminate\Support\Facades\Route;

Route::prefix('v1')->middleware('throttle:api')->group(function (): void {
    Route::get('/articles', [ArticleController::class, 'index'])->name('api.articles.index');
    Route::get('/articles/{identifier}', [ArticleController::class, 'show'])->name('api.articles.show');
    Route::get('/tags', [TagController::class, 'index'])->name('api.tags.index');
    Route::get('/tags/{slug}/articles', [TagController::class, 'show'])->name('api.tags.show');
    Route::get('/projects', [ProjectController::class, 'index'])->name('api.projects.index');
    Route::get('/projects/{slug}', [ProjectController::class, 'show'])->name('api.projects.show');
});

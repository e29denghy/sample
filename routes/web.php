<?php

use App\Http\Controllers\Admin\ArticleController as AdminArticleController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\MediaAssetController as AdminMediaAssetController;
use App\Http\Controllers\Admin\ProjectController as AdminProjectController;
use App\Http\Controllers\ArticleController;
use App\Http\Controllers\Auth\ForgotPasswordController;
use App\Http\Controllers\Auth\ResetPasswordController;
use App\Http\Controllers\FeedController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\ProjectController;
use App\Http\Controllers\SessionsController;
use App\Http\Controllers\SitemapController;
use App\Http\Controllers\SitePagesController;
use App\Http\Controllers\TagController;
use App\Http\Controllers\WeChatController;
use Illuminate\Support\Facades\Route;

Route::get('/', HomeController::class)->name('home');
Route::get('/articles', [ArticleController::class, 'index'])->name('articles.index');
Route::get('/articles/{slug}', [ArticleController::class, 'show'])->name('articles.show');
Route::get('/tags/{slug}', [TagController::class, 'show'])->name('tags.show');
Route::get('/projects', [ProjectController::class, 'index'])->name('projects.index');
Route::get('/projects/{slug}', [ProjectController::class, 'show'])->name('projects.show');
Route::get('/now', [SitePagesController::class, 'now'])->name('now');
Route::get('/about', [SitePagesController::class, 'about'])->name('about');
Route::get('/help', [SitePagesController::class, 'about'])->name('help');

Route::get('/rss.xml', [FeedController::class, 'rss'])->name('feeds.rss');
Route::get('/atom.xml', [FeedController::class, 'atom'])->name('feeds.atom');
Route::get('/sitemap.xml', SitemapController::class)->name('sitemap');

Route::get('/login', [SessionsController::class, 'create'])->name('login');
Route::post('/login', [SessionsController::class, 'store'])->name('login.store')->middleware('throttle:login');
Route::delete('/logout', [SessionsController::class, 'destroy'])->name('logout')->middleware('auth');

Route::get('/password/reset', [ForgotPasswordController::class, 'showLinkRequestForm'])->name('password.request');
Route::post('/password/email', [ForgotPasswordController::class, 'sendResetLinkEmail'])->name('password.email')->middleware('throttle:password');
Route::get('/password/reset/{token}', [ResetPasswordController::class, 'showResetForm'])->name('password.reset');
Route::post('/password/reset', [ResetPasswordController::class, 'reset'])->name('password.update');

Route::middleware(['auth', 'can:admin'])->prefix('admin')->name('admin.')->group(function (): void {
    Route::get('/', DashboardController::class)->name('dashboard');
    Route::get('/articles', [AdminArticleController::class, 'index'])->name('articles.index');
    Route::get('/articles/create', [AdminArticleController::class, 'create'])->name('articles.create');
    Route::post('/articles', [AdminArticleController::class, 'store'])->name('articles.store');
    Route::get('/articles/{article}/edit', [AdminArticleController::class, 'edit'])->name('articles.edit');
    Route::patch('/articles/{article}', [AdminArticleController::class, 'update'])->name('articles.update');
    Route::post('/articles/preview', [AdminArticleController::class, 'preview'])->name('articles.preview');
    Route::post('/articles/{article}/publish', [AdminArticleController::class, 'publish'])->name('articles.publish');
    Route::post('/articles/{article}/archive', [AdminArticleController::class, 'archive'])->name('articles.archive');
    Route::get('/projects', [AdminProjectController::class, 'index'])->name('projects.index');
    Route::get('/projects/create', [AdminProjectController::class, 'create'])->name('projects.create');
    Route::post('/projects', [AdminProjectController::class, 'store'])->name('projects.store');
    Route::get('/projects/{project}/edit', [AdminProjectController::class, 'edit'])->name('projects.edit');
    Route::patch('/projects/{project}', [AdminProjectController::class, 'update'])->name('projects.update');
    Route::get('/media', [AdminMediaAssetController::class, 'index'])->name('media.index');
    Route::post('/media', [AdminMediaAssetController::class, 'store'])->middleware('throttle:media-uploads')->name('media.store');
});

Route::any('/wechat', [WeChatController::class, 'serve']);

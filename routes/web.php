<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\ApplicationController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\NiaController;
use App\Http\Controllers\NiaMockController;
use App\Models\Application;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\AdminController;

Route::get('/link-storage', function () {
    try {
        Illuminate\Support\Facades\Artisan::call('storage:link');
        return 'Storage link has been successfully created.';
    } catch (Exception $e) {
        return 'Error: ' . $e->getMessage();
    }
});

Route::get('/', function () {
    return view('home');
})->name('home');

Route::get('/programs', [ApplicationController::class, 'programsIndex'])->name('programs.index');

Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/auth/email', [AuthController::class, 'handleEmail'])->name('auth.email');
    Route::post('/auth/password', [AuthController::class, 'loginWithPassword'])->name('auth.password');
    Route::post('/auth/send-link', [AuthController::class, 'sendLink'])->name('auth.send-link');
    Route::get('/auth/verify', [AuthController::class, 'verifyTicket'])->name('auth.verify');
});

Route::get('/auth/nia', [NiaController::class, 'metadata'])->name('nia.metadata');
Route::post('/auth/nia/callback', [NiaController::class, 'acs'])->name('nia.acs');
Route::get('/auth/nia/logout', fn() => redirect('/'))->name('nia.logout');

Route::middleware('auth')->group(function () {

    Route::get('/dashboard', function () {
        $applications = Application::where('user_id', Auth::id())
            ->orderBy('created_at', 'desc')
            ->get();
        return view('dashboard', compact('applications'));
    })->name('dashboard');

    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

    Route::get('/auth/nia/login/{applicationId}', [NiaController::class, 'login'])->name('nia.real.login');
    Route::get('/nia/mock-login/{applicationId}', [NiaMockController::class, 'login'])->name('nia.mock.login');
    Route::get('/nia/callback', [NiaMockController::class, 'callback'])->name('nia.mock.callback');

    Route::post('/profile/email', [ProfileController::class, 'updateEmail'])->name('profile.email');
    Route::post('/profile/password', [ProfileController::class, 'updatePassword'])->name('profile.password');

    Route::get('/application/create/{program_id}', [ApplicationController::class, 'create'])->name('application.create');

    Route::get('/application/{id}/personal',    [ApplicationController::class, 'step1'])->name('application.step1');
    Route::get('/application/{id}/education',   [ApplicationController::class, 'step2'])->name('application.step2');
    Route::get('/application/{id}/additional',  [ApplicationController::class, 'step3'])->name('application.step3');
    Route::get('/application/{id}/payment',     [ApplicationController::class, 'step4'])->name('application.step4');
    Route::get('/application/{id}/summary',     [ApplicationController::class, 'step5'])->name('application.step5');

    Route::patch('/application/{id}/autosave',  [ApplicationController::class, 'autosave'])->name('application.autosave');
    Route::post('/application/{id}/upload',     [ApplicationController::class, 'uploadAttachment'])->name('application.uploadAttachment');
    Route::post('/application/{id}/submit',     [ApplicationController::class, 'submit'])->name('application.submit');

    Route::delete(
        '/application/{id}/attachment/{attachmentId}',
        [ApplicationController::class, 'deleteAttachment']
    )->name('application.deleteAttachment');
});

Route::prefix('admin')->group(function () {
    Route::get('/login',           [AdminController::class, 'showLogin'])->name('admin.login');
    Route::post('/login',          [AdminController::class, 'login'])->name('admin.login.post');
    Route::get('/dashboard',       [AdminController::class, 'dashboard'])->name('admin.dashboard');
    Route::get('/application/{id}', [AdminController::class, 'showApplication'])->name('admin.application.show');
});

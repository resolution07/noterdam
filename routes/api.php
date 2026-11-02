<?php

use App\Http\Controllers\Api\Admin\InviteController;
use App\Http\Controllers\Api\AttachmentController;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\NoteController;
use App\Http\Controllers\Api\ProjectController;
use App\Http\Controllers\Api\ProjectMemberController;
use App\Http\Controllers\Api\SearchController;
use App\Http\Controllers\Api\Settings\PasswordController;
use App\Http\Controllers\Api\Settings\ProfileController;
use App\Http\Controllers\Api\Settings\TokenController;
use App\Http\Controllers\Api\TagController;
use Illuminate\Support\Facades\Route;

Route::post('login', [AuthController::class, 'login'])->name('api.login');
Route::post('register', [AuthController::class, 'register'])->name('api.register');

Route::middleware('auth:sanctum')->group(function () {
    Route::post('logout', [AuthController::class, 'logout'])->name('api.logout');
    Route::get('user', [AuthController::class, 'user'])->name('api.user');

    Route::get('projects', [ProjectController::class, 'index'])->name('api.projects.index');
    Route::post('projects', [ProjectController::class, 'store'])->name('api.projects.store');
    Route::get('projects/{project}', [ProjectController::class, 'show'])->name('api.projects.show');
    Route::put('projects/{project}', [ProjectController::class, 'update'])->name('api.projects.update');
    Route::delete('projects/{project}', [ProjectController::class, 'destroy'])->name('api.projects.destroy');
    Route::post('projects/{project}/archive', [ProjectController::class, 'archive'])->name('api.projects.archive');

    Route::post('projects/{project}/members', [ProjectMemberController::class, 'store'])->name('api.projects.members.store');
    Route::put('projects/{project}/members/{user}', [ProjectMemberController::class, 'update'])->name('api.projects.members.update');
    Route::delete('projects/{project}/members/{user}', [ProjectMemberController::class, 'destroy'])->name('api.projects.members.destroy');
    Route::post('projects/{project}/leave', [ProjectMemberController::class, 'leave'])->name('api.projects.leave');

    Route::get('projects/{project}/notes', [NoteController::class, 'index'])->name('api.projects.notes.index');
    Route::post('projects/{project}/notes', [NoteController::class, 'store'])->name('api.projects.notes.store');
    Route::get('notes/{note}', [NoteController::class, 'show'])->name('api.notes.show');
    Route::put('notes/{note}', [NoteController::class, 'update'])->name('api.notes.update');
    Route::delete('notes/{note}', [NoteController::class, 'destroy'])->name('api.notes.destroy');

    Route::post('projects/{project}/notes/{note}/attachments', [AttachmentController::class, 'store'])->name('api.attachments.store');
    Route::delete('attachments/{attachment}', [AttachmentController::class, 'destroy'])->name('api.attachments.destroy');
    Route::get('attachments/{attachment}/download', [AttachmentController::class, 'download'])->name('api.attachments.download');
    Route::get('attachments/{attachment}/inline', [AttachmentController::class, 'inline'])->name('api.attachments.inline');

    Route::get('search', [SearchController::class, 'index'])->name('api.search');
    Route::get('tags', [TagController::class, 'index'])->name('api.tags.index');

    Route::get('user/profile', [ProfileController::class, 'show'])->name('api.user.profile.show');
    Route::put('user/profile', [ProfileController::class, 'update'])->name('api.user.profile.update');
    Route::put('user/password', [PasswordController::class, 'update'])->name('api.user.password.update');
    Route::get('user/tokens', [TokenController::class, 'index'])->name('api.user.tokens.index');
    Route::post('user/tokens', [TokenController::class, 'store'])->name('api.user.tokens.store');
    Route::delete('user/tokens/{token}', [TokenController::class, 'destroy'])->name('api.user.tokens.destroy');

    Route::middleware('can:admin')->group(function () {
        Route::get('admin/invites', [InviteController::class, 'index'])->name('api.admin.invites.index');
        Route::post('admin/invites', [InviteController::class, 'store'])->name('api.admin.invites.store');
        Route::delete('admin/invites/{invite}', [InviteController::class, 'destroy'])->name('api.admin.invites.destroy');
    });
});

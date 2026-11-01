<?php

use App\Http\Controllers\Admin\InviteController;
use App\Http\Controllers\AttachmentController;
use App\Http\Controllers\Auth\AuthenticatedSessionController;
use App\Http\Controllers\Auth\RegisteredUserController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\NoteController;
use App\Http\Controllers\ProjectController;
use App\Http\Controllers\ProjectMemberController;
use App\Http\Controllers\SearchController;
use App\Http\Controllers\Settings\PasswordController;
use App\Http\Controllers\Settings\ProfileController;
use App\Http\Controllers\Settings\TokenController;
use Illuminate\Support\Facades\Route;

Route::middleware('guest')->group(function () {
    Route::get('login', [AuthenticatedSessionController::class, 'create'])->name('login');
    Route::post('login', [AuthenticatedSessionController::class, 'store']);

    Route::get('register/{invite?}', [RegisteredUserController::class, 'create'])->name('register');
    Route::post('register', [RegisteredUserController::class, 'store']);
});

Route::middleware('auth')->group(function () {
    Route::post('logout', [AuthenticatedSessionController::class, 'destroy'])->name('logout');

    Route::get('/', DashboardController::class)->name('dashboard');
    Route::get('projects', [ProjectController::class, 'index'])->name('projects.index');
    Route::post('projects', [ProjectController::class, 'store'])->name('projects.store');
    Route::get('projects/{project}', [ProjectController::class, 'show'])->name('projects.show');
    Route::put('projects/{project}', [ProjectController::class, 'update'])->name('projects.update');
    Route::delete('projects/{project}', [ProjectController::class, 'destroy'])->name('projects.destroy');
    Route::post('projects/{project}/archive', [ProjectController::class, 'archive'])->name('projects.archive');

    Route::post('projects/{project}/members', [ProjectMemberController::class, 'store'])->name(
        'projects.members.store'
    );
    Route::put('projects/{project}/members/{user}', [ProjectMemberController::class, 'update'])->name(
        'projects.members.update'
    );
    Route::delete('projects/{project}/members/{user}', [ProjectMemberController::class, 'destroy'])->name(
        'projects.members.destroy'
    );
    Route::post('projects/{project}/leave', [ProjectMemberController::class, 'leave'])->name('projects.leave');

    Route::get('projects/{project}/notes', [NoteController::class, 'index'])->name('projects.notes.index');
    Route::post('projects/{project}/notes', [NoteController::class, 'store'])->name('projects.notes.store');
    Route::get('projects/{project}/notes/{note}', [NoteController::class, 'show'])->name('projects.notes.show');
    Route::get('projects/{project}/notes/{note}/edit', [NoteController::class, 'edit'])->name('projects.notes.edit');
    Route::put('projects/{project}/notes/{note}', [NoteController::class, 'update'])->name('projects.notes.update');
    Route::delete('projects/{project}/notes/{note}', [NoteController::class, 'destroy'])->name(
        'projects.notes.destroy'
    );

    Route::post('projects/{project}/notes/{note}/attachments', [AttachmentController::class, 'store'])
        ->name('attachments.store');
    Route::delete('attachments/{attachment}', [AttachmentController::class, 'destroy'])->name('attachments.destroy');
    Route::get('attachments/{attachment}/download', [AttachmentController::class, 'download'])->name(
        'attachments.download'
    );
    Route::get('attachments/{attachment}/inline', [AttachmentController::class, 'inline'])->name('attachments.inline');

    Route::get('search', SearchController::class)->name('search');

    Route::get('settings/profile', [ProfileController::class, 'edit'])->name('settings.profile.edit');
    Route::put('settings/profile', [ProfileController::class, 'update'])->name('settings.profile.update');
    Route::get('settings/password', [PasswordController::class, 'edit'])->name('settings.password.edit');
    Route::put('settings/password', [PasswordController::class, 'update'])->name('settings.password.update');
    Route::get('settings/tokens', [TokenController::class, 'index'])->name('settings.tokens.index');
    Route::post('settings/tokens', [TokenController::class, 'store'])->name('settings.tokens.store');
    Route::delete('settings/tokens/{token}', [TokenController::class, 'destroy'])->name('settings.tokens.destroy');

    Route::middleware('can:admin')->group(function () {
        Route::get('admin/invites', [InviteController::class, 'index'])->name('admin.invites.index');
        Route::post('admin/invites', [InviteController::class, 'store'])->name('admin.invites.store');
        Route::delete('admin/invites/{invite}', [InviteController::class, 'destroy'])->name('admin.invites.destroy');
    });
});

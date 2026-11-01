<?php

use App\Http\Controllers\Api\AttachmentController;
use App\Http\Controllers\Api\NoteController;
use App\Http\Controllers\Api\ProjectController;
use Illuminate\Support\Facades\Route;

Route::middleware('auth:sanctum')->group(function () {
    Route::apiResource('projects', ProjectController::class)->names('api.projects');
    Route::apiResource('projects.notes', NoteController::class)->shallow()->names('api.projects.notes');
    Route::get('attachments/{attachment}/download', [AttachmentController::class, 'download'])
        ->name('api.attachments.download');
});

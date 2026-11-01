<?php

namespace App\Providers;

use App\Models\Note;
use App\Models\Project;
use App\Models\User;
use App\Policies\NotePolicy;
use App\Policies\ProjectPolicy;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Gate;

class AuthServiceProvider extends ServiceProvider
{
    protected $policies = [
        Project::class => ProjectPolicy::class,
        Note::class => NotePolicy::class,
    ];

    public function boot(): void
    {
        Gate::define('admin', function (User $user) {
            return $user->is_admin;
        });
    }
}

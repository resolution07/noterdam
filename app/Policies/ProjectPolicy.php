<?php

namespace App\Policies;

use App\Models\Project;
use App\Models\User;

class ProjectPolicy
{
    public function view(User $user, Project $project): bool
    {
        return $project->isMember($user);
    }

    public function update(User $user, Project $project): bool
    {
        return $project->memberRole($user) === 'owner';
    }

    public function delete(User $user, Project $project): bool
    {
        return $project->memberRole($user) === 'owner';
    }

    public function manageMembers(User $user, Project $project): bool
    {
        return $project->memberRole($user) === 'owner';
    }

    public function leave(User $user, Project $project): bool
    {
        return $project->memberRole($user) !== null && $project->owner_id !== $user->id;
    }

    public function createNote(User $user, Project $project): bool
    {
        $role = $project->memberRole($user);

        return in_array($role, ['owner', 'editor'], true);
    }

    public function manageAttachments(User $user, Project $project): bool
    {
        $role = $project->memberRole($user);

        return in_array($role, ['owner', 'editor'], true);
    }
}

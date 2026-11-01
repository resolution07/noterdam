<?php

namespace App\Policies;

use App\Models\Note;
use App\Models\Project;
use App\Models\User;

class NotePolicy
{
    public function view(User $user, Note $note): bool
    {
        return $note->project->isMember($user);
    }

    public function create(User $user, Project $project): bool
    {
        $role = $project->memberRole($user);

        return in_array($role, ['owner', 'editor'], true);
    }

    public function update(User $user, Note $note): bool
    {
        $role = $note->project->memberRole($user);

        return in_array($role, ['owner', 'editor'], true);
    }

    public function delete(User $user, Note $note): bool
    {
        $role = $note->project->memberRole($user);

        if ($role === 'owner') {
            return true;
        }

        return $role === 'editor' && $note->author_id === $user->id;
    }
}

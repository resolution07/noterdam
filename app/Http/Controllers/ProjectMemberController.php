<?php

namespace App\Http\Controllers;

use App\Http\Requests\Project\MemberRequest;
use App\Http\Requests\Project\UpdateMemberRequest;
use App\Models\Project;
use App\Models\User;
use Illuminate\Http\Request;

class ProjectMemberController extends Controller
{
    public function store(MemberRequest $request, Project $project)
    {
        $this->authorize('manageMembers', $project);

        $user = User::where('email', $request->validated('email'))->firstOrFail();

        if ($project->isMember($user)) {
            return back()->with('error', 'Пользователь уже участник проекта.');
        }

        $project->members()->attach($user->id, ['role' => $request->validated('role')]);

        return back()->with('success', 'Участник добавлен.');
    }

    public function update(UpdateMemberRequest $request, Project $project, User $user)
    {
        $this->authorize('manageMembers', $project);

        if ($project->owner_id === $user->id) {
            return back()->with('error', 'Нельзя изменить роль владельца.');
        }

        $project->members()->updateExistingPivot($user->id, ['role' => $request->validated('role')]);

        return back()->with('success', 'Роль обновлена.');
    }

    public function destroy(Request $request, Project $project, User $user)
    {
        $this->authorize('manageMembers', $project);

        if ($project->owner_id === $user->id) {
            return back()->with('error', 'Нельзя удалить владельца.');
        }

        $project->members()->detach($user->id);

        return back()->with('success', 'Участник удалён.');
    }

    public function leave(Request $request, Project $project)
    {
        $this->authorize('leave', $project);

        $project->members()->detach($request->user()->id);

        return redirect()->route('dashboard')
            ->with('success', 'Вы покинули проект.');
    }
}

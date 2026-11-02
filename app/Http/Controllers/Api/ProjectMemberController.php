<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Project\MemberRequest;
use App\Http\Requests\Project\UpdateMemberRequest;
use App\Models\Project;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ProjectMemberController extends Controller
{
    public function store(MemberRequest $request, Project $project): JsonResponse
    {
        $user = User::where('email', $request->validated('email'))->firstOrFail();

        if ($project->isMember($user)) {
            return response()->json([
                'message' => 'Пользователь уже участник проекта.',
            ], 422);
        }

        $project->members()->attach($user->id, ['role' => $request->validated('role')]);

        return response()->json(['message' => 'Участник добавлен.']);
    }

    public function update(UpdateMemberRequest $request, Project $project, User $user): JsonResponse
    {
        if ($project->owner_id === $user->id) {
            return response()->json([
                'message' => 'Нельзя изменить роль владельца.',
            ], 422);
        }

        $project->members()->updateExistingPivot($user->id, ['role' => $request->validated('role')]);

        return response()->json(['message' => 'Роль обновлена.']);
    }

    public function destroy(Request $request, Project $project, User $user): JsonResponse
    {
        $this->authorize('manageMembers', $project);

        if ($project->owner_id === $user->id) {
            return response()->json([
                'message' => 'Нельзя удалить владельца.',
            ], 422);
        }

        $project->members()->detach($user->id);

        return response()->json(['message' => 'Участник удалён.']);
    }

    public function leave(Request $request, Project $project): JsonResponse
    {
        $this->authorize('leave', $project);

        $project->members()->detach($request->user()->id);

        return response()->json(['message' => 'Вы покинули проект.']);
    }
}

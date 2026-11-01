<?php

namespace App\Console\Commands;

use App\Models\Project;
use Illuminate\Console\Command;

class CleanupDeletedProjects extends Command
{
    protected $signature = 'cleanup:deleted-projects {--days=1 : Delete projects soft-deleted more than N days ago}';

    protected $description = 'Permanently delete soft-deleted projects and their attachments.';

    public function handle(): int
    {
        $cutoff = now()->subDays((int) $this->option('days'));

        $projects = Project::onlyTrashed()
            ->where('deleted_at', '<=', $cutoff)
            ->with('notes.attachments')
            ->get();

        foreach ($projects as $project) {
            foreach ($project->notes as $note) {
                foreach ($note->attachments as $attachment) {
                    $attachment->delete();
                }
                $note->forceDelete();
            }
            $project->forceDelete();
        }

        $this->info("Cleaned up {$projects->count()} projects.");

        return self::SUCCESS;
    }
}

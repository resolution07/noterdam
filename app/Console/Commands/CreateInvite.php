<?php

namespace App\Console\Commands;

use App\Models\Invite;
use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Str;

class CreateInvite extends Command
{
    protected $signature = 'invite:create
                            {--email= : Restrict invite to email}
                            {--created-by= : Creator user ID}
                            {--expires= : Expiration date}';

    protected $description = 'Create a registration invite.';

    public function handle(): int
    {
        $creatorId = $this->option('created-by') ?? User::first()?->id;

        if (! $creatorId) {
            $this->error('No users found. Create a user first.');

            return self::FAILURE;
        }

        $invite = Invite::create([
            'code' => Str::random(32),
            'email' => $this->option('email'),
            'created_by' => $creatorId,
            'expires_at' => $this->option('expires'),
        ]);

        $this->info("Invite code: {$invite->code}");
        $this->info('URL: '.route('register', ['invite' => $invite->code]));

        return self::SUCCESS;
    }
}

<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Hash;

class CreateUser extends Command
{
    protected $signature = 'user:create
                            {--name= : User name}
                            {--email= : User email}
                            {--password= : User password}
                            {--admin : Grant admin rights}';

    protected $description = 'Create a new user (first admin). For non-interactive mode use --name, --email and --password.';

    public function handle(): int
    {
        $isFirst = User::count() === 0;

        $name = $this->option('name') ?: $this->ask('Name');
        $email = $this->option('email') ?: $this->ask('Email');
        $password = $this->option('password') ?: $this->secret('Password');

        if (empty($name) || empty($email) || empty($password)) {
            $this->error('Name, email and password are required.');

            return self::FAILURE;
        }

        $user = User::create([
            'name' => $name,
            'email' => $email,
            'password' => Hash::make($password),
            'is_admin' => $isFirst || $this->option('admin'),
            'settings' => ['theme' => 'dark'],
        ]);

        $this->info("User [{$user->email}] created. Admin: " . ($user->is_admin ? 'yes' : 'no'));

        return self::SUCCESS;
    }
}

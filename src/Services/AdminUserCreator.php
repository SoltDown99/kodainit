<?php

namespace KodaInit\Services;

use Symfony\Component\Process\Process;

class AdminUserCreator
{
    public function create(string $projectPath): void
    {
        $command = "
        \$user = App\Models\User::updateOrCreate(
            ['email' => 'admin@example.com'],
            [
                'name' => 'Administrator',
                'password' => bcrypt('password'),
                'email_verified_at' => now(),
            ]
        );
        ";

        $process = new Process([
            'docker',
            'compose',
            'exec',
            '-T',
            'app',
            'php',
            'artisan',
            'tinker',
            '--execute=' . $command
        ], $projectPath);

        $process->mustRun();
    }
}
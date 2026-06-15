<?php

namespace KodaInit\Services;

use Symfony\Component\Process\Process;

class AdminUserCreator
{
    public function create(
        string $projectPath,
        string $name = 'Administrator',
        string $email = 'admin@koda.local',
        string $password = 'password'
    ): void {

        $nameEscaped = $this->escapeForPhpSingleQuotes($name);
        $emailEscaped = $this->escapeForPhpSingleQuotes($email);
        $passwordEscaped = $this->escapeForPhpSingleQuotes($password);

        $command = "
        \$user = App\Models\User::updateOrCreate(
            ['email' => '{$emailEscaped}'],
            [
                'name' => '{$nameEscaped}',
                'password' => bcrypt('{$passwordEscaped}'),
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

    private function escapeForPhpSingleQuotes(string $value): string
    {
        return str_replace(
            ['\\', "'"],
            ['\\\\', "\\'"],
            $value
        );
    }
}
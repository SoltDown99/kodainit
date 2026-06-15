<?php

namespace KodaInit\Services;

use Symfony\Component\Process\Process;

class MigrationRunner
{
    public function migrate(string $projectPath): void
    {
        $process = new Process([
            'docker',
            'compose',
            'exec',
            '-T',
            '--user',
            'www-data',
            'app',
            'php',
            'artisan',
            'migrate',
            '--force',
        ], $projectPath);

        $process->setTimeout(null);

        $process->mustRun(function ($type, $buffer) {
            echo $buffer;
        });
    }
}
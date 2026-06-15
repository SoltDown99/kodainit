<?php

namespace KodaInit\Services;

use Symfony\Component\Process\Process;

class FilamentInstaller
{
    public function install(string $projectPath): void
    {
        $this->composerRequire($projectPath);

        $this->installPanel($projectPath);
    }

    private function composerRequire(string $projectPath): void
    {
        $process = new Process([
            'docker',
            'compose',
            'exec',
            '-T',
            '--user',
            'www-data',
            'app',
            'composer',
            'require',
            'filament/filament:^4.0'
        ], $projectPath);

        $process->setTimeout(null);

        $process->mustRun(function ($type, $buffer) {
            echo $buffer;
        });
    }

    private function installPanel(string $projectPath): void
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
            'filament:install',
            '--panels'
        ], $projectPath);

        $process->setTimeout(null);

        $process->mustRun(function ($type, $buffer) {
            echo $buffer;
        });
    }
}
<?php

namespace KodaInit\Services;

use Symfony\Component\Process\Process;

class LaravelInstaller
{
    public function install(string $projectName): void
    {
        if (is_dir($projectName)) {
            throw new \RuntimeException(
                "Directory {$projectName} already exists."
            );
        }

        $process = new Process([
            'composer',
            'create-project',
            'laravel/laravel',
            $projectName,
            '^12.0'
        ]);

        $process->setTimeout(null);

        $process->mustRun(function ($type, $buffer) {
            echo $buffer;
        });
    }
}
<?php

namespace KodaInit\Services;

use Symfony\Component\Process\Process;

class LaravelInstaller
{
    public function install(string $projectName, string $workingDir): void
    {
        $process = new Process([
            'composer',
            'create-project',
            'laravel/laravel',
            $projectName,
            '^12.0'
        ], $workingDir);

        $process->setTimeout(null);

        $process->mustRun(function ($type, $buffer) {
            echo $buffer;
        });
    }
}
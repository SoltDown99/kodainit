<?php

namespace KodaInit\Services;

use Symfony\Component\Process\Process;

class DockerLauncher
{
    public function checkDocker(): void
    {
        $process = new Process([
            'docker',
            '--version'
        ]);

        $process->mustRun();
    }

    public function build(string $projectPath): void
    {
        $process = new Process([
            'docker',
            'compose',
            'build'
        ], $projectPath);

        $process->setTimeout(null);

        $process->mustRun(function ($type, $buffer) {
            echo $buffer;
        });
    }

    public function up(string $projectPath): void
    {
        $process = new Process([
            'docker',
            'compose',
            'up',
            '-d'
        ], $projectPath);

        $process->setTimeout(null);

        $process->mustRun(function ($type, $buffer) {
            echo $buffer;
        });
    }

    public function waitForDatabase(
        string $projectPath,
        int $timeout = 60
    ): void {

        $start = time();

        while (true) {

            $process = new Process([
                'docker',
                'compose',
                'ps',
                '--status',
                'running',
                '--format',
                'json',
                'postgres',
            ], $projectPath);

            $process->run();

            $output = trim($process->getOutput());

            if ($output !== '') {

                if (str_contains($output, 'healthy')) {
                    return;
                }
            }

            if ((time() - $start) >= $timeout) {
                throw new \RuntimeException(
                    'PostgreSQL did not become healthy in time.'
                );
            }

            sleep(2);
        }
    }

    public function waitForApp(
        string $projectPath,
        int $timeout = 60
    ): void {

        $start = time();

        while (true) {

            $process = new Process([
                'docker',
                'compose',
                'ps',
                '--status',
                'running',
                '--format',
                'json',
                'app',
            ], $projectPath);

            $process->run();

            $output = trim($process->getOutput());

            if ($output !== '') {
                return;
            }

            if ((time() - $start) >= $timeout) {
                throw new \RuntimeException(
                    'Application container did not start.'
                );
            }

            sleep(2);
        }
    }
}
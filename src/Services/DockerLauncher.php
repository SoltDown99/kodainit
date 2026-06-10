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
        string $projectName,
        int $timeout = 60
    ): void {

        $container = "{$projectName}-postgres-1";

        $start = time();

        while (true) {

            $process = new Process([
                'docker',
                'inspect',
                '--format',
                '{{.State.Health.Status}}',
                $container
            ]);

            $process->run();

            $status = trim(
                $process->getOutput()
            );

            if ($status === 'healthy') {
                return;
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
        string $projectName,
        int $timeout = 60
    ): void {

        $container = "{$projectName}-app-1";

        $start = time();

        while (true) {

            $process = new Process([
                'docker',
                'inspect',
                '--format',
                '{{.State.Status}}',
                $container
            ]);

            $process->run();

            $status = trim(
                $process->getOutput()
            );

            if ($status === 'running') {
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
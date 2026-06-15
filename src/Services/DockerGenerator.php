<?php

namespace KodaInit\Services;

use RuntimeException;

class DockerGenerator
{
    public function generate(string $projectPath, int $appPort = 8888): void
    {
        $this->createDirectories($projectPath);

        $this->copyTemplate(
            'docker-compose.stub',
            $projectPath . '/docker-compose.yml',
            [
                '{{APP_PORT}}' => (string) $appPort,
            ]
        );

        $this->copyTemplate(
            '.dockerignore.stub',
            $projectPath . '/.dockerignore'
        );

        $this->copyTemplate(
            'Dockerfile.stub',
            $projectPath . '/docker/php/Dockerfile'
        );

        $this->copyTemplate(
            'entrypoint.stub',
            $projectPath . '/docker/php/entrypoint.sh'
        );

        $this->copyTemplate(
            'nginx.conf.stub',
            $projectPath . '/docker/nginx/default.conf'
        );

        $this->setPermissions($projectPath);
    }

    private function createDirectories(string $projectPath): void
    {
        @mkdir($projectPath . '/docker', 0777, true);

        @mkdir($projectPath . '/docker/php', 0777, true);

        @mkdir($projectPath . '/docker/nginx', 0777, true);
    }

    private function copyTemplate(
        string $source,
        string $destination,
        array $replacements = []
    ): void {

        $templatePath = __DIR__ . '/../../templates/' . $source;

        if (! file_exists($templatePath)) {
            throw new RuntimeException(
                "Template not found: {$source}"
            );
        }

        $content = file_get_contents($templatePath);

        if ($content === false) {
            throw new RuntimeException(
                "Unable to read template: {$source}"
            );
        }

        // Forzar formato Linux (LF)
        $content = str_replace(
            ["\r\n", "\r"],
            "\n",
            $content
        );

        if (! empty($replacements)) {
            $content = str_replace(
                array_keys($replacements),
                array_values($replacements),
                $content
            );
        }

        file_put_contents(
            $destination,
            $content
        );
    }

    private function setPermissions(
        string $projectPath
    ): void {

        $entrypoint = $projectPath .
            '/docker/php/entrypoint.sh';

        if (file_exists($entrypoint)) {
            chmod($entrypoint, 0755);
        }
    }
}
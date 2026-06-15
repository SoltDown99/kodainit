<?php

namespace KodaInit\Services;

use RuntimeException;

class DockerGenerator
{
    /**
     * Fallback UID/GID used on platforms where the host user id cannot be
     * detected (e.g. Windows). Docker Desktop on Windows/macOS already
     * translates container file ownership to the host user transparently,
     * so this value is mostly a sane default for the build args.
     */
    private const FALLBACK_UID = 1000;

    private const FALLBACK_GID = 1000;

    public function generate(string $projectPath, int $appPort = 8888): void
    {
        $this->createDirectories($projectPath);

        [$uid, $gid] = $this->resolveHostUidGid();

        $this->copyTemplate(
            'docker-compose.stub',
            $projectPath . '/docker-compose.yml',
            [
                '{{APP_PORT}}' => (string) $appPort,
                '{{HOST_UID}}' => (string) $uid,
                '{{HOST_GID}}' => (string) $gid,
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

    /**
     * Detect the current host user's UID/GID so the container's www-data
     * user can be aligned with it. This avoids root-owned files being
     * written back to the host through bind mounts on Linux.
     *
     * Falls back to 1000:1000 on platforms without POSIX support (Windows),
     * where Docker Desktop already handles ownership translation.
     *
     * @return array{0:int,1:int}
     */
    private function resolveHostUidGid(): array
    {
        if (function_exists('posix_getuid') && function_exists('posix_getgid')) {

            $uid = posix_getuid();

            $gid = posix_getgid();

            if (is_int($uid) && is_int($gid)) {
                return [$uid, $gid];
            }
        }

        return [self::FALLBACK_UID, self::FALLBACK_GID];
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
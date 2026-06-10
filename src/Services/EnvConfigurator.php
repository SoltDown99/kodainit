<?php

namespace KodaInit\Services;

class EnvConfigurator
{
    public function configure(string $projectPath): void
    {
        $envFile = $projectPath . '/.env';

        $content = file_get_contents($envFile);

        $replacements = [

            // PostgreSQL
            'DB_CONNECTION=sqlite'
                => 'DB_CONNECTION=pgsql',

            '# DB_HOST=127.0.0.1'
                => 'DB_HOST=postgres',

            '# DB_PORT=3306'
                => 'DB_PORT=5432',

            '# DB_DATABASE=laravel'
                => 'DB_DATABASE=laravel',

            '# DB_USERNAME=root'
                => 'DB_USERNAME=laravel',

            '# DB_PASSWORD='
                => 'DB_PASSWORD=secret',

            // Evitar dependencias de tablas adicionales
            'CACHE_STORE=database'
                => 'CACHE_STORE=file',

            'SESSION_DRIVER=database'
                => 'SESSION_DRIVER=file',

            'QUEUE_CONNECTION=database'
                => 'QUEUE_CONNECTION=sync',
        ];

        $content = str_replace(
            array_keys($replacements),
            array_values($replacements),
            $content
        );

        file_put_contents(
            $envFile,
            $content
        );
    }
}
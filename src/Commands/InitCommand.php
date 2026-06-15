<?php

namespace KodaInit\Commands;

use Throwable;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\Question;
use KodaInit\Services\LaravelInstaller;
use KodaInit\Services\DockerGenerator;
use KodaInit\Services\EnvConfigurator;
use KodaInit\Services\DockerLauncher;
use KodaInit\Services\MigrationRunner;
use KodaInit\Services\FilamentInstaller;
use KodaInit\Services\AdminUserCreator;

class InitCommand extends Command
{
    private const DEFAULT_APP_PORT = 8888;

    private const DEFAULT_ADMIN_NAME = 'Administrator';

    private const DEFAULT_ADMIN_EMAIL = 'admin@koda.local';

    private const DEFAULT_ADMIN_PASSWORD = 'password';

    protected function configure(): void
    {
        $this
            ->setName('init')
            ->setDescription(
                'Create a new Laravel project with Docker, PostgreSQL and Filament'
            )
            ->addArgument(
                'name',
                InputArgument::REQUIRED,
                'Project name'
            )
            ->addOption(
                'interactive',
                'i',
                InputOption::VALUE_NONE,
                'Ask for project configuration (app port, admin credentials) before creating the project'
            );
    }

    protected function execute(
        InputInterface $input,
        OutputInterface $output
    ): int {

        $name = $input->getArgument('name');

        if (is_dir($name)) {

            $output->writeln(
                "<error>Directory '{$name}' already exists.</error>"
            );

            return Command::FAILURE;
        }

        $config = $this->resolveConfiguration($input, $output);

        if ($config === null) {
            return Command::FAILURE;
        }

        [$appPort, $adminName, $adminEmail, $adminPassword] = $config;

        try {

            $dockerLauncher = new DockerLauncher();

            $output->writeln(
                '<info>Checking Docker installation...</info>'
            );

            $dockerLauncher->checkDocker();

            $output->writeln(
                "<info>Creating Laravel 12 project: {$name}</info>"
            );

            $installer = new LaravelInstaller();

            $installer->install($name);

            $output->writeln(
                '<info>Generating Docker configuration...</info>'
            );

            $dockerGenerator = new DockerGenerator();

            $dockerGenerator->generate($name, $appPort);

            $output->writeln(
                '<info>Configuring environment...</info>'
            );

            $envConfigurator = new EnvConfigurator();

            $envConfigurator->configure($name);

            $output->writeln(
                '<info>Building Docker images...</info>'
            );

            $dockerLauncher->build($name);

            $output->writeln(
                '<info>Starting containers...</info>'
            );

            $dockerLauncher->up($name);

            $output->writeln(
                '<info>Waiting for PostgreSQL...</info>'
            );

            $dockerLauncher->waitForDatabase($name);

            $output->writeln(
                '<info>Waiting for application container...</info>'
            );

            $dockerLauncher->waitForApp($name);

            $output->writeln(
                '<info>Installing Filament 4 and admin panel...</info>'
            );

            $filamentInstaller = new FilamentInstaller();

            $filamentInstaller->install($name);

            $output->writeln(
                '<info>Running database migrations...</info>'
            );

            $migrationRunner = new MigrationRunner();

            $migrationRunner->migrate($name);

            $output->writeln(
                '<info>Creating administrator account...</info>'
            );

            $adminUserCreator = new AdminUserCreator();

            $adminUserCreator->create(
                $name,
                $adminName,
                $adminEmail,
                $adminPassword
            );

            $output->writeln('');
            $output->writeln(
                '<fg=green>✓ Project created successfully.</>'
            );
            $output->writeln('');

            $output->writeln(
                "<comment>Project:</comment> {$name}"
            );

            $output->writeln(
                '<comment>Laravel:</comment> 12'
            );

            $output->writeln(
                '<comment>PHP:</comment> 8.4'
            );

            $output->writeln(
                '<comment>Database:</comment> PostgreSQL 17'
            );

            $output->writeln(
                '<comment>Web Server:</comment> Nginx'
            );

            $output->writeln(
                '<comment>Admin Panel:</comment> Filament 4'
            );

            $output->writeln('');

            $output->writeln(
                "<info>Application URL:</info> http://localhost:{$appPort}"
            );

            $output->writeln(
                "<info>Admin Panel:</info> http://localhost:{$appPort}/admin"
            );

            $output->writeln(
                "<info>Admin Name:</info> {$adminName}"
            );

            $output->writeln(
                "<info>Admin Email:</info> {$adminEmail}"
            );

            if ($adminPassword === self::DEFAULT_ADMIN_PASSWORD) {
                $output->writeln(
                    "<info>Admin Password:</info> {$adminPassword}"
                );
            } else {
                $output->writeln(
                    '<info>Admin Password:</info> (the one you entered)'
                );
            }

            $output->writeln('');

            return Command::SUCCESS;

        } catch (Throwable $e) {

            $output->writeln('');

            $output->writeln(
                '<error>Project creation failed.</error>'
            );

            $output->writeln('');

            $output->writeln(
                '<comment>Exception:</comment> ' . get_class($e)
            );

            $output->writeln(
                "<error>{$e->getMessage()}</error>"
            );

            $output->writeln('');

            return Command::FAILURE;
        }
    }

    /**
     * Resolve project configuration values.
     *
     * Returns [appPort, adminName, adminEmail, adminPassword] or null on
     * invalid input.
     *
     * @return array{0:int,1:string,2:string,3:string}|null
     */
    private function resolveConfiguration(
        InputInterface $input,
        OutputInterface $output
    ): ?array {

        if (! $input->getOption('interactive')) {
            return [
                self::DEFAULT_APP_PORT,
                self::DEFAULT_ADMIN_NAME,
                self::DEFAULT_ADMIN_EMAIL,
                self::DEFAULT_ADMIN_PASSWORD,
            ];
        }

        $helper = $this->getHelper('question');

        $output->writeln('<info>Project configuration</info>');
        $output->writeln('<comment>Press Enter to accept the default value shown in brackets.</comment>');
        $output->writeln('');

        // App port
        $portQuestion = new Question(
            sprintf(
                'Application port [%d]: ',
                self::DEFAULT_APP_PORT
            ),
            (string) self::DEFAULT_APP_PORT
        );

        $portQuestion->setValidator(function ($answer) {

            $answer = trim((string) $answer);

            if ($answer === '') {
                return self::DEFAULT_APP_PORT;
            }

            if (! ctype_digit($answer)) {
                throw new \RuntimeException(
                    'Port must be a number.'
                );
            }

            $port = (int) $answer;

            if ($port < 1 || $port > 65535) {
                throw new \RuntimeException(
                    'Port must be between 1 and 65535.'
                );
            }

            return $port;
        });

        $appPort = $helper->ask($input, $output, $portQuestion);

        // Admin name
        $nameQuestion = new Question(
            sprintf(
                'Administrator name [%s]: ',
                self::DEFAULT_ADMIN_NAME
            ),
            self::DEFAULT_ADMIN_NAME
        );

        $nameQuestion->setValidator(function ($answer) {

            $answer = trim((string) $answer);

            return $answer === '' ? self::DEFAULT_ADMIN_NAME : $answer;
        });

        $adminName = $helper->ask($input, $output, $nameQuestion);

        // Admin email
        $emailQuestion = new Question(
            sprintf(
                'Administrator email [%s]: ',
                self::DEFAULT_ADMIN_EMAIL
            ),
            self::DEFAULT_ADMIN_EMAIL
        );

        $emailQuestion->setValidator(function ($answer) {

            $answer = trim((string) $answer);

            if ($answer === '') {
                return self::DEFAULT_ADMIN_EMAIL;
            }

            if (! filter_var($answer, FILTER_VALIDATE_EMAIL)) {
                throw new \RuntimeException(
                    'Please enter a valid email address.'
                );
            }

            return $answer;
        });

        $adminEmail = $helper->ask($input, $output, $emailQuestion);

        // Admin password (hidden input, with confirmation)
        $adminPassword = $this->askForPassword($input, $output, $helper);

        if ($adminPassword === null) {
            return null;
        }

        $output->writeln('');

        return [$appPort, $adminName, $adminEmail, $adminPassword];
    }

    private function askForPassword(
        InputInterface $input,
        OutputInterface $output,
        $helper
    ): ?string {

        $maxAttempts = 3;

        for ($attempt = 1; $attempt <= $maxAttempts; $attempt++) {

            $passwordQuestion = new Question(
                sprintf(
                    'Administrator password (leave empty for default "%s"): ',
                    self::DEFAULT_ADMIN_PASSWORD
                )
            );

            $passwordQuestion->setHidden(true);
            $passwordQuestion->setHiddenFallback(false);

            $password = $helper->ask($input, $output, $passwordQuestion);

            $password = (string) $password;

            if ($password === '') {
                return self::DEFAULT_ADMIN_PASSWORD;
            }

            $confirmQuestion = new Question(
                'Confirm administrator password: '
            );

            $confirmQuestion->setHidden(true);
            $confirmQuestion->setHiddenFallback(false);

            $confirmation = (string) $helper->ask($input, $output, $confirmQuestion);

            if ($password === $confirmation) {
                return $password;
            }

            $output->writeln(
                '<error>Passwords do not match. Please try again.</error>'
            );
        }

        $output->writeln(
            '<error>Too many failed attempts. Aborting.</error>'
        );

        return null;
    }
}
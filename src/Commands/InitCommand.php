<?php

namespace KodaInit\Commands;

use Throwable;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use KodaInit\Services\LaravelInstaller;
use KodaInit\Services\DockerGenerator;
use KodaInit\Services\EnvConfigurator;
use KodaInit\Services\DockerLauncher;
use KodaInit\Services\MigrationRunner;
use KodaInit\Services\FilamentInstaller;
use KodaInit\Services\AdminUserCreator;

class InitCommand extends Command
{
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

            $dockerGenerator->generate($name);

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

            $adminUserCreator->create($name);

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
                '<info>Application URL:</info> http://localhost:8888'
            );

            $output->writeln(
                '<info>Admin Panel:</info> http://localhost:8888/admin'
            );

            $output->writeln(
                '<info>Admin Email:</info> admin@koda.local'
            );

            $output->writeln(
                '<info>Admin Password:</info> password'
            );

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
}
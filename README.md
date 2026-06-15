# Koda Init

Koda Init is an open-source CLI tool that bootstraps a complete Laravel development environment in a single command.

The goal is to eliminate repetitive project setup and provide a ready-to-develop stack including:

- Laravel 12
- PHP 8.4
- PostgreSQL 17
- Docker
- Nginx
- Filament 4

---

## Features

- Create a fresh Laravel 12 project
- Generate Docker infrastructure automatically
- Configure PostgreSQL 17
- Build and start containers
- Run database migrations
- Install Filament 4
- Create an administrator account automatically
- Interactive configuration wizard (custom app port and admin credentials)
- Host-aligned file permissions (no sudo needed to delete generated projects on Linux)
- Ready-to-use development environment

---

## Stack

| Component  | Version |
| ---------- | ------- |
| PHP        | 8.4     |
| Laravel    | 12      |
| PostgreSQL | 17      |
| Nginx      | Latest  |
| Filament   | 4       |

---

## Requirements

Before using Koda Init, ensure the following tools are installed:

- PHP 8.4+
- Composer
- Docker
- Docker Compose

Verify your installation:

```bash
php -v
composer --version
docker --version
docker compose version
```

---

## Installation

Clone the repository:

```bash
git clone https://github.com/SoltDown99/kodainit.git

cd koda-init
```

Install dependencies:

```bash
composer install
```

Make the CLI executable:

```bash
chmod +x bin/koda
```

---

## Usage

Quick start (default configuration)

Create a new project with the default settings:

```bash
./bin/koda init crm
```

This uses the default application port (8888) and the default
administrator account (admin@koda.local / password), without asking
any questions.

Interactive mode

Use the --interactive (or -i) flag to customize the project during
creation:

```bash
./bin/koda init crm --interactive
```

```bash
./bin/koda init crm --i
```

In interactive mode, Koda Init will ask for:

Application port — the host port used to access the app via Nginx
(default: 8888). Must be a number between 1 and 65535.
Administrator name (default: Administrator)
Administrator email (default: admin@koda.local), validated as a
proper email address
Administrator password — entered with hidden input and confirmed by
typing it twice. Leave empty to keep the default password (password).

Pressing Enter on any prompt accepts the default value shown in
brackets.

What Koda Init does

Regardless of the mode used, Koda Init will automatically:

- Create a new Laravel project
- Generate Docker configuration (using the configured app port)
- Configure PostgreSQL
- Build Docker images
- Start containers
- Install Filament
- Run migrations
- Create an administrator account (using the configured credentials)

---

## Generated Project Structure

```text
crm/
├── app/
├── bootstrap/
├── config/
├── database/
├── docker/
│   ├── nginx/
│   └── php/
├── public/
├── resources/
├── routes/
├── storage/
├── docker-compose.yml
└── artisan
```

---

## Accessing the Application

Application:

```text
http://localhost:8888
```

If you used --interactive and chose a different port, replace 8888
with the port you configured.

Filament Admin Panel:

```text
http://localhost:8888/admin
```

Default Administrator:

```text
Email: admin@koda.local
Password: password
```

If you used --interactive, the admin panel uses the name, email and
password you provided during setup instead of the defaults above.

Change the administrator credentials immediately after the first login.

> Change the administrator credentials immediately after the first login.

---

## Development

Project structure:

```text
koda-init/
├── bin/
├── src/
│   ├── Commands/
│   └── Services/
├── templates/
├── composer.json
└── README.md
```

---

## Roadmap

### v0.2.0

- Global Composer installation
- Custom admin credentials
- Custom ports
- Project configuration wizard (--interactive flag)

### v0.3.0

- Optional Redis container
- Optional Queue worker
- Mailpit support
- HTTPS local development

### v1.0.0

- Production-ready scaffolding
- Multiple stack presets
- Package ecosystem
- Plugin architecture

---

## Contributing

Contributions, suggestions, and bug reports are welcome.

Please open an issue or submit a pull request.

---

## License

This project is open-sourced software licensed under the MIT license.

---

## Author

Hector Daniel M.

GitHub:
https://github.com/SoltDown99

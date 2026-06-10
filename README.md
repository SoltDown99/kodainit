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

Create a new project:

```bash
./bin/koda init crm
```

Koda Init will automatically:

1. Create a new Laravel project
2. Generate Docker configuration
3. Configure PostgreSQL
4. Build Docker images
5. Start containers
6. Install Filament
7. Run migrations
8. Create an administrator account

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

Filament Admin Panel:

```text
http://localhost:8888/admin
```

Default Administrator:

```text
Email: admin@koda.local
Password: password
```

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
- Project configuration wizard

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

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
- Host-aligned file permissions (no `sudo` needed to delete generated projects on Linux/WSL2)
- Projects are always created in the directory where you run the command, not inside `koda-init/`
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

### Project location

Koda Init always creates the project in **the directory where you run the command**, regardless of where `koda-init` is cloned. For example, if your workspace is `~/proyectos/` and `koda-init` is cloned inside it:

```text
~/proyectos/
├── koda-init/     ← cloned repo
└── crm/           ← project gets created here ✓
```

```bash
# Standing in ~/proyectos/
~/proyectos$ ./koda-init/bin/koda init crm
```

The project will be created at `~/proyectos/crm`, not inside `~/proyectos/koda-init/crm`.

---

### Quick start (default configuration)

Create a new project with the default settings:

```bash
./bin/koda init crm
```

This uses the default application port (`8888`) and the default
administrator account (`admin@koda.local` / `password`), without asking
any questions.

---

### Interactive mode

Use the `--interactive` (or `-i`) flag to customize the project during creation:

```bash
./bin/koda init crm --interactive
```

```bash
./bin/koda init crm -i
```

In interactive mode, Koda Init will ask for:

- **Application port** — the host port used to access the app via Nginx
  (default: `8888`). Must be a number between `1` and `65535`.
- **Administrator name** (default: `Administrator`)
- **Administrator email** (default: `admin@koda.local`), validated as a
  proper email address.
- **Administrator password** — entered with hidden input and confirmed by
  typing it twice. Leave empty to keep the default password (`password`).

Pressing **Enter** on any prompt accepts the default value shown in brackets.

---

### What Koda Init does

Regardless of the mode used, Koda Init will automatically:

1. Create a new Laravel project in your current directory
2. Generate Docker configuration (using the configured app port)
3. Configure PostgreSQL
4. Build Docker images
5. Start containers
6. Install Filament
7. Run migrations
8. Create an administrator account (using the configured credentials)

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

## File Permissions

When containers write to bind-mounted directories (e.g. `storage`,
`bootstrap/cache`, `vendor`), files can end up owned by `root` or by a
UID that doesn't match your host user — making the generated project
hard or impossible to delete without `sudo`.

Koda Init avoids this in two complementary ways:

1. **UID/GID alignment** — detects your host user's UID and GID (via
   `posix_getuid()` / `posix_getgid()`) when generating the project, and
   passes them as build arguments to the PHP container. The container's
   `www-data` user is aligned to match your host user.

2. **Running as `www-data`** — every command executed inside the container
   during setup (`composer require`, `php artisan migrate`,
   `php artisan filament:install`, `php artisan tinker`) runs as
   `www-data` via `docker compose exec --user www-data`. Since `www-data`
   shares your host UID/GID, every file created — including `vendor/`,
   `storage/`, and `bootstrap/cache/` — is owned by you on the host.

| Platform       | Behavior                                                                   |
| -------------- | -------------------------------------------------------------------------- |
| Linux          | UID/GID detected automatically. `rm -rf myproject` works without `sudo`.   |
| macOS          | UID/GID detected automatically. Docker Desktop handles ownership natively. |
| Windows (WSL2) | UID/GID detected from the WSL2 user. Docker Desktop handles the bridge.    |

### Fixing a project created before this change

If a project was generated by an older version of Koda Init, some files
(typically inside `vendor/`) may be owned by `root` and can't be deleted
with `rm -rf`. Fix it in two steps:

1. Reclaim ownership (run from the project root):

   ```bash
   docker compose exec --user root app chown -R www-data:www-data /var/www/html
   ```

2. Rebuild the image so future commands run as the correctly-aligned user:

   ```bash
   docker compose build --no-cache
   docker compose up -d
   ```

After this, the project can be deleted normally without `sudo`.

---

## Accessing the Application

Application:

```text
http://localhost:8888
```

> If you used `--interactive` and chose a different port, replace `8888`
> with the port you configured.

Filament Admin Panel:

```text
http://localhost:8888/admin
```

Default Administrator:

```text
Email:    admin@koda.local
Password: password
```

> If you used `--interactive`, the admin panel uses the credentials
> you provided during setup instead of the defaults above.

> Change the administrator credentials immediately after the first login.

---

## Koda Init Project Structure

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

- [x] Custom admin credentials
- [x] Custom ports
- [x] Project configuration wizard (`--interactive` flag)
- [x] Host-aligned file permissions (no `sudo` on delete)
- [x] Projects created in the caller's working directory
- [ ] Global Composer installation (`composer global require`)

### v0.3.0

- Optional Redis container
- Optional Queue worker
- Mailpit support
- HTTPS local development
- Port availability check (detect ports already in use on the host)

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

GitHub: https://github.com/SoltDown99

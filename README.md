# Web application for Cyklo Shop

A small PHP content-management system and website for a family-run bike shop, rewritten from Nette 2.0.4 to **Nette 3.3 on PHP 8.4** as a learning exercise.

## About

The original was a website with a custom admin area, built around 2012 for my grandfather's bike shop. It was unpaid; the main reason I built it was to learn the [Nette Framework](https://nette.org/).

This repository is the rewrite. The goal was not to add features but to learn how a modern Nette project is put together: PSR-4 autoloading, dependency injection, typed code checked by PHPStan, a Docker-based development environment, database migrations, and an automated test suite. The old version is still in the git history.

The public site (`FrontModule`) shows editable pages, photo galleries and a contact page. The admin system (`AdminModule`) lets a logged-in user manage the menu tree, page content (with a TinyMCE editor) and galleries, and upload files.

The UI and all messages are in Czech. The shop's real name, address, phone numbers and logo images have been replaced with placeholders (`Cyklo shop`, `info@example.com`, `+420 123 456 789`, …).

## Status

A finished learning project. It runs and is covered by tests, but it has never been deployed for real use. See [Security notes](#security-notes) before putting it on a public server.

## Features

- **Public site**
  - Hierarchical, database-driven menu with clean URLs (`/sortiment/nahradni-dily.html`)
  - Editable pages with per-page SEO title, keywords and description
  - Photo galleries with a Shadowbox lightbox and generated thumbnails
  - Contact page with opening hours and an embedded [Mapy.com](https://developer.mapy.com/) map
  - Sitemap page and styled 404 pages
- **Admin (`/admin`)**
  - Sign-in with bcrypt-hashed passwords, "remember me" and inactivity logout
  - Menu editor: add, edit, reorder, activate and delete items; internal pages or external links; nested to any depth
  - Page editor with TinyMCE
  - Gallery manager: create galleries, upload images, edit captions, reorder, activate, delete
  - File manager for images and documents, connected to TinyMCE's image and link pickers
  - AJAX-updated snippets ([Naja](https://naja.js.org/))
- **Not carried over from the original:** comments, guestbook and news.

## Tech stack

| | |
|---|---|
| Language | PHP 8.4 (requires >= 8.2) |
| Framework | Nette 3.3: Application, Forms, Database, DI, Security, Assets |
| Templates | Latte 3 |
| Database | MySQL 8.4 (Nette Database Explorer) |
| Front-end | jQuery, Shadowbox, TinyMCE 3, Naja, served from `www/assets` |
| Development | Docker Compose, VS Code Dev Containers, Xdebug |
| Quality | PHPStan level 8, PHPUnit 13 |

## Getting started

The supported way to develop is **Docker Compose with a VS Code Dev Container** (developed on WSL2 Ubuntu). Everything below runs inside the `web` container as the `dev` user.

### Prerequisites

- Docker with Compose v2
- Visual Studio Code with the *Dev Containers* extension (optional, you can use `docker compose` directly)

### First run

```bash
git clone git@github.com:vojtech-krupicka/web-cyklo-shop.git
cd web-cyklo-shop

cp .env.example .env
cp config/local.example.neon config/local.dev.neon
```

`.env` is the single source of truth for the database settings. `docker-compose.yml` uses it to create the MySQL database and user, and `config/local.dev.neon` (from the example) reads the same values as `%env.MYSQL_USER%` and so on. You only need to edit `config/local.dev.neon` if you want the contact-page map (see [Configuration](#configuration)). `.env` itself is git-ignored; change the copy of `.env.example` as you like, before the first `docker compose up`.

Open the folder in VS Code and run **Dev Containers: Reopen in Container**. Without VS Code:

```bash
docker compose up -d
docker compose exec -u dev web bash
```

Then, inside the container:

```bash
composer install
bin/db-migrate
```

The site is at <http://localhost:4203>, the admin at <http://localhost:4203/admin>.

### Development accounts

The migrations load mock content and two **test-only** members. Both use the password `aaa`:

| Username | Role |
|---|---|
| `test-admin` | admin |
| `test-editor` | editor |

Never use these anywhere public.

## Configuration

Configuration is layered; later files override earlier ones.

| File | In git | Purpose |
|---|---|---|
| `config/common.neon` | yes | Framework setup and non-secret defaults (shop info, opening hours, SEO defaults) |
| `config/services.neon` | yes | DI services |
| `config/local.<env>.neon` | see below | Environment-specific values: database credentials, API keys, paths |

`Bootstrap` loads `config/local.$APP_ENV.neon` and **stops with a clear error if it is missing**.

| File | In git | Used when |
|---|---|---|
| `local.example.neon` | yes | Template, not loaded |
| `local.dev.neon` | no | `APP_ENV=dev` (the dev container) |
| `local.test.neon` | yes | `APP_ENV=test` (PHPUnit); throwaway database, nothing secret |
| `local.prod.neon` | no | `APP_ENV=prod` (default when the variable is unset) |

### Environment variables

| Variable | Meaning |
|---|---|
| `APP_ENV` | Which local config to load: `dev`, `test` or `prod`. Defaults to `prod`, so a server with no variables set is safe. |
| `NETTE_DEBUG` | `1` turns on debug mode (Tracy, container auto-rebuild). Unset or anything else means production mode. Independent of `APP_ENV`. |

`docker-compose.yml` sets `APP_ENV=dev` and `NETTE_DEBUG=1` for the dev container. A production environment sets neither.

Every environment variable is also available in the NEON files as `%env.NAME%` (for example `%env.MYSQL_PASSWORD%`). The development file uses that for the database settings, so `.env` is the only place for them. `local.prod.neon` should hold literal values instead, because shared hosting usually cannot set environment variables. The values are read at runtime and are not stored in the cached container.

### Map API key

The old Mapy.cz JavaScript SDK was shut down, so the contact page uses Leaflet with Mapy.com map tiles. Get a free key at <https://developer.mapy.com/rest-api-mapy-cz/api-key/> and put it in your local file:

```neon
parameters:
	mapy:
		apiKey: 'your-key'
```

Restrict the key to your domain in the Mapy.com console, because it is visible in the page source.

## Database

Migrations are plain SQL files in `db/migrations/`, applied in filename order and recorded by name in a `schema_migrations` table.

| Command | What it does |
|---|---|
| `bin/db-migrate` | Apply pending migrations to the development database |
| `bin/db-dump [file]` | Dump the database to a gzip-compressed SQL file (default in `db/dumps/`, which is git-ignored) |
| `bin/db-restore <file>` | Restore a `.sql` or `.sql.gz` dump |
| `composer run test:db` | Drop and recreate the **test** database from the migrations |

| Migration | Content |
|---|---|
| `0001_create_schema.sql` | Tables, indexes and foreign keys (`utf8mb4`, Czech collation) |
| `0002_populate_tables.sql` | Mock pages, menu tree and galleries |
| `0003_populate_members.sql` | The two test members |

Migrations run once. To change the schema, add a new numbered file; do not edit one that has been applied.

The MySQL container creates two databases: `app` (development) and `app_test` (tests), through `docker/db/init/`. That script only runs when the data volume is first created. On an existing volume, run the two statements in it once as the MySQL root user.

## Testing and code quality

All commands run inside the dev container. VS Code tasks with the same names are in `.vscode/tasks.json`.

| Command | What it does |
|---|---|
| `composer run test` | Run the PHPUnit suite |
| `composer run phpstan` | Static analysis, level 8, on `app/` and `tests/` |
| `composer run psr` | Check that every class matches its file path (PSR-4), with no duplicates |
| `composer run check` | `psr` and `phpstan` |
| `composer run test:db` | Rebuild the test database |

### How the tests work

- **Unit tests**: the router (`RouterFactory`) and the authenticator, with a mocked member facade.
- **Facade tests** run against the real `app_test` database. Each test runs in a transaction that is rolled back, so tests can write freely and the mock data from the migrations is always intact.
- **Presenter tests** run presenters from the real DI container:
  - every public and admin page is rendered completely (layout, menus, footer), which catches broken templates;
  - forms are submitted as a browser would, as POST requests with the signal parameter;
  - signal handlers (activate, delete, move) are called as GET requests;
  - uploads use real temporary files.
- **Safety**: `phpunit.xml` forces `APP_ENV=test`. The base test case refuses to run when the connected database name does not end in `_test`, and file uploads go to a sandbox folder in `temp/`, so tests never touch your development data or `www/resources`.
- Many assertions depend on the mock data from `0002_populate_tables.sql`. If you change that file, run `composer run test:db` and expect to adjust the tests.

PHPUnit runs with Xdebug switched off (`-d xdebug.mode=off` in the composer script); the dev container otherwise starts a debugger connection attempt for every PHP process.

### Debugging

Xdebug listens on port 9003 inside the container. Use the **Listen for Xdebug** launch configuration in VS Code and set breakpoints.

## Project layout

```
app/
  Bootstrap.php            Boots Nette: debug mode, config loading
  Components/              Reusable UI controls (menus, breadcrumbs) with their templates
  Core/                    Router, authenticator, identity
  Model/                   Facades and entities, one folder per feature
    Gallery/ Member/ MenuItem/ Page/ Settings/
  Presentation/
    FrontModule/           Public site (presenters, templates, @layout.latte)
    AdminModule/           Admin system
    Error/                 Error4xx (dispatches to the front or admin error page) and Error5xx
config/                    common.neon, services.neon, local.*.neon
db/
  migrations/              SQL migrations
  dumps/                   Local dumps (git-ignored)
bin/                       db-migrate, db-dump, db-restore, db-test-reset
docker/                    PHP/Apache image, MySQL init scripts
tests/                     PHPUnit tests (Core, Model, Presentation)
www/                       Public document root
  assets/                  CSS, JavaScript, images
  resources/               Uploads (git-ignored)
log/ temp/ var/mail/       Runtime directories (contents git-ignored)
```

Namespaces follow the folders (`App\Model\Gallery\GalleryFacade` is `app/Model/Gallery/GalleryFacade.php`), and `composer run psr` enforces it.

## Deployment

Only `www/` may be web-accessible. `app/`, `config/`, `log/`, `temp/` and `vendor/` must stay outside the document root. See the [Nette security warning](https://nette.org/security-warning).

**Docker or Kubernetes**: build the image from `docker/php`, mount or bake in `config/local.prod.neon` (or mount it as a Secret), and leave `APP_ENV` and `NETTE_DEBUG` unset.

**Shared hosting (FTP)**:

1. Run `composer install --no-dev --optimize-autoloader` locally and upload `vendor/` too.
2. Upload `app/`, `config/` and `www/`, plus your own `config/local.prod.neon`. Do not upload `local.dev.neon`.
3. Keep `app/`, `config/`, `vendor/`, `log/` and `temp/` above the public folder, or deny them in `.htaccess`.
4. Make `temp/`, `log/` and `www/resources/` (with `images`, `files` and `galleries`) writable by the web server.
5. Apply the schema (`db/migrations/0001_create_schema.sql`) with your host's database tool. Skip `0002` and `0003`; they contain mock data and test accounts. Create a real member with a password hashed by `Nette\Security\Passwords`.
6. Clear `temp/cache` after every deployment.

Before the first public deployment: test the real 404 and 500 pages with debug off, and check that errors end up in `log/`, not on screen.

## Security notes

- **Passwords** are hashed with `Nette\Security\Passwords` (bcrypt). The development accounts use a known password and must not exist in production.
- **Forms** use Nette's same-origin check against CSRF. Admin actions triggered by links (activate, delete, move, log out) are plain GET signals, so they rely on the admin being logged in and are not protected by tokens. Treat that as a known weakness.
- **Uploads** are written below `www/resources`, which is publicly readable. Image uploads are checked by content type, other files are accepted as they are.
- Only the admin can reach the upload and file-manager code, and deleting a file refuses paths that leave the upload folder (covered by a test).
- The map key is public by design; restrict it by domain.
- The TinyMCE 3 and jQuery copies in `www/assets/javascript` are old and unmaintained. Update or replace them before using this for anything real.

## License

The code of this project is released under the [MIT License](LICENSE).

Third-party files in `www/assets/javascript` keep their own licenses and are not covered by it. Check each one before reusing it, especially for commercial use:

| Component | License |
|---|---|
| jQuery and its plugins (livequery, Nette ajax helpers) | MIT (jQuery and livequery are dual-licensed MIT or GPL v2) |
| TinyMCE 3 | LGPL 2.1 (`www/assets/javascript/tiny_mce/license.txt`) |
| Shadowbox 3.0.3 | Its own license, which as far as I know restricts commercial use. The file header does not say, so check <http://shadowbox-js.com/> |
| Nette, Latte, Tracy and other Composer packages | See each package in `vendor/` |

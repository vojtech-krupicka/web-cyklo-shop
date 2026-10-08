# To My Agents!

It is my fervent wish that this file guide every AI coding agent working with code in this repository.

This is a small **Nette 3.3 web application** (a CMS and website for a bike shop), rewritten from
Nette 2.0.4 as a learning project. The public site is `FrontModule`, the admin system is
`AdminModule`. `README.md` explains setup, configuration and deployment for humans; this file is
what an agent needs to change code safely.

## First: equip your agent

Deep, up-to-date knowledge of the Nette ecosystem (conventions, APIs, gotchas) and automatic
validation of the files you write ship as a set of **skills**, not baked into this file.

**Install them before writing any real code.** If the developer has not done so yet, ask them
to run the steps below first.

### Claude Code

Add the Nette marketplace (with auto-update), then install the plugins:

```
/plugin marketplace add nette/claude-code
/plugin install nette@nette
```

Strongly recommended. Validates PHP, Latte, NEON, JSON and JS after every edit and reports
errors straight back to the agent:

```
/plugin install nette-lint@nette
```

Optional, for automatic PHP code-style fixing:

```
/plugin install php-fixer@nette
/install-php-fixer
```

Details and the full skill list: https://github.com/nette/claude-code

### Other agents

The plugins above are Claude Code specific. For any other agent, the authoritative source is the
official manual at https://doc.nette.org. Consult it for the area you are working on
(architecture, forms, database, Latte, NEON, Tracy) before editing, rather than relying on this
file. This file is a signpost, not the manual.

## Project at a glance

- **Backend:** PHP 8.4 (requires 8.2+), Nette 3.3, Latte 3
- **Database:** MySQL 8.4 through Nette Database Explorer. Schema and mock data are SQL files in
  `db/migrations/`.
- **Frontend:** jQuery, Shadowbox, TinyMCE 3 and Naja served from `www/assets` through Nette Assets.
  `package.json` and `vite.config.ts` come from the skeleton and are **not used**.
- **Testing:** PHPUnit 13 (`tests/**/*Test.php`)
- **Static analysis:** PHPStan level 8 on `app` and `tests`
- **Language:** the UI and all user-facing messages are in **Czech**; code and identifiers are English.
- **Environment:** Docker Compose and a VS Code Dev Container. PHP and MySQL are **not** installed
  on the host.

```
app/
├── Bootstrap.php              # Debug mode, config loading (config/local.$APP_ENV.neon)
├── Components/                # Reusable controls (menus, breadcrumb) with their templates
├── Core/                      # RouterFactory, AdminAuthenticator, AdminIdentity
├── Model/                     # Facades + entities, one folder per feature
│   ├── BaseFacade.php  BaseEntity.php
│   └── Gallery/ Member/ MenuItem/ Page/ Settings/
└── Presentation/
    ├── BasePresenter.php
    ├── FrontModule/           # Homepage, Cms, Contact, Gallery, Sitemap
    ├── AdminModule/           # Default, Cms, Gallery, File, SignIn
    └── Error/                 # Error4xx dispatches to the front or admin error page; Error5xx
config/
├── common.neon                # framework configuration, non-secret defaults
├── services.neon              # DI services + auto-discovery (*Facade, *Factory, …)
└── local.<dev|test|prod>.neon # per-environment values; dev and prod are git-ignored
db/migrations/                 # numbered SQL migrations
tests/                         # Core/, Model/, Presentation/ + base classes
www/index.php                  # HTTP entry point
```

## Essential commands

Run these **inside the dev container, as the `dev` user**:

```bash
docker compose exec -u dev web bash      # from the host, if you are not in the Dev Container

composer run test        # PHPUnit
composer run phpstan     # static analysis, level 8
composer run psr         # every class matches its file path, no duplicate classes
composer run check       # psr + phpstan
composer run test:db     # rebuild the test database (app_test) from the migrations

bin/db-migrate           # apply pending migrations to the development database
```

Before you call a change done, `composer run check` and `composer run test` must both pass.

Do not run commands as root (`docker compose exec` without `-u dev`): the files and folders they
create (for example `/tmp/Tester`, `temp/`, `log/`) become unwritable for the `dev` user.

## Conventions

**Autoloading**
- One class per file, the file name equals the class name, and the namespace equals the path below
  `app/` (`App\Model\Gallery\GalleryFacade` is `app/Model/Gallery/GalleryFacade.php`). Form data
  classes (`*FormData`) and item objects get their own files too. Nette's DI scan fails for a class
  that autoloading cannot find. There is no RobotLoader.

**Model**
- Facades extend `BaseFacade` and are **not `final`**, so tests can mock them. Entities are `final`,
  extend `BaseEntity` and implement `fromActiveRow(): static`.
- `BaseEntity::fromSelection()` returns a **list** (`list<static>`), not an array keyed by id. Never
  use array keys as database ids.
- Entity ids are `?int` (null until saved). Check for `null` before using a looked-up entity.

**Presenters and templates**
- Concrete presenters use constructor injection. Shared dependencies of a module live in its base
  presenter as `#[Inject]` properties.
- State set in an `action*()` method is stored in a non-nullable, uninitialized property. The action
  redirects when the record is missing; `redirect()` never returns.
- Create forms with `DefaultFormRenderer` and `wrappers['controls']['container'] = 'table class="form"'`,
  and map submitted values to a `*FormData` class.
- Links from controls and components to presenters use `{plink}`, not `n:href`.
- Signals are plain GET links (`handleXxx`). Anything destructive must check that the record exists.

**Configuration**
- No secrets in `common.neon`. Environment-specific values go in `config/local.<env>.neon`.
- `APP_ENV` selects the file (`dev`, `test`, `prod`; default `prod`). `NETTE_DEBUG=1` turns on debug
  mode. They are independent; never derive one from the other.
- Environment variables are available in NEON as `%env.NAME%`. In development `.env` is the single
  source of truth for the database settings (`local.dev.neon` reads them from there); `.env` is
  git-ignored, `.env.example` is its template. `local.prod.neon` uses literal values because
  shared hosting cannot set environment variables.

**Database**
- Change the schema or the mock data with a **new** numbered migration. Never edit an applied one.
  Do not let an editor auto-format the SQL files in `db/migrations/`.
- Tests use the separate `app_test` database. Never point tests at the development database.

**Code style**
- PHP: 4 spaces. NEON: tabs. Both are set in `.editorconfig`.
- Tracy must stay off in production.

## Tests

- Any behaviour change needs a test. Run `composer run test` and make sure a new test **fails** when
  the code under test is broken.
- Base classes: `Tests\DatabaseTestCase` wraps each test in a rolled-back transaction and refuses to
  run when the database name does not end in `_test`. `Tests\PresenterTestCase` adds
  `runPresenter()`, `renderPage()`, `submitForm()`, `signal()`, `loginAsAdmin()`, `upload()` and
  `uploadImage()`.
- Tests that need files use the sandbox returned by `resourcesDir()` (`temp/test-resources`), never
  `www/resources`.
- Forms only accept same-origin submissions; `tests/bootstrap.php` sets `Sec-Fetch-Site: same-origin`
  for that reason.
- Many assertions depend on the mock data in `0002_populate_tables.sql`.
- PHPUnit 13 specifics: use `expectExceptionMessageIs()` (the plain `expectExceptionMessage()` is
  deprecated), and do not call `with()` on a mock method without `expects()`.

## Ground rules

- **Directories `app/`, `config/`, `log/`, `temp/` must never be web-accessible.**
  Only `www/` is the document root. See https://nette.org/security-warning.
- Add Nette packages as you need them: `composer require nette/<package>`
  (catalog at https://nette.org/packages).
- This project pins the installed versions: for example `Route::ONE_WAY` (not `OneWay`) in
  `nette/routing`. Check `vendor/` before trusting a snippet from the web.

When in doubt about a Nette convention, defer to the installed skills. They are the source of
truth, and they are kept current in a way this file is not.

# Changelog

All notable changes to this project are documented in this file.

The format follows [Keep a Changelog](https://keepachangelog.com/en/1.1.0/), and the project uses
[Semantic Versioning](https://semver.org/).

## [Unreleased]

## [1.0.0] - 2026-10-08

First release of the rewrite from Nette 2.0.4 (PHP 5.3) to Nette 3.3 (PHP 8.4). It replaces the
2012 application completely; the old version remains in the git history.

### Added

- Docker Compose development environment with a VS Code Dev Container: PHP 8.4 with Apache,
  MySQL 8.4, Xdebug, the MySQL client, and a separate `app_test` database.
- SQL migrations in `db/migrations` (schema, mock content, two test members) and the scripts
  `db/migrate`, `db/dump`, `db/restore` and `db/test-reset`.
- Layered configuration: `config/common.neon`, `config/services.neon` and one
  `config/local.<env>.neon` per environment, selected by `APP_ENV`. Environment variables are
  available in NEON as `%env.NAME%`; `NETTE_DEBUG` switches debug mode independently.
- Static analysis with PHPStan level 8 on `app` and `tests`.
- PHPUnit test suite (143 tests): router, authenticator, all facades, every public and admin page
  rendered completely, every admin form and signal handler, file uploads, and logout.
- Composer scripts `test`, `test:db`, `phpstan`, `psr` and `check`, and matching VS Code tasks.
- Contact page map using Leaflet with Mapy.com tiles (needs an API key in the local config).
- Example configuration (`config/deployment.example.ini`) and a README guide for deploying to FTP
  hosting with dg/ftp-deployment.
- README, `AGENTS.md` (instructions for AI coding agents), and an MIT `LICENSE`.

### Changed

- Framework and platform: Nette 3.3, Latte 3, PSR-4 autoloading with one class per file,
  dependency injection through constructors and `#[Inject]`, Nette Database Explorer, and Nette
  Assets for static files.
- Code structure: presenters under `app/Presentation/{FrontModule,AdminModule}`, models split into
  facades and entities per feature under `app/Model`, reusable controls (menus, breadcrumb) in
  `app/Components`.
- Error pages are chosen per module (public site or admin) and translated to Czech.
- AJAX is done only by Naja. The jQuery AJAX plugins (`jquery.nette.js`, `jquery.ajaxform.js`,
  `jquery.livequery.js`) are gone.
- Passwords are hashed with `Nette\Security\Passwords` (bcrypt) instead of the old custom
  username-salted SHA-512. **Breaking:** members of the old database cannot sign in until their
  passwords are reset; no data migration is provided.
- The database schema uses `utf8mb4` with the Czech collation and declares its foreign keys.

### Removed

- Comments, guestbook, news and the generated `sitemap.xml`. A few remnants remain: the
  "allow comments" flags in the forms and schema, and some dashboard help text.
- The bundled framework copy, dibi, and all vendored PHP libraries; Composer manages dependencies.
- The old Mapy.cz JavaScript SDK (shut down by the provider).
- Skeleton leftovers: `latte-lint`, `vite.config.ts`, `package.json`, a fake sendmail, and the
  unused `nette/mail` and `symfony/thanks` packages.

### Fixed

Bugs found by the new tests while porting the code:

- Editing the URL of an external menu item was never saved.
- Moving menu items and gallery images up or down swapped the wrong neighbour.
- Inactive menu items could not be deleted or moved.
- Deleting a gallery image left a gap in the sort order and never removed the image files.
- The file manager page crashed when the upload folders did not exist.

### Security

- The file manager's delete action accepted paths containing `../`, which allowed deleting files
  outside the upload folder. File names are now reduced to a plain name.
- The file manager accepted any file type, including `.php`, into the publicly served upload
  folder, which allowed remote code execution by any signed-in member. It now accepts only images
  and an allowlist of document types, stores names without dots in the stem (so `shell.php.txt`
  cannot keep a `.php` segment), and `www/resources/.htaccess` denies script execution there as a
  second layer.
- Forms and signal links (activate, delete, move, log out) require a same-origin request, as
  enforced by Nette 3.3; there are no per-request tokens.

### Known limitations

- The shared-hosting deployment steps in the README have not been tried on a real host.
- Shadowbox, TinyMCE 3 and jQuery in `www/assets/javascript` are old third-party copies with their
  own licences.
- The snippet names redrawn by some admin handlers do not match the snippets in the templates, so
  AJAX updates in the gallery editor show only the flash message.

[Unreleased]: https://github.com/vojtech-krupicka/web-cyklo-shop/compare/v1.0.0...HEAD
[1.0.0]: https://github.com/vojtech-krupicka/web-cyklo-shop/releases/tag/v1.0.0

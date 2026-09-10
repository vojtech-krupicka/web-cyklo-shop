# Web application for Cyklo Shop

A small PHP content-management system and website built for a family-run bike shop.

## About

This was a website with a custom admin area, built around 2012 for my grandfather's bike shop. It was unpaid — the main reason I built it was to learn the [Nette Framework](https://nette.org/), and it later became the seedbed for my own small "Hassa Framework" layer that sits on top of Nette in this codebase.

The public site (`FrontModule`) shows editable pages, photo galleries, a guestbook and a contact page. The admin system (`AdminModule`) lets a logged-in user manage the menu tree, page content (via a TinyMCE editor), galleries and comments, and upload files.

The UI and all messages are in Czech. The shop's real name, address, phone numbers, other contact details and the logo images have been replaced with placeholders (`Cyklo shop`, `info@example.com`, `+420 XXX XXX XXX`, …) throughout the templates and assets.

## Status

**Archived.** Built in 2012, no longer maintained. It targets PHP 5.3–5.5 and a version of Nette that is a decade out of support. It is published here for reference / as a portfolio piece, not for reuse in production. See [Security notes](#security-notes) before running it anywhere public.

## Features

- **Front-end**
  - Hierarchical, database-driven navigation menu with clean URLs (`nejaka-stranka.html`)
  - Editable CMS pages with per-page SEO title / keywords / description
  - Photo galleries with a Shadowbox lightbox and auto-generated thumbnails
  - Threaded comments on pages and galleries, plus a standalone guestbook ("diskuze")
  - Contact page with an embedded Mapy.cz map
  - Generated `sitemap.xml`
- **Admin (`/admin`)**
  - Form-based login with role-based identity, "remember me", and inactivity logout
  - Menu editor: add / edit / reorder (up-down) / activate / delete items, internal or external links, nested to any depth
  - Page editor with a TinyMCE WYSIWYG editor
  - Gallery manager: create galleries, upload images, edit captions, reorder, activate, delete
  - Comment moderation: reply, edit, delete, on pages / galleries / guestbook
  - File manager for images and documents, wired into TinyMCE's image/link pickers
- AJAX-updated snippets throughout the admin UI (Nette snippets + jQuery)

## Tech stack

| | |
|---|---|
| Language | PHP 5.3–5.5 |
| Framework | Nette Framework 2.0.4 (bundled in `libs/`) |
| Database layer | Nette\Database + [dibi](https://dibiphp.com/) 2.0 (both bundled) |
| Templates | Latte |
| Database | MySQL (via `mysql` / `mysqli`) |
| Front-end | jQuery, Shadowbox, TinyMCE 3 |
| Server | Apache with `mod_rewrite` (an IIS `web.config` is also included) |

Third-party libraries are vendored directly into `libs/` and `www/javascript/` — there is no Composer setup.

## Repository layout

```
app/                      Application code
  bootstrap.php            Boots Nette, RobotLoader, DI container, routing
  presenters/              Shared base presenters
  FrontModule/             Public site (presenters + Latte templates)
  AdminModule/             Admin system (presenters + Latte templates)
  model/                   Table-gateway classes over Nette\Database
  components/              Reusable UI controls (CommentsControl)
  config/                  DI + parameters config — NOT in the repo, see below
libs/                     Vendored Nette Framework + dibi
www/                      Public document root
  index.php                Front controller
  css/ javascript/ images/ Static assets
log/ temp/                Writable runtime dirs (kept empty in git)
```

The original `www/` also contained a bundled copy of **Adminer 3.4.0** (a web database console) and the **Nette Checker** environment tool. Both are `.gitignore`d and not part of this repo — they are security-sensitive to ship and trivial to re-download if needed.

## Setup

> Historical instructions — expect to fight PHP-version and library-age issues on anything modern.

1. **Requirements:** PHP 5.3–5.5 with `mysqli`, `mbstring`, `gd`, and `mod_rewrite`.
2. **Document root:** point the vhost at `www/`. `app/`, `libs/`, `log/` and `temp/` must stay outside the web root (or keep the bundled `.htaccess` / `web.config` deny rules).
3. **Writable dirs:** make `temp/` and `log/` writable by the web server. The file/gallery uploaders also write into `www/resources/images`, `www/resources/files` and `www/resources/galleries/` — create those and make them writable.
4. **Database:** create a MySQL database and load the schema. **No schema dump is included in this repo** — the tables have to be created by hand (see [Database schema](#database-schema-inferred)).
5. **Create `app/config/config.neon`** — this file is not in the repo because it holds the database credentials. See [Configuration](#configuration-appconfigconfigneon) for the structure the code expects.
6. Create at least one row in `members` with a password hashed the way `Model\Authenticator` expects (see [Authentication](#authentication)).
7. Visit the site; the admin area is at `/admin`.

## Configuration (`app/config/config.neon`)

The original `app/config/config.neon` was never committed and I no longer have a copy. `app/bootstrap.php` loads it and the presenters read a number of services and parameters from the DI container. The file below is **reconstructed from how the code uses the container** — it is a starting point, not the verified original.

The application expects:

- **Parameters**
  - `wwwDir`, `resourcesDir` — absolute paths; `resourcesDir` is the base for `/images`, `/files` and `/galleries/gallery_<id>` upload folders
  - `defaults.seoTitle`, `defaults.seoKeywords`, `defaults.seoDescription` — fallback `<meta>` values for pages that don't set their own
- **Services** — one per model, named so the container exposes `createMenuItems()`, `createPages()`, `createGalleries()`, `createGalleryItems()`, `createComments()`, `createMembers()`, `createNews()`, plus an authenticator built from `Model\Members`
- A **database** connection service for `Nette\Database\Connection`

```neon
parameters:
    wwwDir: %wwwDir%
    resourcesDir: %wwwDir%/resources
    defaults:
        seoTitle: "Cyklo shop"
        seoKeywords: "cyklo shop, jízdní kola, prodej, servis"
        seoDescription: "Cyklo shop – prodej a servis jízdních kol."

php:
    date.timezone: Europe/Prague

nette:
    session:
        expiration: 14 days
    database:
        dsn: 'mysql:host=127.0.0.1;dbname=YOUR_DATABASE'
        user: YOUR_DB_USER
        password: YOUR_DB_PASSWORD
        options:
            lazy: yes

services:
    authenticator: Model\Authenticator
    menuItems:     Model\MenuItems
    pages:         Model\Pages
    galleries:     Model\Galleries
    galleryItems:  Model\GalleryItems
    comments:      Model\Comments
    members:       Model\Members
    news:          Model\News
```

`dibi` is also bundled and initialised by the framework layer; if your build wires it explicitly it needs the same MySQL credentials.

## Authentication

`Model\Authenticator` checks the submitted nickname/password against the `members` table (`active = 1` only). Passwords are stored as:

```
sha512( firstHalf(password) . nickname . secondHalf(password) )   // lowercased hex
```

i.e. the **username is used as the salt** and the password is split in half around it. There is no per-user random salt and no key stretching. This was weak even in 2012 and should not be copied — it is documented here only so the login can be made to work.

To create a hash for a new user, call `Model\Authenticator::saltPassword($nickname, $plaintextPassword)`.

## Database schema (inferred)

There is no migration or dump in the repo. These columns are **inferred from the queries in the models and presenters** and are almost certainly incomplete on types and constraints.

**`members`** — `id`, `nickname`, `password` (128-char sha512 hex), `role`, `active` (bool), `last_logon` (datetime)

**`menu_items`** — `id`, `name`, `title`, `url`, `url_rewrite_name`, `url_query`, `url_fragment`, `parent_id` (nullable → self), `page_id` (nullable → `pages.id`), `sort_order` (int), `active` (bool)

**`pages`** — `id`, `heading`, `content` (HTML), `seo_title`, `seo_keywords`, `seo_description`, `allow_comments` (bool), `is_homepage` (bool), `created` (datetime), `modified` (datetime)

**`galleries`** — `id`, `name`, `description`, `allow_comments` (bool), `active` (bool), `added` (datetime)

**`gallery_items`** — `id`, `gallery_id` (→ `galleries.id`), `title`, `description`, `file_name`, `sort_order` (int), `active` (bool), `added` (datetime)

**`comments`** — `id`, `parent_id` (nullable, threaded), `page_id` (nullable), `gallery_id` (nullable), `news_id` (nullable), `nickname`, `email`, `subject`, `comment`, `added` (datetime). A row belongs to exactly one of page / gallery / news; all three null = guestbook.

**`news`** — a `Model\News` gateway over a `news` table exists but no presenter renders it; treat it as unfinished.

## Security notes

This code reflects 2012 practices. If you put it on a public server:

- **Bundled framework is unpatched.** Nette 2.0.4 and dibi 2.0 in `libs/` have had many security fixes since. Do not run this exposed to the internet as-is.
- **Password hashing** is the weak custom scheme described above.
- **If you re-add Adminer or the Nette Checker** to `www/` for local work, do not deploy them to a public server — put them behind HTTP auth / an IP allowlist, or delete them after use.
- No CSRF/XSS review has been done against current standards; the file and gallery uploaders write user-named files under the web root.

## License

No license. All rights reserved. This is published for reference; if you want to reuse any of it, get in touch.

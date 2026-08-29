# InitiumPHP Skeleton

Boilerplate app for [InitiumPHP](https://github.com/timbotron/initium-php-core) —
a small, dependency-light PHP starter with turnkey user authentication. Start a
new project from this, fill in config, and you have a working auth-enabled app.

The framework itself lives in the `timbotron/initium-php-core` Composer package;
this repo is just the app shell that requires it.

- **App namespace:** `App\` (PSR-4 → `src/`)
- **Web root:** `public/` — point your server there. Everything above it
  (`config/`, `routes/`, `src/`, `templates/`, `storage/`, `vendor/`) is not
  web-exposed.

## Layout

```
public/index.php     thin front controller: autoload → _env → Kernel → routes → run()
public/css/          default stylesheets (copied from core; edit freely)
config/_env.php       your config constants (gitignored; copy from the template)
routes/web.php        app routes; core auth routes are mounted in index.php
src/Controllers/      app pages (Home ships as a starter)
templates/            override any core template by dropping a same-named file here
storage/sessions/     private session store, above the web root
```

## Getting started

```bash
composer create-project timbotron/initium-php-skeleton myapp
cd myapp
# post-create copies config/_env.php.template → config/_env.php; fill in real values
# import the schema (shipped by core — users + login_attempts):
for f in vendor/timbotron/initium-php-core/migrations/*.sql; do mysql -u <user> -p <db> < "$f"; done
php -S localhost:8000 -t public
```

Framework fixes arrive with `composer update`.

## Config

`config/_env.php` holds all config as `define()` constants (see
`config/_env.php.template` for the required set). Core validates the required
constants at boot via `\Initium\Config::validate()` and fails fast if any are
missing. `_env.php` is gitignored and never committed.

## Adding your own routes, pages, and templates

- **Routes** — add them in `routes/web.php`; the callback receives a
  `FastRoute\RouteCollector`. Core's auth routes are mounted separately in
  `public/index.php`, so your file only holds app routes.

  ```php
  $r->get('/about', [\App\Controllers\Home::class, 'about_page']);
  ```

- **Pages / models** — drop classes under `src/` in the `App\` namespace (PSR-4).
  Extend `Initium\Base` for `$this->db`, the message queue, and helpers; use
  `Initium\Auth\Cred::userDetails()` to check login state. `App\Controllers\Home`
  is a working starter — copy its shape.

- **Templates** — anything in `templates/` overrides the core default of the same
  name. Render app views as `app::name` and lay them out with
  `<?php $this->layout('app::basic'); ?>`. To restyle the whole site, drop your own
  `templates/basic.php`; core's is the fallback.

## Docker harness

A Caddy + php-fpm + MySQL stack for local testing lives in `docker-compose.yml`
(web root `public/`, port 8080):

```bash
cp config/_env.php.template config/_env.php   # set DB_SERVER=db, DB_NAME/USER/PASS=initium
docker compose run --rm composer              # populate vendor/
docker compose up -d                          # http://localhost:8080
```

## Coming from the old monolith

InitiumPHP used to be a single repo you cloned per project. To move an existing
project over, start a fresh `composer create-project` app and carry across your
config values (`config/_env.php`), app pages/models (`src/`, `App\`), route
declarations (`routes/web.php`), and any customized templates (`templates/`). The
framework is no longer copied into your repo — it's `timbotron/initium-php-core`,
updated with `composer update`. Note the old monolith's `michelf/php-markdown` and
`verot/class.upload.php` are not part of core; add them to `require` here if you
need them.

## Local development against a working copy of core

Until core is published to Packagist (CODE-103), this repo resolves it through a
Composer **path repository** pointing at a sibling `../initium-php-core` checkout
(`repositories` block in `composer.json`, `symlink: false` so it is copied into
`vendor/` — which keeps the docker mounts working). At publish time that block is
removed and `require` resolves `timbotron/initium-php-core` straight from
Packagist with no other change.

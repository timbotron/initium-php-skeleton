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
# import the users table (shipped by core):
mysql -u <user> -p <db> < vendor/timbotron/initium-php-core/migrations/001-migration-start.sql
php -S localhost:8000 -t public
```

Framework fixes arrive with `composer update`.

## Config

`config/_env.php` holds all config as `define()` constants (see
`config/_env.php.template` for the required set). Core validates the required
constants at boot via `\Initium\Config::validate()` and fails fast if any are
missing. `_env.php` is gitignored and never committed.

## Docker harness

A Caddy + php-fpm + MySQL stack for local testing lives in `docker-compose.yml`
(web root `public/`, port 8080):

```bash
cp config/_env.php.template config/_env.php   # set DB_SERVER=db, DB_NAME/USER/PASS=initium
docker compose run --rm composer              # populate vendor/
docker compose up -d                          # http://localhost:8080
```

## Local development against a working copy of core

Until core is published to Packagist (CODE-103), this repo resolves it through a
Composer **path repository** pointing at a sibling `../initium-php-core` checkout
(`repositories` block in `composer.json`, `symlink: false` so it is copied into
`vendor/` — which keeps the docker mounts working). At publish time that block is
removed and `require` resolves `timbotron/initium-php-core` straight from
Packagist with no other change.

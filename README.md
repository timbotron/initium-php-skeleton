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

You'll need PHP ≥ 8.0 (with `pdo_mysql` and `curl`), Composer, and a MySQL
database you can connect to.

```bash
composer create-project timbotron/initium-php-skeleton myapp
cd myapp
# post-create copies config/_env.php.template → config/_env.php; now fill in real
# values (DB connection, SITE_URL, Mailgun — see Config below)
# import the schema into your database (shipped by core — users + login_attempts):
for f in vendor/timbotron/initium-php-core/migrations/*.sql; do mysql -u <user> -p <db> < "$f"; done
php -S localhost:8000 -t public
```

Framework fixes arrive with `composer update`. If you just want a local stack
without installing PHP/MySQL yourself, skip ahead to the Docker harness.

## Config

`config/_env.php` holds all config as `define()` constants (see
`config/_env.php.template` for the required set). Core validates the required
constants at boot via `\Initium\Config::validate()` and fails fast if any are
missing. `_env.php` is gitignored and never committed.

## Authentication & email

Accounts are activated by email, not by a password field at signup. Creating an
account (or requesting a password reset) inserts/updates the user and emails a
**set-password link** via Mailgun; the user stays inactive until they follow it
and set a password. So valid `EMAIL_MAILGUN_*` values are required for the normal
flow to work end-to-end.

For **local testing without Mailgun**, no email will arrive — grab the link
target yourself. After submitting the create-account or forgot-password form,
read the UUID from the database and visit the reset URL directly:

```sql
SELECT email, password_reset FROM users WHERE password_reset <> '';
```

Then open `<SITE_URL>password-reset/<password_reset>` to set a password and
activate the account. `ALLOW_SIGNUPS=0` disables the create-account route
entirely (it 404s); with signups off, seed your first user by inserting a row and
using the same reset-link trick.

For a real **no-email install** (not just local testing), set `NO_EMAIL_SIGNUP`
truthy in `config/_env.php`. That unlocks the admin **require valid email** toggle
(below); with it off, new users skip Mailgun entirely and are shown their
set-password link on screen. This path is **enumerable by design** — showing that
link reveals whether an account already existed — so enable it only on
trusted/internal installs. Without the constant the toggle stays locked on and
email verification is always required.

## Admin area

Core ships a small admin settings area at `/admin` (mounted in
`public/index.php`). It has two toggles — **allow new sign-ups** and **require
valid email** — stored in the `settings` table and editable at runtime. Turning
"require valid email" off skips the Mailgun set-password email and sends new
users straight to the set-password page, which is handy for installs without
Mailgun. That toggle is **locked on** unless the install sets the
`NO_EMAIL_SIGNUP` constant, because the no-email path is enumerable by design
(see Authentication & email above).

Access is admin-only: a logged-in user reaches it when their `users.is_admin`
flag is `1`, or when their email matches the optional `ADMIN_EMAIL` constant in
`config/_env.php` (the easiest way to make yourself the first admin). Everyone
else gets a 404.

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

To hack on the framework and app together, resolve core from a sibling checkout
instead of Packagist. Add a path repository to `composer.json` pointing at your
local `../initium-php-core` (use `"symlink": false` so it's copied into `vendor/`,
which keeps the Docker mounts working), then `composer update
timbotron/initium-php-core`. Remove the `repositories` block to go back to the
published package — the `require` line is unchanged either way.

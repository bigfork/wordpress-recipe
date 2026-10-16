# WordPress Recipe

Bigfork’s quickstart recipe for simple WordPress projects: [Bedrock](https://roots.io/bedrock/)-style structure, a [Sage](https://roots.io/sage/) theme, DDEV config and our usual plugins, mu-plugins and settings.

## Requirements

- [Composer](https://getcomposer.org) 2
- [DDEV](https://ddev.com)
- An ACF Pro licence key (Composer installs ACF Pro from `connect.advancedcustomfields.com`)

The project runs on PHP 8.4 in DDEV. Composer is pinned to resolve for 8.4 (`config.platform.php`) whatever PHP your host runs, but prefer `ddev composer …` once the project exists.

## Starting a project

### 1. Create the project

```bash
composer create-project bigfork/wordpress-recipe ./acme-site dev-master
```

Name the directory after the project — it becomes the project slug (eg. `Acme Site` → `acme-site`).

If Composer asks for credentials for `connect.advancedcustomfields.com`, use the ACF Pro licence key as the username and the site URL as the password.

### 2. Answer the installer prompt

Once dependencies are installed, the installer asks whether DDEV should start. Either way it will:

- set the DDEV project name to the slug, so the site lives at `https://acme-site.ddev.site`
- rename the theme from `public/app/themes/sage` to `public/app/themes/acme-site`, updating its Vite base path, the `ddev theme` command, the package name, the theme name and `WP_DEFAULT_THEME` so a fresh install activates it automatically (the `sage` text domain is left alone)
- copy `.env.example` to `.env` and replace each `generateme` placeholder with a random 64-character salt
- delete this README

Answer **yes** and it also runs `ddev launch` and `ddev theme install` (the theme’s Yarn and Composer dependencies). Answer **no** and run them yourself:

```bash
ddev start
```

```bash
ddev theme install
```

### 3. Configure `.env`

The defaults already point at the DDEV database and hostname, and the salts are already generated. Before going any further:

- fill in `ACF_PRO_LICENSE`, `WP_ROCKET_KEY` and `WP_ROCKET_EMAIL`
- set `LOGIN_SLUG` if the login should move away from `wp-login.php`

The Redis / Object Cache Pro settings can stay commented out locally.

### 4. Install WordPress

Visit `https://acme-site.ddev.site` and run through the WordPress installer.

### 5. Start building

```bash
ddev theme watch
```

## Commands

| Command | What it does |
| --- | --- |
| `ddev theme install` | Installs the theme’s Yarn and Composer dependencies |
| `ddev theme watch` | Runs the Vite dev server |
| `ddev theme build` | Builds production assets |
| `ddev theme <args>` | Runs any other Yarn command in the theme directory |
| `ddev wp <args>` | Runs WP-CLI in the web container |
| `ddev composer <args>` | Runs Composer in the web container |

## What’s included

- **Plugins:** ACF Pro, ACF Extended, ACF Image Aspect Ratio Crop, Disable Gutenberg, Duplicate Post, Simple Page Ordering, Slim SEO, Wordfence, WP Rocket, Bigfork HTML Sitemap
- **Dev only:** Query Monitor, Symfony VarDumper, PHP_CodeSniffer
- **mu-plugins:** ACF field group sync via WP-CLI, auto-updates (with plugin installs locked down for CMS users), 404 caching, hidden login URL, REST API restrictions, youtube-nocookie embeds, indexing disallowed outside production
- **Theme security** (`app/security.php`): XML-RPC and Application Passwords off, generic login and lost password errors
- **`.htaccess` hardening:** XML-RPC and scripts in uploads are blocked, plus `X-Frame-Options`, `frame-ancestors`, `Referrer-Policy`, `Permissions-Policy` and HSTS headers. HSTS has no `includeSubDomains` or `preload` by default — add them per project only once every subdomain is on HTTPS. Point the host’s document root at `public/`; the root `.htaccess` is a fallback that still denies env, Composer and config files
- **Deploy helpers** in `util/`: WP Rocket cache generation and an OPcache clear script

## Working on the recipe

There is deliberately no committed `composer.lock`, so new projects get the latest versions. If you bump the PHP platform pin in `composer.json`, bump `php_version` in `.ddev/config.yaml` (and the theme’s `composer.json`) with it.

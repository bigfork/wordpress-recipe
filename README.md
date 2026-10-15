# WordPress Recipe

Bigfork’s quickstart recipe for simple WordPress projects. Contains frequently used modules, templates, config settings, JavaScript and CSS.

## Project setup

Requires PHP 8.4. Composer is pinned to resolve for 8.4 (`config.platform.php`), matching DDEV, whatever PHP the host runs.

- Run `composer create-project bigfork/wordpress-recipe ./project dev-master`
  - The installer slugs the project directory name (eg. `Acme Site` → `acme-site`) and uses it for the DDEV project name and the theme folder (`public/app/themes/sage` → `public/app/themes/acme-site`, with its Vite base path, `ddev theme` command, package name, theme name and `WP_DEFAULT_THEME` to match, so a fresh WordPress install activates it automatically). The `sage` text domain is left alone.
- Run `ddev start`
- Tick the "I am not affiliated with WP Engine in any way, financially or otherwise." box

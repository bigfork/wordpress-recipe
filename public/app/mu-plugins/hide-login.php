<?php

/**
 * Plugin Name: Hide Login
 * Description: Serves the login form at a custom, non-guessable URL instead of wp-login.php, and 404s direct access to wp-login.php/wp-admin for anyone not already logged in. Configure the slug via the LOGIN_SLUG environment variable - the plugin stays fully inactive (no behaviour change at all) until that's actually set.
 */

namespace Bigfork\HideLogin;

use JetBrains\PhpStorm\NoReturn;

if (!defined('ABSPATH')) {
    exit;
}

function get_login_slug(): ?string
{
    $slug = trim((string) \Env\env('LOGIN_SLUG'), '/');

    return $slug === '' ? null : $slug;
}

function get_request_path(): string
{
    return trim((string) parse_url($_SERVER['REQUEST_URI'] ?? '', PHP_URL_PATH), '/');
}

// wp-login.php/wp-admin aren't reachable through the front-end template system (they're
// separate entry-point scripts), so there's no themed 404 template to render here.
// Reuse the same cached 404 page cache-404.php builds from a real front-end 404, so a
// blocked login/admin request looks identical to any other 404 on the site. Falls back
// to a generic, unbranded body on the off chance the cache hasn't been warmed yet.
#[NoReturn]
function send_404(): void
{
    status_header(404);
    nocache_headers();
    header('Content-Type: text/html; charset=utf-8');

    $cache_file = \Bigfork\Cache404\get_cache_path();

    if (file_exists($cache_file)) {
        readfile($cache_file);
    } else {
        echo '<!DOCTYPE html><html><head><title>404 Not Found</title></head>'
            . '<body><h1>Not Found</h1><p>The requested URL was not found on this server.</p></body></html>';
    }

    exit;
}

$login_slug = get_login_slug();

// LOGIN_SLUG isn't set for this site - leave every URL and 404 behaviour untouched
// rather than "hide" the login behind the plugin's own hardcoded fallback slug.
if ($login_slug === null) {
    return;
}

// WordPress's own canonical redirect (wp_redirect_admin_locations(), hooked to
// template_redirect) treats "/admin", "/dashboard", "/login" etc. as aliases for
// wp-admin/wp-login.php and bounces them to the real, unhidden URL. Removing it lets
// those paths 404 normally instead - through the theme's own 404 template, since they
// still flow through the regular front-end request cycle.
remove_action('template_redirect', 'wp_redirect_admin_locations', 1000);

add_action('init', function () use ($login_slug) {
    $path = get_request_path();

    if ($path === $login_slug) {
        // wp-login.php reliably throws a few harmless notices of its own (eg. an
        // undefined $user_login on a plain GET) that WP normally just swallows.
        // This install's Acorn/Laravel error handler turns those into fatal
        // exceptions, so swap it out for the duration of this require.
        set_error_handler(static fn () => true, E_WARNING | E_NOTICE | E_DEPRECATED);
        require ABSPATH . 'wp-login.php';
        restore_error_handler();
        exit;
    }

    $pagenow = $GLOBALS['pagenow'] ?? '';

    // Must stay reachable for logged-out requests regardless of the above (front-end
    // AJAX, form posts from plugins/themes etc. all go through these two files)
    if (in_array($pagenow, ['admin-ajax.php', 'admin-post.php'], true)) {
        return;
    }

    // Direct hits on the real wp-login.php file - always blocked, logged in or not.
    if ($pagenow === 'wp-login.php') {
        send_404();
    }

    // Direct hits on the real wp-admin while logged out. Once authenticated, wp-admin
    // is left completely alone - this only ever blocks logged-out probing/bookmarks.
    if (is_admin() && !is_user_logged_in()) {
        send_404();
    }
});

// wp_login_url(), wp_logout_url(), wp_lostpassword_url(), register_url() and
// wp-login.php's own form actions all build off site_url('wp-login.php', ...) - but several
// of those (logout, register, lostpassword, resetpass, the postpass form, error redirects)
// bake a query string directly into that path, eg. 'wp-login.php?action=logout', rather
// than appending one afterwards. Matching only the exact 'wp-login.php' string missed all
// of those, leaving them pointing at the real, blocked wp-login.php and 404ing when used.
add_filter('site_url', function ($url, $path) use ($login_slug) {
    if ($path === 'wp-login.php' || str_starts_with($path, 'wp-login.php?')) {
        return home_url('/' . $login_slug . substr($path, strlen('wp-login.php')));
    }

    return $url;
}, 10, 2);

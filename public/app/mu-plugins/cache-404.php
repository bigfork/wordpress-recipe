<?php

/**
 * Plugin Name: Cache 404 Page
 * Description: Caches the theme's rendered 404 page to a static file the first time it's hit, then serves that file directly on every subsequent 404 instead of re-rendering the full page. Also used by hide-login.php so blocked wp-login/wp-admin requests get the same page. Cache is purged and pre-warmed automatically on theme/plugin updates; `wp cache-404 warm` does the same on demand (eg. from a deploy script).
 */

namespace Bigfork\Cache404;

if (!defined('ABSPATH')) {
    exit;
}

function get_cache_path(): string
{
    return WP_CONTENT_DIR . '/cache/404.html';
}

function purge(): void
{
    $file = get_cache_path();

    if (file_exists($file)) {
        unlink($file);
    }
}

// Triggers a real request to a URL that can't possibly exist, so the template_redirect
// handler below does the actual rendering/capturing exactly as it would for organic
// traffic - nothing here needs to know about the theme or how the page gets built.
function warm(): array|\WP_Error
{
    return wp_remote_get(home_url('/' . wp_generate_password(24, false) . '-404-warm'), [
        'blocking' => true,
        'timeout' => 10,
        'sslverify' => false,
    ]);
}

function purge_and_rewarm(): void
{
    purge();
    warm();
}

add_action('switch_theme', __NAMESPACE__ . '\\purge_and_rewarm');
add_action('upgrader_process_complete', __NAMESPACE__ . '\\purge_and_rewarm');

if (defined('WP_CLI') && WP_CLI) {
    \WP_CLI::add_command('cache-404 warm', function () {
        purge();
        $response = warm();

        if (is_wp_error($response)) {
            \WP_CLI::error('Failed to warm the 404 cache: ' . $response->get_error_message());
        }

        if (!file_exists(get_cache_path())) {
            \WP_CLI::error('Request completed but no cache file was written - check the response was really a 404.');
        }

        \WP_CLI::success('404 page cache warmed.');
    });
}

// Priority 1001: must run after WordPress core's wp_redirect_admin_locations() (priority
// 1000), or a stale cached 404 permanently shadows its redirect for bare /wp-admin,
// /wp-login.php etc. to their real, hidden URL - which looks identical to this site being
// genuinely down, not just logged out.
add_action('template_redirect', function () {
    // Only cache/serve for logged-out visitors - keeps this dead simple and avoids
    // ever handing a logged-in user (admin bar, etc.) a cached logged-out response.
    if (!is_404() || is_user_logged_in()) {
        return;
    }

    $cache_file = get_cache_path();

    if (file_exists($cache_file)) {
        status_header(404);
        nocache_headers();
        readfile($cache_file);
        exit;
    }

    // Not cached yet - let the theme render normally, but capture the output so the
    // next hit (this is almost always bot/scanner traffic hammering the same handful
    // of non-existent URLs) can skip straight to a static file.
    ob_start(static function ($html) use ($cache_file) {
        if (trim($html) !== '') {
            wp_mkdir_p(dirname($cache_file));
            file_put_contents($cache_file, $html);
        }

        return $html;
    });
}, 1001);
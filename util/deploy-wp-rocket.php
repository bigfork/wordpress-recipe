<?php

/**
 * Regenerates the WP Rocket files that live inside the release directory — and are therefore
 * lost on every deploy — then purges the cache and starts the preload.
 *
 * Run from Envoyer once the new release has been activated:
 *
 *     cd {{ release }}
 *     wp eval-file util/deploy-wp-rocket.php
 *
 * advanced-cache.php and wp-rocket-config/<host>.php are gitignored and contain absolute
 * paths to the release that generated them, and the .htaccess rewrite rules are wiped when
 * the committed public/.htaccess is restored. WP Rocket only heals any of this on admin_init
 * (inc/admin/admin.php), i.e. the next time somebody happens to load wp-admin.
 */

if (!function_exists('flush_rocket_htaccess')) {
    WP_CLI::warning('WP Rocket is not loaded, skipping cache regeneration.');
    return;
}

// flush_rocket_htaccess() bails out unless $is_apache is true, and get_home_path() needs
// SCRIPT_FILENAME to place the rules in public/.htaccess. WP-CLI sets up neither.
global $is_apache;

$is_apache = true;
$_SERVER['SCRIPT_FILENAME'] = ABSPATH;

rocket_generate_advanced_cache_file();
rocket_generate_config_file();
flush_rocket_htaccess();

// Fires rocket_after_clean_domain, which marks every known URL pending for the preload
rocket_clean_domain();

WP_CLI::success('WP Rocket config regenerated and cache purged.');

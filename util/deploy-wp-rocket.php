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
 * paths to the release that generated them. WP Rocket only heals any of this on admin_init
 * (inc/admin/admin.php), i.e. the next time somebody happens to load wp-admin.
 */

if (!function_exists('rocket_clean_domain')) {
    WP_CLI::warning('WP Rocket is not loaded, skipping cache regeneration.');
    return;
}

rocket_generate_advanced_cache_file();
rocket_generate_config_file();

// Fires rocket_after_clean_domain, which marks every known URL pending for the preload
rocket_clean_domain();

WP_CLI::success('WP Rocket config regenerated and cache purged.');

<?php

// Disable this feature: https://docs.wp-rocket.me/article/1835-automatic-lazy-rendering
// It breaks so many things, and it's a terrible use of content-visibility anyway...
add_filter('rocket_lrc_optimization', '__return_false', 999);

/**
 * WP_CACHE is defined in config/application.php, so stop the plugin writing the constant
 * into public/wp-config.php (as any edits are wiped out on the next deploy)
 *
 * @see wp-rocket/inc/Engine/Cache/WPCache.php should_set_wp_cache_constant()
 */
add_filter('rocket_set_wp_cache_constant', '__return_false');

/**
 * WP Rocket bakes an absolute path to the current release into its rewrite rules, so they
 * point at a dead release after every deploy. Make them relative to the webroot instead,
 * which is a symlink and so always resolves to the live release.
 *
 * @see wp-rocket/inc/functions/htaccess.php get_rocket_htaccess_mod_rewrite()
 */
add_filter('rocket_htaccess_mod_rewrite', function ($rules) {
    // This usually just resolves to "app/"
    $contentDir = str_replace(dirname(ABSPATH), '', WP_CONTENT_DIR);

    // If nothing was replaced, the content directory sits outside the webroot and there is no
    // relative path to give to Apache. Let WP Rocket's own rewrite rules win
    if ($contentDir === WP_CONTENT_DIR) {
        return $rules;
    }

    $cacheDir = trim($contentDir, '/') . '/cache/wp-rocket/';

    // File tests take a filesystem path...
    $rules = preg_replace(
        '#(RewriteCond\s+")\S*?cache/wp-rocket/#',
        '$1%{DOCUMENT_ROOT}/' . $cacheDir,
        $rules
    );

    // ...substitutions take a URL path.
    return preg_replace(
        '#(RewriteRule\s+\S+\s+")\S*?cache/wp-rocket/#',
        '$1/' . $cacheDir,
        $rules
    );
}, 20);

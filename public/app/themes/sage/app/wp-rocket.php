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

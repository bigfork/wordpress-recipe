<?php

add_action('slim_seo_init', function($plugin) {
    $plugin->disable( 'code' );
});

add_filter('slim_seo_robots_txt', function ($content) {
    $content .= "Disallow: /cdn-cgi/\n";
    $content .= "Disallow: /?s=\n";
    return $content;
});

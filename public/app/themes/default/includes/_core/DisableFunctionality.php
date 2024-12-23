<?php

declare(strict_types=1);

/**
 * Disable author templates
 */
add_action('template_redirect', function () {
    if (is_author()) {
        wp_redirect(get_home_url(), 301);
        exit();
    }
});

/**
 * Disable emojis
 */
add_action('init', function () {
    remove_action('wp_head', 'print_emoji_detection_script', 7);
    remove_action('admin_print_scripts', 'print_emoji_detection_script');
    remove_action('wp_print_styles', 'print_emoji_styles');
    remove_action('admin_print_styles', 'print_emoji_styles');
    remove_filter('the_content_feed', 'wp_staticize_emoji');
    remove_filter('comment_text_rss', 'wp_staticize_emoji');
    remove_filter('wp_mail', 'wp_staticize_emoji_for_email');

    add_filter(
        'tiny_mce_plugins',
        fn($plugins) => is_array($plugins)
            ? array_diff($plugins, ['wpemoji'])
            : [],
    );

    add_filter(
        'wp_resource_hints',
        function ($urls, $relation_type) {
            if ('dns-prefetch' == $relation_type) {
                /** This filter is documented in wp-includes/formatting.php */
                $emoji_svg_url = apply_filters(
                    'emoji_svg_url',
                    'https://s.w.org/images/core/emoji/2/svg/',
                );

                $urls = array_diff($urls, [$emoji_svg_url]);
            }

            return $urls;
        },
        10,
        2,
    );
});

add_action('wp_footer', function () {
    wp_dequeue_style('core-block-supports');
});

// Disable support for comments and trackbacks in post types
add_action('admin_init', function () {
    $post_types = get_post_types();
    foreach ($post_types as $post_type) {
        if (post_type_supports($post_type, 'comments')) {
            remove_post_type_support($post_type, 'comments');
            remove_post_type_support($post_type, 'trackbacks');
        }
    }
});

// Close comments on the front-end
add_filter('comments_open', '__return_false', 20, 2);
add_filter('pings_open', '__return_false', 20, 2);
add_filter('comments_array', fn($comments) => [], 10, 2);

// Remove comments page in menu
add_action('admin_menu', fn() => remove_menu_page('edit-comments.php'));

// Redirect any user trying to access comments page
add_action('admin_init', function () {
    global $pagenow;
    if ($pagenow == 'edit-comments.php') {
        wp_redirect(admin_url());
        exit();
    }
});

// Remove comments metabox from dashboard
add_action(
    'admin_init',
    fn() => remove_meta_box('dashboard_recent_comments', 'dashboard', 'normal'),
);

// Remove comments links from admin bar
add_action('init', function () {
    if (is_admin_bar_showing()) {
        remove_action('admin_bar_menu', 'wp_admin_bar_comments_menu', 60);
    }
});

add_action('wp_before_admin_bar_render', function () {
    global $wp_admin_bar;
    $wp_admin_bar->remove_menu('comments');
});

add_action('admin_init', function() {
    remove_submenu_page( 'themes.php', 'edit.php?post_type=wp_block' );
    remove_submenu_page( 'themes.php', 'site-editor.php?path=/patterns' );
}, 100);

// Remove Meta Generator: <meta name="generator" content="WordPress x.x" />
// and <meta name="generator" content="WooCommerce x.x.x" />
remove_action('wp_head', 'wp_generator');

// Remove the EditURI/RSD
// Like: <link rel="EditURI" type="application/rsd+xml" title="RSD" href="http://localhost/wp/xmlrpc.php?rsd" />
remove_action('wp_head', 'rsd_link');

// Remove it if you don't know what is Windows Live Writer
// <link rel="wlwmanifest" type="application/wlwmanifest+xml" href="http://localhost/wp/wp-includes/wlwmanifest.xml" />
remove_action('wp_head', 'wlwmanifest_link');

// Remove page/post's short links
// Like: <link rel='shortlink' href='http://localhost/wp/?p=5' />
remove_action('wp_head', 'wp_shortlink_wp_head');

// Remove feed links
// Like: <link rel="alternate" type="application/rss+xml" title="WP Site &raquo; Feed" href="http://localhost/wp/feed/" />
remove_action('wp_head', 'feed_links', 2);

// Remove comment feeds
// Like: <link rel="alternate" type="application/rss+xml" title="WP Site &raquo; Comments Feed" href="http://localhost/wp/comments/feed/" />
remove_action('wp_head', 'feed_links_extra', 3);

// Disable REST API link tag
remove_action('wp_head', 'rest_output_link_wp_head', 10);

// Disable oEmbed Discovery Links
remove_action('wp_head', 'wp_oembed_add_discovery_links', 10);

// Disable REST API link in HTTP headers
remove_action('template_redirect', 'rest_output_link_header', 11, 0);

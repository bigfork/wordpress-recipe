<?php

declare(strict_types=1);

add_action('admin_init', function () {
    if (WP_ENV != 'development') {
        remove_menu_page('edit.php?post_type=acf-field-group');
        remove_menu_page('tools.php');
        remove_menu_page('plugins.php');
        remove_menu_page('edit-comments.php');
        remove_menu_page('themes.php');
        remove_submenu_page('index.php', 'update-core.php');
        remove_submenu_page('index.php', 'index.php');
    }

    if (current_user_can('administrator')) {
        add_menu_page(
            'Menus',
            'Menus',
            'edit_posts',
            'nav-menus.php',
            '',
            'dashicons-menu',
            '25',
        );
    } else {
        remove_menu_page('tools.php');
    }

    remove_submenu_page('themes.php', 'nav-menus.php');
});

add_action('admin_init', function () {
    if (WP_ENV != 'development') {
        global $pagenow;
        if (in_array($pagenow, ['themes.php'])) {
            wp_redirect(admin_url());
            exit();
        }
    }
});

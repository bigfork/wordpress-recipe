<?php

/**
 * Plugin Name: Auto-updates Configuration
 * Description: Enables auto-updates, but stops CMS users adding plugins etc.
 */

// Allow minor core auto-updates
add_filter( 'allow_major_auto_core_updates', '__return_false' );
add_filter( 'allow_minor_auto_core_updates', '__return_true' );
add_filter( 'automatic_updates_is_vcs_checkout', '__return_false', 1 );

if (defined('WP_ENV') && WP_ENV !== 'development') {
    // Hide the updates UI from everyone
    add_filter('map_meta_cap', function ($caps, $cap) {
        if (in_array($cap, ['update_core', 'update_plugins', 'update_themes'], true)) {
            return ['do_not_allow'];
        }
        return $caps;
    }, 10, 2);

    // Block direct access to the Updates page + remove the menu item
    add_action('admin_init', function () {
        if (($GLOBALS['pagenow'] ?? '') === 'update-core.php') {
            wp_die('Updates are managed automatically.', '', ['response' => 403, 'back_link' => true]);
        }
        remove_action('admin_notices', 'update_nag', 3);
    });

    // Remove updates subnav item
    add_action('admin_menu', function () {
        remove_submenu_page('index.php', 'update-core.php');
    }, 999);

    // Stop ability to upload plugins/themes
    add_filter('map_meta_cap', function ($caps, $cap) {
        $blocked = [
            'install_plugins',
            'upload_plugins',
            'install_themes',
            'upload_themes',
            'delete_plugins',
            'delete_themes',
        ];
        return in_array($cap, $blocked, true) ? ['do_not_allow'] : $caps;
    }, 10, 2);
}

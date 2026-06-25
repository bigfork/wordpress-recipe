<?php

/**
 * Theme filters.
 */

namespace App;

/**
 * Add "… Continued" to the excerpt.
 *
 * @return string
 */
add_filter('excerpt_more', function () {
    return sprintf(' &hellip; <a href="%s">%s</a>', get_permalink(), __('Continued', 'sage'));
});

/**
 * Remove the Dashboard from the admin sidebar entirely.
 */
add_action('admin_menu', function (): void {
    remove_menu_page('index.php');
});

/**
 * Redirect any direct visit to the Dashboard to a more useful landing page.
 * Sends to WooCommerce Orders when WooCommerce is active, otherwise Pages.
 * Respects the HPOS setting for the orders URL.
 */
add_action('admin_init', function (): void {
    global $pagenow;

    if ($pagenow !== 'index.php') {
        return;
    }

    $redirect = admin_url('edit.php?post_type=page');
    if (class_exists('WooCommerce')) {
        $redirect = admin_url('edit.php?post_type=shop_order');
    }

    wp_safe_redirect($redirect);
    exit;
});

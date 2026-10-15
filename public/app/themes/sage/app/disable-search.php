<?php

declare(strict_types=1);

/**
 * Disables front-end search. Delete this file (and its entry in functions.php) on a
 * site that needs search - the theme's search templates and form are left in place.
 */

add_action('parse_query', function (\WP_Query $query): void {
    if (is_admin() || !$query->is_search() || !$query->is_main_query()) {
        return;
    }

    unset($_GET['s'], $_POST['s'], $_REQUEST['s'], $query->query['s']);
    $query->set('s', '');
    $query->is_search = false;
    $query->set_404();
    status_header(404);
    nocache_headers();
}, 5);

add_filter('get_search_form', '__return_empty_string', 999);

add_action('widgets_init', fn() => unregister_widget('WP_Widget_Search'), 1);

// A fresh install seeds sidebar-primary with a Search block widget
add_action('init', function (): void {
    if (\WP_Block_Type_Registry::get_instance()->is_registered('core/search')) {
        unregister_block_type('core/search');
    }
}, 11);

add_action('admin_bar_menu', fn(\WP_Admin_Bar $bar) => $bar->remove_menu('search'), 99999);

add_filter('slim_seo_schema_searchaction_enable', '__return_false');

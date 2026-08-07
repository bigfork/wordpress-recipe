<?php

// Query Monitor is a require-dev package (wp-plugin/query-monitor) so it won't even be
// present on a production install, but gate on WP_ENV too in case that's ever misconfigured.
if (WP_ENV === 'production' || !isset($_GET['debug'])) {
    return;
}

// Query Monitor's toolbar output only exists when the admin bar itself renders, which
// WP hides for logged-out visitors by default.
add_filter('show_admin_bar', '__return_true');

// Visibility is otherwise gated on the view_query_monitor capability (normally
// manage_options only) - grant it to everyone, including logged-out visitors.
add_filter('user_has_cap', function (array $allcaps): array {
    $allcaps['view_query_monitor'] = true;

    return $allcaps;
});

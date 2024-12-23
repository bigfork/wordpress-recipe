<?php

declare(strict_types=1);

const EXAMPLE = 'homepage_hero';

add_action('after_setup_theme', function () {
    add_image_size(EXAMPLE, 2000, 486, true);
});

add_filter('jpeg_quality', fn() => 90);
add_filter('wp_editor_set_quality', fn() => 90);

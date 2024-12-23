<?php

declare(strict_types=1);

add_action('admin_init', function () {
    add_editor_style([
        get_template_directory_uri() . '/dist/css/editor.css',
    ]);
});

add_filter('tiny_mce_before_init', function ($settings) {
    $style_formats = [];

    // Add the array the 'style_formats' setting
    $settings['style_formats'] = json_encode($style_formats);
    $settings['body_class'] = 'typography';

    return $settings;
});

add_filter('mce_buttons', function ($buttons) {
    $formats = array_shift($buttons);
    $buttons = array_merge([$formats, 'styleselect'], $buttons);
    $buttons[] = 'hr';

    return $buttons;
});

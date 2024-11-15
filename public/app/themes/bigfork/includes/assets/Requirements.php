<?php
add_action(
    'wp_enqueue_scripts',
    function () {
        wp_enqueue_script(
            'nesta-script',
            get_template_directory_uri() . '/dist/js/app.js',
            ver: filectime(get_template_directory() . '/dist/js/app.js'),
            args: ['strategy' => 'defer', 'in_footer' => false]
        );

        wp_enqueue_style(
            'nesta-style',
            get_template_directory_uri() . '/dist/css/style.css',
            ver: filectime(get_template_directory() . '/dist/css/style.css')
        );
    }
);
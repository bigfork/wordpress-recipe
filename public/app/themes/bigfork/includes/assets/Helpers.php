<?php
function themedResourceURL(string $path): string
{
    $file =  get_template_directory() . "/{$path}";
    $time = filectime($file);

    return get_template_directory_uri() . "/{$path}?t={$time}";
}

add_filter(
    'template_include',
    function ($t) {
        $GLOBALS['current_theme_template'] = basename($t);
        return $t;
    },
    1000,
);

function get_current_template($echo = false)
{
    if (!isset($GLOBALS['current_theme_template'])) {
        return false;
    }

    if ($echo) {
        echo $GLOBALS['current_theme_template'];
    }

    return $GLOBALS['current_theme_template'];
}

function get_current_template_nice(): string
{
    return str_replace('.php', '', get_current_template() ?: '');
}

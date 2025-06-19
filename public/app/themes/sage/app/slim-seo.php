<?php

add_action('slim_seo_init', function($plugin) {
    $plugin->disable( 'code' );
});

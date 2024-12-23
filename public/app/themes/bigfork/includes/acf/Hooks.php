<?php

declare(strict_types=1);

use function Env\env;

add_filter('acf/fields/google_map/api', function (array $api): array {
    $api['key'] = env('GOOGLE_MAPS_API_KEY');
    return $api;
});

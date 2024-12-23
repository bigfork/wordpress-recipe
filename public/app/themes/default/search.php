<?php
/**
 * Search results page
 *
 * @link https://developer.wordpress.org/themes/basics/template-hierarchy/
 */

declare(strict_types=1);

use Timber\Timber;

$templates = [ 'templates/search.twig', 'templates/archive.twig', 'templates/index.twig' ];

$context = Timber::context(
    [
        'title' => 'Search results for ' . get_search_query(),
    ]
);

Timber::render( $templates, $context );

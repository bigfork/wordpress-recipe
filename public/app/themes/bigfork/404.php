<?php
/**
 * The template for the 404 page
 */

declare(strict_types=1);

namespace App;

use Timber\Timber;

$context = Timber::context();
Timber::render( 'templates/404.twig', $context );

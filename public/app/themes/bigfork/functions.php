<?php
/**
 * bigfork functions and definitions
 *
 * @link https://developer.wordpress.org/themes/basics/theme-functions/
 *
 * @package bigfork
 */

declare(strict_types=1);

namespace App;

use Timber\Timber;

// Load Composer dependencies.
require_once __DIR__ . '/includes/Site.php';

Timber::init();

new Site();

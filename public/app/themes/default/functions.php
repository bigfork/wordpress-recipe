<?php
/**
 * Functions and definitions
 *
 * @link https://developer.wordpress.org/themes/basics/theme-functions/
 *
 * @package default
 */

declare(strict_types=1);

namespace App;

use Timber\Timber;
use WP_Block_Patterns_Registry;

// Load Composer dependencies.
require_once __DIR__ . '/includes/includes.php';
require_once __DIR__ . '/includes/Site.php';

Timber::init();

new Site();

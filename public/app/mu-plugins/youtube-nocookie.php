<?php

declare(strict_types=1);

/**
 * Plugin Name: YouTube No-Cookie Embeds
 * Description: Rewrites YouTube embed URLs in front-end HTML to youtube-nocookie.com, covering post content, oEmbeds, ACF fields and hardcoded theme templates alike. Channel and watch links are left alone.
 */

namespace Bigfork\YoutubeNocookie;

if (!defined('ABSPATH')) {
    exit;
}

function rewrite(string $html): string
{
    if (!str_contains($html, 'youtube.com')) {
        return $html;
    }

    return preg_replace(
        '~(https?:)?(//|\\\\/\\\\/)(?:www\.|m\.)?youtube\.com(/|\\\\/)embed(/|\\\\/)~i',
        '$1$2www.youtube-nocookie.com$3embed$4',
        $html
    ) ?? $html;
}

// Priority 0 so this buffer wraps cache-404.php's, keeping the cached 404 file untouched
// while still rewriting whatever gets served from it.
add_action('template_redirect', static function (): void {
    ob_start(static fn (string $html): string => rewrite($html));
}, 0);

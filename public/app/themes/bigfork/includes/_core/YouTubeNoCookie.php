<?php

add_filter(
    'embed_oembed_html',
    function ($html, $url, $attr, $post_id) {
        // Check if the URL is a YouTube URL
        if (str_contains($url, 'youtube.com') || str_contains($url, 'youtu.be')) {
            // Replace the URL with the no-cookie version
            $html = str_replace(['youtube.com', 'youtu.be'], 'youtube-nocookie.com', $html);

            $html = "<div class='youtube-embed'>{$html}</div>";
        }

        return $html;
    },
    10,
    4
);

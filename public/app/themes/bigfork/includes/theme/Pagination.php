<?php
function numeric_pagination($args = []): void
{
    global $wp_query, $wp_rewrite;

    // Don't print empty markup if there's only one page.
    if ($wp_query->max_num_pages < 2) {
        return;
    }

    $paged = get_query_var('paged') ? intval(get_query_var('paged')) : 1;
    $pagenum_link = html_entity_decode(get_pagenum_link());
    $query_args = [];
    $url_parts = explode('?', $pagenum_link);

    if (isset($url_parts[1])) {
        wp_parse_str($url_parts[1], $query_args);
    }

    $pagenum_link = remove_query_arg(array_keys($query_args), $pagenum_link);
    $pagenum_link = trailingslashit($pagenum_link) . '%_%';

    $format =
        $wp_rewrite->using_index_permalinks() &&
        !strpos($pagenum_link, 'index.php')
            ? 'index.php/'
            : '';
    $format .= $wp_rewrite->using_permalinks()
        ? user_trailingslashit($wp_rewrite->pagination_base . '/%#%', 'paged')
        : '?paged=%#%';

    // Set up paginated links.
    $links = paginate_links(
        array_merge(
            [
                'base' => $pagenum_link,
                'format' => $format,
                'total' => $wp_query->max_num_pages,
                'current' => $paged,
                'mid_size' => 2,
                'add_args' => array_map('urlencode', $query_args),
                'prev_text' => 'Newer posts',
                'next_text' => ' Older posts',
                'type' => 'array',
            ],
            $args,
        ),
    );

    if ($links) : ?>
        <nav class="pagination" aria-label="Pagination">
            <ul>
                <?php foreach ($links as $link) : ?>
                    <li>
                        <?= $link ?>
                    </li>
                <?php endforeach; ?>
            </ul>
        </nav>
    <?php endif;
}

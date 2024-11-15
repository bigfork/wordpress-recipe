<?php
if (function_exists('get_field')) {
    $length = count(get_field('blocks') ?: []);
    $count = 0;
    $counters = [];

    if (have_rows('blocks')): ?>
        <?php while (have_rows('blocks')): the_row();
            $count++;
            $layout = get_row_layout();

            if (!isset($counters[$layout])) {
                // initialize counter
                $counters[$layout] = 1;
            } else {
                // increase existing counter
                $counters[$layout]++;
            }
            ?>

            <div class="block block--<?= $layout ?>">
                <?php get_template_part(
                    'partials/blocks/' . $layout,
                    args: [
                        'first' => $count === 1,
                        'last'  => $count === $length,
                        'id'    => $counters[$layout],
                    ],
                ); ?>
            </div>
        <?php endwhile; ?>
    <?php endif;
}

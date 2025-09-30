<?php

// Disable this feature: https://docs.wp-rocket.me/article/1835-automatic-lazy-rendering
// It breaks so many things, and it's a terrible use of content-visibility anyway...
add_filter('rocket_lrc_optimization', '__return_false' , 999);

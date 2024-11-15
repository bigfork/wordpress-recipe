<?php
/**
 * The template for displaying the footer
 *
 * Contains the closing of the #content div and all content after.
 *
 * @link https://developer.wordpress.org/themes/basics/template-files/#template-partials
 *
 * @package bigfork
 */

?>

<footer class="footer">
    Footer<?php if (is_front_page()) :
        ?> | Blank theme by <a href="https://www.bigfork.co.uk">Bigfork</a><?php
          endif; ?>
</footer>
</div>

<?php wp_footer(); ?>

</body>
</html>

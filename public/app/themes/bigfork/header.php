<?php
/**
 * The header for our theme
 *
 * This is the template that displays all of the <head> section and everything up until <div id="content">
 *
 * @link https://developer.wordpress.org/themes/basics/template-files/#template-partials
 *
 * @package bigfork
 */

?>
<!doctype html>
<html <?php language_attributes(); ?>>
<head>
    <meta charset="<?php bloginfo('charset'); ?>">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <?php wp_head(); ?>
</head>

<body <?php body_class(); ?>>
<?php wp_body_open(); ?>
<div class="viewport">
<header class="header">
    <a href="<?= home_url() ?>"><?= bloginfo('name') ?></a>

    <nav class="nav">
        <ul class="nav__menu">
            <?php foreach (getNavigation('main') as $menuItem) : ?>
                <li class="nav__item">
                    <a
                        href="<?= $menuItem->url ?>"
                        <?= $menuItem->target ? 'target="' . $menuItem->target . '"' : null ?>
                        class="nav__link"
                    ><?= $menuItem->title ?></a>

                    <?php if ($menuItem->children) : ?>
                        <ul class="nav__submenu">
                            <?php foreach ($menuItem->children as $child) : ?>
                                <li class="nav__subitem nav__subitem--{$LinkingMode}">
                                    <a
                                        href="<?= $child->url ?>"
                                        <?= $child->target ? 'target="' . $child->target . '"' : null ?>
                                        class="nav__sublink"
                                    ><?= $child->title ?></a>
                                </li>
                            <?php endforeach; ?>
                        </ul>
                    <?php endif; ?>
                </li>
            <?php endforeach; ?>
        </ul>
    </nav>
</header>


<?php
$menuItems = getNavigation('primary');
$socials = getFooterSocials();
?>

<div class="navigation-main">
    <div class="navigation-main__grid">
        <nav class="navigation-main__menu" aria-label="Main">
            <div class="navigation-main__menu-container">
                <span class="navigation-main__menu-title">Menu</span>

                <ul>
                    <?php foreach ($menuItems as $menuItem): ?>
                        <li>
                            <a
                                    href="<?= $menuItem->url ?>"
                                <?= $menuItem->target ? 'target="' . $menuItem->target . '"' : null ?>
                            ><?= $menuItem->title ?></a>
                        </li>
                    <?php endforeach; ?>
                </ul>
            </div>
        </nav>

        <?php if (!empty($socials)): ?>
            <div class="navigation-main__content">
                <nav class="navigation-main__socials">
                    <ul>
                        <?php foreach ($socials as $social): ?>
                            <li><a href="<?= $social->url ?>" title="Follow us on <?= $social->title ?>"
                                   target="_blank">
                                    <svg width="32" height="32">
                                        <use href="<?= themeResourceURL('dist/images/icons.svg') ?>#icon-<?= $social->icon ?>"></use>
                                    </svg>
                                </a></li>
                        <?php endforeach; ?>
                    </ul>
                </nav>

                <address class="navigation-main__address"><?= getAddress() ?></address>
            </div>
        <?php endif; ?>
    </div>
</div>



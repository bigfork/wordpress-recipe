<?php

declare(strict_types=1);

namespace App;

use Timber\Site as TimberSite;
use Timber\Timber;
use Twig\Environment;
use Twig\TwigFilter;

class Site extends TimberSite
{
    public function __construct()
    {
        add_action('after_setup_theme', [$this, 'theme_supports']);
        add_action('init', [$this, 'register_post_types']);
        add_action('init', [$this, 'register_taxonomies']);

        add_filter('timber/context', [$this, 'add_to_context']);
        add_filter('timber/twig', [$this, 'add_to_twig']);
        add_filter('timber/twig/environment/options', [$this, 'update_twig_environment_options']);

        parent::__construct();
    }

    /**
     * This is where you can register custom post types.
     */
    public function register_post_types()
    {
    }

    /**
     * This is where you can register custom taxonomies.
     */
    public function register_taxonomies()
    {
    }

    /**
     * This is where you add some context
     *
     * @param array $context context['this'] Being the Twig's {{ this }}.
     */
    public function add_to_context(array $context): array
    {
        $context['primary_menu'] = Timber::get_menu('primary');
        $context['site'] = $this;

        return $context;
    }

    function themedResourceURL(string $path): string
    {
        if (str_starts_with($path, '/')) {
            $path = substr($path, 1);
        }

        $file =  get_template_directory() . "/{$path}";
        $time = filectime($file);

        return get_template_directory_uri() . "/{$path}?t={$time}";
    }

    /**
     * This is where you can add your theme supports.
     */
    public function theme_supports(): void
    {
        // Add default posts and comments RSS feed links to head.
        add_theme_support('automatic-feed-links');

        /*
         * Let WordPress manage the document title.
         * By adding theme support, we declare that this theme does not use a
         * hard-coded <title> tag in the document head, and expect WordPress to
         * provide it for us.
         */
        add_theme_support('title-tag');

        /*
         * Enable support for Post Thumbnails on posts and pages.
         *
         * @link https://developer.wordpress.org/themes/functionality/featured-images-post-thumbnails/
         */
        add_theme_support('post-thumbnails');

        /*
         * Switch default core markup for search form, comment form, and comments
         * to output valid HTML5.
         */
        add_theme_support(
            'html5',
            [
                'comment-form',
                'comment-list',
                'gallery',
                'caption',
            ]
        );

        /*
         * Enable support for Post Formats.
         *
         * See: https://codex.wordpress.org/Post_Formats
         */
        add_theme_support(
            'post-formats',
            [
                'aside',
                'image',
                'video',
                'quote',
                'link',
                'gallery',
                'audio',
            ]
        );

        add_theme_support('menus');

        register_nav_menus(
            [
                'primary' => esc_html__('Primary'),
            ]
        );
    }

    /**
     * This is where you can add your own functions to twig.
     *
     * @param Environment $twig get extension.
     */
    public function add_to_twig(Environment $twig): Environment
    {
        $twig->addFilter(new TwigFilter('themedResourceURL', [$this, 'themedResourceURL']));

        return $twig;
    }

    /**
     * Updates Twig environment options.
     *
     * @link https://twig.symfony.com/doc/2.x/api.html#environment-options
     *
     * @param array $options An array of environment options.
     *
     * @return array
     */
    public function update_twig_environment_options(array $options = []): array
    {
        // $options['autoescape'] = true;

        return $options;
    }
}

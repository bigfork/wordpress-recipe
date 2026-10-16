<?php

declare(strict_types=1);

namespace Bigfork\Installer;

use Composer\Script\Event;

require 'vendor/autoload.php';

/**
 * Post create-project helper
 */
class Install
{
    private const string RECIPE_NAME = 'wordpress-recipe';
    private const string RECIPE_THEME = 'sage';
    private const string THEMES_PATH = '/public/app/themes/';
    private const string THEME_COMMAND_PATH = '/.ddev/commands/web/theme';
    private const string APPLICATION_CONFIG_PATH = '/config/application.php';
    private const string SALT_PLACEHOLDER = 'generateme';
    private const int SALT_LENGTH = 64;
    private const string SALT_CHARACTERS = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789!#%&()*+,-./:;<=>?@[]^_{|}~';

    private static string $basePath = '';

    /**
     * Get the document root for this project. Attempts getcwd(), falls back to
     * directory traversal.
     */
    public static function getBasepath(): string
    {
        if (!self::$basePath) {
            $candidate = getcwd() ?: dirname(__FILE__, 4);
            self::$basePath = rtrim($candidate, DIRECTORY_SEPARATOR);
        }

        return self::$basePath;
    }

    /**
     * The project directory name as a lowercase, hyphenated slug - used for both the
     * DDEV project name (a hostname) and the theme directory.
     */
    public static function getProjectSlug(): string
    {
        $slug = strtolower(basename(self::getBasepath()));

        return trim((string) preg_replace('/[^a-z0-9]+/', '-', $slug), '-');
    }

    public static function postCreateProject(Event $event): void
    {
        $io = $event->getIO();
        $config = [
            'ddevShouldStart' => $io->askConfirmation('Would you like DDEV to start after the installation is complete?: [y/n] ', true),
        ];

        $slug = self::getProjectSlug();

        self::updateDDevName($slug);
        self::renameTheme($slug);
        self::copyEnv();
        self::removeReadme();

        if ($config['ddevShouldStart']) {
            self::startDDEV();
            self::installTheme();
        }

        exit;
    }

    protected static function updateDDevName(string $slug): void
    {
        $ddevConfigPath = self::getBasepath() . '/.ddev/config.yaml';

        if ($slug !== '' && file_exists($ddevConfigPath)) {
            self::replaceInFile($ddevConfigPath, self::RECIPE_NAME, $slug);
        }
    }

    /**
     * Renames the Sage theme directory to the project slug, along with the paths and
     * names that point at it, and makes it the default theme for a fresh install.
     * The text domain is left as "sage".
     */
    protected static function renameTheme(string $slug): void
    {
        $base = self::getBasepath();
        $from = $base . self::THEMES_PATH . self::RECIPE_THEME;
        $to = $base . self::THEMES_PATH . $slug;

        if ($slug === '' || $slug === self::RECIPE_THEME || !is_dir($from) || file_exists($to)) {
            return;
        }

        $themePath = 'themes/' . self::RECIPE_THEME;
        $newThemePath = 'themes/' . $slug;

        self::replaceInFile($base . self::THEME_COMMAND_PATH, $themePath, $newThemePath);
        self::replaceInFile($from . '/vite.config.js', $themePath, $newThemePath);
        self::replaceInFile($from . '/package.json', '"name": "' . self::RECIPE_THEME . '"', '"name": "' . $slug . '"');
        self::replaceInFile($from . '/style.css', 'Sage Starter Theme', ucwords(str_replace('-', ' ', $slug)));
        self::replaceInFile(
            $base . self::APPLICATION_CONFIG_PATH,
            "'WP_DEFAULT_THEME', '" . self::RECIPE_THEME . "'",
            "'WP_DEFAULT_THEME', '" . $slug . "'",
        );

        rename($from, $to);
    }

    protected static function replaceInFile(string $path, string $search, string $replace): void
    {
        if (file_exists($path)) {
            file_put_contents($path, str_replace($search, $replace, (string) file_get_contents($path)));
        }
    }

    /**
     * Copies the .env.example to .env if it doesn't exist
     */
    protected static function copyEnv(): void
    {
        $base = self::getBasepath();

        $envPath = $base . '/.env';
        $examplePath = $base . '/.env.example';
        if (file_exists($examplePath) && !file_exists($envPath)) {
            copy($examplePath, $envPath);
        }

        self::generateSalts($envPath);
    }

    /**
     * Replaces each placeholder salt in the .env with its own random value
     */
    protected static function generateSalts(string $envPath): void
    {
        if (!file_exists($envPath)) {
            return;
        }

        $contents = (string) preg_replace_callback(
            '/"' . self::SALT_PLACEHOLDER . '"/',
            static fn (): string => '"' . self::generateSalt() . '"',
            (string) file_get_contents($envPath),
        );

        file_put_contents($envPath, $contents);
    }

    /**
     * Excludes characters that are special inside a double-quoted dotenv value ($ " \ ')
     */
    protected static function generateSalt(): string
    {
        $chars = self::SALT_CHARACTERS;
        $max = strlen($chars) - 1;
        $salt = '';

        for ($i = 0; $i < self::SALT_LENGTH; $i++) {
            $salt .= $chars[random_int(0, $max)];
        }

        return $salt;
    }

    /**
     * Installs the theme's yarn and composer dependencies via "ddev theme install".
     * Root composer dependencies are already installed by create-project itself.
     */
    protected static function installTheme(): void
    {
        if (file_exists(self::getBasepath() . self::THEME_COMMAND_PATH)) {
            echo shell_exec('ddev theme install');
        }
    }

    protected static function removeReadme(): void
    {
        $readmePath = self::getBasepath() . '/README.md';

        if (file_exists($readmePath)) {
            unlink($readmePath);
        }
    }

    protected static function startDDEV(): void
    {
        echo shell_exec('ddev launch');
    }
}

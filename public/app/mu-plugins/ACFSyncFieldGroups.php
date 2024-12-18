<?php
/*
* Plugin Name: ACFSyncFieldGroups
* Plugin URI: https://gist.github.com/hirasso/c48c04def92f839f6264349a1be773b3
* Description: Allows to sync ACF field groups via WP CLI
* Version: 0.0.2
* Author: Rasso Hilber
* Author URI: https://rassohilber.com/
* License: GPL2+
* License URI: https://www.gnu.org/licenses/gpl-2.0.txt
* Requires PHP: 8.0
*/

if (!defined('ABSPATH')) {
    exit;
} // Exit if accessed directly

/**
 * Adds a wp cli command to sync all ACF field groups. Use it like this:
 *
 * "wp rhau acf-sync-field-groups"
 *
 */
class ACFSyncFieldGroups
{

    /**
     * Init
     */
    public static function init()
    {
        add_action('acf/init', [__CLASS__, 'add_wp_cli_command']);
    }

    /**
     * Add the WP_CLI command
     *
     * @return void
     */
    public static function add_wp_cli_command(): void
    {
        if (self::is_wp_cli()) {
            WP_CLI::add_command('rhau acf-sync-field-groups', [__CLASS__, 'wp_cli_acf_sync_field_groups']);
        }
    }

    /**
     * Conditional to check if inside WP_CLI
     *
     * @return boolean
     */
    private static function is_wp_cli(): bool
    {
        return defined('WP_CLI') && WP_CLI;
    }

    /**
     * Syncs all ACF field groups
     *
     * ## OPTIONS
     *
     * @param array $args
     * @param array $assoc_args
     * @return void
     */
    public static function wp_cli_acf_sync_field_groups(array $args = [], array $assoc_args = []): void
    {
        // Only allow this if invoked from WP CLI
        if (!self::is_wp_cli()) {
            return;
        }

        acf_include('includes/admin/admin-internal-post-type-list.php');
        if (!class_exists('ACF_Admin_Internal_Post_Type_List')) {
            WP_CLI::error('Some required ACF classes could not be found. Please update ACF to the latest version.');
        }
        acf_include('includes/admin/post-types/admin-field-groups.php');


        /**
         * @var ACF_Admin_Field_Groups $field_groups_class
         */
        $field_groups_class = acf_get_instance('ACF_Admin_Field_Groups');
        $field_groups_class->setup_sync();

        // Disable "Local JSON" controller to prevent the .json file from being modified during import.
        acf_update_setting('json', false);

        // Sync field groups and generate array of new IDs.
        $files = acf_get_local_json_files();

        foreach ($field_groups_class->sync as $key => $field_group) {
            $local_field_group = json_decode(file_get_contents($files[$key]), true);
            $local_field_group['ID'] = $field_group['ID'];

            $imported_field_group = acf_import_field_group($local_field_group);
            WP_CLI::success("Synced ACF field group: {$imported_field_group["title"]}");
        }
    }
}

/**
 * Initialize the plugin
 */
ACFSyncFieldGroups::init();

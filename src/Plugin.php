<?php

declare(strict_types=1);

namespace Devidw\Hard;

/**
 * Proxy, that communicates with the must-use plugin.
 */
class Plugin
{
    const SLUG = 'dw-hard';
    const VERSION = '1.0.0';

    /**
     * Constructor.
     */
    public function __construct()
    {
        add_filter(
            hook_name: 'plugin_action_links_' . Plugin::getBasename(),
            callback: [$this, 'addActionLinks'],
            priority: PHP_INT_MAX,
        );

        add_filter(
            hook_name: 'plugin_row_meta',
            callback: [$this, 'addRowMeta'],
            accepted_args: 2,
            priority: PHP_INT_MAX,
        );
    }

    /**
     * Get the slug.
     */
    public static function getSlug(): string
    {
        return self::SLUG;
    }

    /**
     * Get the filename.
     */
    public static function getFileBasename(): string
    {
        return self::getSlug() . '.php';
    }

    /**
     * Get the plugin file path.
     */
    public static function getFile(): string
    {
        return dirname(__DIR__) . '/' . self::getFileBasename();
    }

    /**
     * Get plugin basename.
     */
    public static function getBasename(): string
    {
        return self::getSlug() . '/' . self::getFileBasename();
    }

    /**
     * Get the plugin directory path.
     */
    public static function getPluginDir($path = ''): string
    {
        return plugin_dir_path(self::getFile()) . $path;
    }

    /**
     * Action Links.
     * 
     * @see https://neliosoftware.com/blog/how-to-add-a-link-to-your-settings-in-the-wordpress-plugin-list/
     */
    public function addActionLinks(array $actionlinks): array
    {
        $actionlinks['settings'] = '<a href="' . admin_url('options-general.php?page=dw-hard') . '">Settings</a>';

        return $actionlinks;
    }

    /**
     * Plugin Row Meta.
     * 
     * @see https://rudrastyh.com/wordpress/plugin_action_links-plugin_row_meta.html
     */
    public function addRowMeta(array $rowMeta, string $pluginBasename): array
    {
        if ($pluginBasename !== self::getBasename()) {
            return $rowMeta;
        }

        $rowMeta[] = '<a href="' . 'https://github.com/devidw' . '" target="_blank">GitHub</a>';
        $rowMeta[] = '<a href="' . 'https://paypal.me/devidwolf' . '" target="_blank">Donate</a>';

        return $rowMeta;
    }
}

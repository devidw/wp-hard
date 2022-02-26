<?php

declare(strict_types=1);

namespace Devidw\Hard\Harden;

/**
 * Files such as `readme.html`, `license.txt`, etc.
 */
class PublicCoreFiles
{
    /**
     * @see https://github.com/Upperdog/wp-core-update-cleaner/blob/0874dcc7cc31a63064261b45d0959addf2293b0c/wp-core-update-cleaner.php#L82-L96
     */
    static private $files = [
        'license.txt',
        'licens.html',
        'licenza.html',
        'licencia.txt',
        'licenc.txt',
        'licencia-sk_SK.txt',
        'licens-sv_SE.txt',
        'liesmich.html',
        'LEGGIMI.txt',
        'lisenssi.html',
        'olvasdel.html',
        'readme.html',
        'readme-ja.html',
        'wp-config-sample.php',
        'wp-admin/install.php'
    ];

    /**
     * Get all public root files.
     */
    public static function getFiles(): array
    {
        $files = [];

        foreach (self::$files as $file) {
            $files[] = [
                'name' => $file,
                'exists' => file_exists(ABSPATH . $file),
            ];
        }

        return $files;
    }

    /**
     * Are all files deleted?
     */
    public static function areAllFilesDeleted(): bool
    {
        foreach (self::$files as $file) {
            if (file_exists(ABSPATH . $file)) {
                return false;
            }
        }

        return true;
    }

    /**
     * Add actions to delete files.
     */
    public static function addActions(): void
    {
        add_action(
            hook_name: 'admin_post_dw_hard_delete_public_core_files',
            callback: [__CLASS__, 'adminFormHandler']
        );

        if ((bool) get_option('dw_hard_auto_delete_public_core_files') === true) {
            add_action(
                hook_name: '_core_updated_successfully',
                callback: [__CLASS__, 'deleteFiles'],
            );
        }
    }

    /**
     * Delete all public root files.
     */
    public static function deleteFiles(): void
    {
        $files = array_map(function ($file) {
            return ABSPATH . $file;
        }, self::$files);

        foreach ($files as $file) {
            if (file_exists($file)) {
                unlink($file);
            }
        }
    }

    /**
     * Admin form handler.
     */
    public static function adminFormHandler(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            return;
        }

        if (wp_verify_nonce($_POST['_wpnonce'], 'dw-hard-delete-public-core-files') !== 1) {
            return;
        }

        if (!current_user_can('administrator')) {
            return;
        }

        self::deleteFiles();

        wp_redirect(
            location: admin_url('options-general.php?page=dw-hard'),
        );
    }
}

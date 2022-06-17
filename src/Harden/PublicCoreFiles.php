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
     * Constructor.
     */
    public function __construct()
    {
        add_action('admin_init', [$this, 'registerSettings']);

        add_action(
            hook_name: 'admin_post_dw_hard_delete_public_core_files',
            callback: [$this, 'adminFormHandler']
        );

        if ((bool) get_option('dw_hard_auto_delete_public_core_files') === true) {
            add_action(
                hook_name: '_core_updated_successfully',
                callback: [$this, 'deleteFiles'],
            );
        }
    }

    /**
     * Register auto delete public core files setting.
     */
    public function registerSettings(): void
    {
        register_setting(
            option_group: 'dw-hard',
            option_name: 'dw_hard_auto_delete_public_core_files',
            args: [
                'type' => 'boolean',
                'description' => 'Automatically delete public core files on each core update.',
                'sanitize_callback' => function (mixed $value): bool {
                    return (bool) $value;
                },
                'show_in_rest' => false,
                'default' => false,
            ],
        );

        add_settings_section(
            page: 'dw-hard',
            id: 'dw-hard-auto-delete-public-core-files-section',
            title: 'Auto Delete Public Core Files',
            callback: function () {
?>
            <p>
                Automatically delete public core files on each core update.
                For a full list of affected files, see the
                <a href="<?= admin_url('options-general.php?page=dw-hard&tab=files') ?>">
                    Public Core Files
                </a>
                tab.
            </p>
<?php
            },
        );

        add_settings_field(
            page: 'dw-hard',
            section: 'dw-hard-auto-delete-public-core-files-section',
            id: 'dw-hard-auto-delete-public-core-files-field',
            title: 'Auto Delete Public Core Files',
            callback: function () {
                $value = get_option('dw_hard_auto_delete_public_core_files');
                $checked = $value ? 'checked' : '';
                echo <<<HTML
                <input type="checkbox" name="dw_hard_auto_delete_public_core_files" {$checked}>
                HTML;
            },
        );
    }

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
     * Delete all public root files.
     */
    public function deleteFiles(): void
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
    public function adminFormHandler(): void
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
            location: admin_url('options-general.php?page=dw-hard&tab=files'),
        );
    }
}

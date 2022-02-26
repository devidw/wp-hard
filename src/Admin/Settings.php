<?php

declare(strict_types=1);

namespace Devidw\Hard\Admin;

class Settings
{
    /**
     * Constructor.
     */
    public function __construct()
    {
        add_action('admin_init', [$this, 'registerSettings']);
    }

    /**
     * Register settings.
     */
    public function registerSettings(): void
    {
        $this->registerAutoDeletePublicCoreFilesSetting();
    }

    /**
     * Register auto delete public core files setting.
     */
    public function registerAutoDeletePublicCoreFilesSetting(): void
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
            id: 'dw-hard-auto-delete-public-core-files-section',
            title: 'Auto Delete Public Core Files',
            callback: function () {
                echo <<<HTML
                <p>Automatically delete public core files on each core update.</p>
                HTML;
            },
            page: 'dw-hard',
        );

        add_settings_field(
            id: 'dw-hard-auto-delete-public-core-files-field',
            title: 'Auto Delete Public Core Files',
            callback: function () {
                $value = get_option('dw_hard_auto_delete_public_core_files');
                $checked = $value ? 'checked' : '';
                echo <<<HTML
                <input type="checkbox" name="dw_hard_auto_delete_public_core_files" {$checked}>
                HTML;
            },
            page: 'dw-hard',
            section: 'dw-hard-auto-delete-public-core-files-section',
        );
    }
}

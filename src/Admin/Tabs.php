<?php

declare(strict_types=1);

namespace Devidw\Hard\Admin;

use Devidw\Hard\Harden\PublicCoreFiles;

class Tabs
{
    /**
     * Get tabs.
     */
    static public function getTabs(): array
    {
        return [
            [
                'name' => 'Options',
                'slug' => 'options',
                'context' => [
                    'form' => (function () {
                        ob_start();
                        settings_fields(option_group: 'dw-hard');
                        do_settings_sections(page: 'dw-hard');
                        submit_button();
                        $output = ob_get_contents();
                        ob_end_clean();
                        return $output;
                    })(),
                ],
            ],
            [
                'name' => 'Public Core Files',
                'slug' => 'files',
                'context' => [
                    'action' => 'dw_hard_delete_public_core_files',
                    'nonce' => wp_nonce_field(
                        action: 'dw-hard-delete-public-core-files',
                        echo: false,
                    ),
                    'files' => PublicCoreFiles::getFiles(),
                    'allFilesDeleted' => PublicCoreFiles::areAllFilesDeleted(),
                ]
            ],
        ];
    }

    /**
     * Get active tab.
     */
    static public function getActiveTab(): array
    {
        $activeTabSlug = filter_input(
            type: INPUT_GET,
            var_name: 'tab',
        );

        $tabs = self::getTabs();

        $activeTabIndex = array_search(
            needle: $activeTabSlug,
            haystack: array_column($tabs, 'slug'),
            strict: true,
        );

        if ($activeTabSlug === false or $activeTabSlug === null or $activeTabIndex === false) {
            return current($tabs);
        }

        return $tabs[$activeTabIndex];
    }
}

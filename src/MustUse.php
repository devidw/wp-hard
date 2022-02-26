<?php

declare(strict_types=1);

namespace Devidw\Hard;

use Devidw\Hard\Plugin;

/**
 * Wrapper to manage the must-use plugin file, that will initialize the plugin core.
 */
class MustUse
{
    /**
     * Constructor.
     */
    public function __construct()
    {
        // dump(Plugin::getFile());

        register_activation_hook(
            file: Plugin::getFile(),
            callback: [$this, 'activate']
        );

        register_deactivation_hook(
            file: Plugin::getFile(),
            callback: [$this, 'deactivate']
        );
    }

    /**
     * Get file path.
     */
    public function getFile(): string
    {
        return WPMU_PLUGIN_DIR . '/' . Plugin::getFileBasename();
    }

    /**
     * Initialize must-use plugin.
     */
    public function activate(): void
    {
        if (!$this->doesMustUseDirExist()) {
            $this->makeMustUseDir();
        }

        if ($this->doesMustUseDirExist() && !$this->doesMustUsePluginExist()) {
            $this->createMustUsePlugin();
        }
    }

    /**
     * Deactivate must-use plugin.
     */
    public function deactivate(): void
    {
        $this->deleteMustUsePlugin();
    }

    /**
     * Does must-use plugin directory exist?
     */
    private function doesMustUseDirExist(): bool
    {
        return is_dir(WPMU_PLUGIN_DIR);
    }

    /**
     * Make must-use plugin directory.
     */
    private function makeMustUseDir(): void
    {
        mkdir(
            directory: WPMU_PLUGIN_DIR,
            permissions: 0755,
        );
    }

    /**
     * Does must-use plugin exist?
     */
    private function doesMustUsePluginExist(): bool
    {
        return file_exists(MustUse::getFile());
    }

    /**
     * Add must-use plugin.
     * 
     * Extract the metadata of the normal plugin and write it to the must-use 
     */
    private function createMustUsePlugin(): int|false
    {
        $version = Plugin::VERSION;

        $autoloader = dirname(__DIR__) . '/vendor/autoload.php';

        $mustUseContents = <<<PHP
        <?php

        /**
         * Plugin Name: Hardening
         * Description: Hardening WordPress.
         * Version:     {$version}
         * Author:      David Wolf
         * Author URI:  https://david.wolf.gdn
         */

        declare(strict_types=1);

        namespace Devidw\Hard;

        use Devidw\Hard\Init;

        defined('ABSPATH') or die('No script kiddies please!');

        if (file_exists('{$autoloader}')) {
            @include_once '{$autoloader}';

            new Init();
        }
        PHP;

        return file_put_contents(
            filename: MustUse::getFile(),
            data: $mustUseContents
        );
    }

    /**
     * Delete must-use plugin.
     */
    private function deleteMustUsePlugin(): bool
    {
        if (!$this->doesMustUsePluginExist()) {
            return true;
        }

        return unlink(MustUse::getFile());
    }
}

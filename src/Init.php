<?php

declare(strict_types=1);

namespace Devidw\Hard;

/**
 * Plugin core is initialized here.
 */
class Init
{
    /**
     * Constructor.
     */
    public function __construct()
    {
        $this->init();
    }

    /**
     * Initialize plugin.
     */
    private function init(): void
    {
        if (is_admin()) {
            new Admin\Page();
        }

        $this->harden();
    }

    /**
     * Let's harden WordPress.
     */
    public function harden(): void
    {
        new Harden\HttpHeader(); // Before login, since login sends headers.
        new Harden\Login();
        new Harden\Author();
        new Harden\WpVersion();
        // new Harden\RestApi();
        new Harden\XmlRpc();
        new Harden\Wlw();
        new Harden\Rsd();
        new Harden\FileEdit();
        new Harden\PublicCoreFiles();
    }
}

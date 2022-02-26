<?php

declare(strict_types=1);

namespace Devidw\Hard\Harden;

/**
 * Windows Live Writer (WLW)
 */
class Wlw
{
    public function __construct()
    {
        /**
         * @see https://developer.wordpress.org/reference/functions/wlwmanifest_link/
         */
        remove_action('wp_head', 'wlwmanifest_link');
    }
}

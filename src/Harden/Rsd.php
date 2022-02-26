<?php

declare(strict_types=1);

namespace Devidw\Hard\Harden;

/**
 * Really Simple Discovery (RSD)
 */
class Rsd
{
    public function __construct()
    {
        /**
         * 
         */
        remove_action('wp_head', 'rsd_link');
    }
}

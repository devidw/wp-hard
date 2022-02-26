<?php

declare(strict_types=1);

namespace Devidw\Hard\Harden;

/**
 * XML-RPC
 */
class XmlRpc
{
    public function __construct()
    {
        /**
         * @see https://developer.wordpress.org/reference/hooks/xmlrpc_enabled/
         */
        add_filter('xmlrpc_enabled', '__return_false');
    }
}

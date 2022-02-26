<?php

declare(strict_types=1);

namespace Devidw\Hard\Harden;

class HttpHeader
{
    private array $headers = [
        'X-Frame-Options' => 'DENY',
        'X-XSS-Protection' => '1; mode=block',
        'X-Content-Type-Options' => 'nosniff',
        // 'Referrer-Policy' => 'no-referrer',
        'Strict-Transport-Security' => 'max-age=31536000; includeSubDomains',
    ];

    /**
     * Constructor.
     */
    public function __construct()
    {
        /**
         * Remove the `X-Redirect-By: WordPress` header.
         */
        add_filter('x_redirect_by', '__return_false', PHP_INT_MAX);

        /**
         * @see https://developer.wordpress.org/reference/hooks/wp_headers/
         */
        add_filter('wp_headers', [$this, 'add'], PHP_INT_MAX);

        /**
         * `send_headers` not working on login and similar pages.
         */
        add_action('init', [$this, 'remove'], PHP_INT_MAX);
    }

    /**
     * Filter HTTP headers.
     */
    public function add(array $headers): array
    {
        return array_merge($headers, $this->headers);
    }

    /**
     * Remove HTTTP headers.
     */
    public function remove(): void
    {
        /**
         * We don't want to leak the PHP version via the `X-Powered-By` header.
         * 
         * `ini_set('expose_php', 'off');` won't work.
         * 
         * @see https://stackoverflow.com/q/2330291/13765033
         */
        header_remove('X-Powered-By');
    }
}

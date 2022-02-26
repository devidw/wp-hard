<?php

declare(strict_types=1);

namespace Devidw\Hard\Harden;

class RestApi
{
    /**
     * Constructor.
     */
    public function __construct()
    {
        $this->remove();
    }

    /**
     * Remove JSON REST API.
     * 
     * @see https://stackoverflow.com/a/67429203/13765033
     */
    public function remove(): void
    {
        remove_action('init', 'rest_api_init');
        remove_action('rest_api_init', 'rest_api_default_filters', 10);
        remove_action('rest_api_init', 'register_initial_settings', 10);
        remove_action('rest_api_init', 'create_initial_rest_routes', 99);
        remove_action('parse_request', 'rest_api_loaded');
    }
}

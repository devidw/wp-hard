<?php

declare(strict_types=1);

namespace Devidw\Hard\Harden;

class WpVersion
{
    public function __construct()
    {
        /**
         * Remove `meta[name="generator"]` from `<head>`.
         */
        remove_action('wp_head', 'wp_generator');

        /**
         * Empty the generator in the RSS feed.
         */
        add_filter('the_generator', '__return_empty_string');

        add_filter('style_loader_src', [$this, 'removeAssetVersionQuery'], PHP_INT_MAX);
        add_filter('script_loader_src', [$this, 'removeAssetVersionQuery'], PHP_INT_MAX);
        // add_filter('wp_admin_css', [$this, 'removeAssetVersionQuery'], PHP_INT_MAX);
    }

    /**
     * Remove `ver` query from `<link>` and `<script>` tags.
     * 
     * Only remove those, which would potentially reveal the WordPress version.
     */
    function removeAssetVersionQuery(string $src): string
    {
        global $wp_version;

        if (!str_contains($src, "?ver={$wp_version}")) {
            return $src;
        }

        return remove_query_arg('ver', $src);
    }
}

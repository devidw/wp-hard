<?php

declare(strict_types=1);

namespace Devidw\Hard\Harden;

class Author
{
    /**
     * Constructor.
     */
    public function __construct()
    {
        /**
         * `template_redirect` is too late
         */
        add_action('pre_get_posts', [$this, 'removeAuthorArchive']);
    }

    /**
     * Remove author profiles.
     * 
     * Author pages could possible be used to sniff login usernames for example `?author=1` for the admin username.
     * 
     * @see https://github.com/wp-plugins/ninjafirewall/blob/c5d215b51153474d2dd5b013e58cd80f19470662/lib/nfw_misc.php#L99-L111
     */
    public function removeAuthorArchive(): void
    {
        if (is_author()) {

            wp_redirect(
                location: home_url(),
                status: 301,
                x_redirect_by: ''
            );

            die;
        }
    }
}

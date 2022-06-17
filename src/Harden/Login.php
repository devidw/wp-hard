<?php

declare(strict_types=1);

namespace Devidw\Hard\Harden;

use Devidw\WordPress\Helper\Helper;

class Login
{
    /**
     * Constructor.
     */
    public function __construct()
    {
        add_action(
            hook_name: 'admin_init',
            callback: [$this, 'registerSettings'],
        );

        if ($this->useCustomLoginPage()) {

            /**
             * Remove the `wp-admin` to `wp-login.php` redirect.
             */
            add_action(
                hook_name: 'auth_redirect_scheme',
                callback: [$this, 'removeAdminToLoginRedirect'],
                // priority: PHP_INT_MAX,
            );

            /**
             * Remove the `wp-admin` to `admin` and `wp-login` to `login` etc. redirects.
             * 
             * @see https://stackoverflow.com/a/37569749/13765033
             */
            remove_action(
                hook_name: 'template_redirect',
                callback: 'wp_redirect_admin_locations',
                priority: 1000,
            );

            /**
             * On logout go home.
             */
            add_action(
                hook_name: 'wp_logout',
                callback: function () {
                    wp_redirect(home_url());
                    die;
                },
                priority: PHP_INT_MAX
            );

            add_action(
                hook_name: 'init',
                callback: [$this, 'addCustomLoginPageUrl'],
            );

            add_filter(
                hook_name: 'query_vars',
                callback: [$this, 'addCustomLoginPageUrlQueryVar'],
            );

            add_action(
                hook_name: 'template_redirect',
                callback: [$this, 'addCustomLoginPageRedirect'],
            );

            add_action(
                hook_name: 'init',
                callback: [$this, 'replaceDefaultLoginPage'],
                priority: PHP_INT_MAX,
            );
        }
    }

    /**
     * Remove the `wp-admin` to `wp-login.php` redirect.
     * 
     * @see https://stackoverflow.com/a/42490453/13765033
     */
    public function removeAdminToLoginRedirect(string $scheme): string
    {
        if (is_user_logged_in()) {
            return $scheme;
        }

        status_header(404);

        die;
    }

    /**
     * Regsister settings.
     */
    public function registerSettings(): void
    {
        register_setting(
            option_group: 'dw-hard',
            option_name: 'dw_hard_custom_login_slug',
            args: [
                'type' => 'string',
                'description' => 'Change the login URL',
                'sanitize_callback' => function (null|string $value): string {
                    $chars = str_split($value);

                    $chars = array_filter($chars, function ($char) {
                        return preg_match('/[a-zA-Z0-9]/', $char);
                    });

                    $slug = implode('', $chars);

                    if (strlen($slug) <= 32) {
                        return $slug;
                    }

                    return substr($slug, 0, 32);
                },
                'show_in_rest' => false,
                'default' => '',
            ],
        );

        add_settings_section(
            page: 'dw-hard',
            id: 'dw-hard-login-section',
            title: 'Login',
            callback: function () {
?>
            <p>
                Restrict direct access to your WordPress admin login page by replacing the default
                <a href="<?= wp_login_url() ?>" target="_blank" rel="noopener noreferrer"><code>wp-login.php</code></a>
                with a custom login URL, only you know about.
            </p>
        <?php
            },
        );

        add_settings_field(
            page: 'dw-hard',
            section: 'dw-hard-login-section',
            id: 'dw-hard-change-login-url-field',
            title: 'Set a custom login URL',
            callback: function () {
                $permalinkUrl = get_admin_url(
                    path: 'options-permalink.php',
                );

                $value = esc_html(get_option('dw_hard_custom_login_slug'));

        ?>
            <a href="<?= $this->getCustomLoginPageUrl() ?>" target="_blank" rel="noopener noreferrer"><code><?= home_url('/') ?></code></a>

            <input type="text" name="dw_hard_custom_login_slug" value="<?= $value ?>" pattern="[a-zA-Z0-9]+" maxlength="32" placeholder="supersecret">

            <a href="<?= $this->getCustomLoginPageUrl() ?>" target="_blank" rel="noopener noreferrer"><code>/</code></a>

            <p>
                Allowed characters: a-z, A-Z, 0-9 and up to 32 characters.
            </p>

            <p>
                After saving your custom login page URL, you have to reflush the permalinks. Go to the <a href="<?= $permalinkUrl ?>">Permalinks</a> page and click the <strong>Save Changes</strong> button.
            </p>
<?php
            },
        );
    }

    /**
     * Get the custom login page slug.
     */
    public function getCustomLoginPageSlug(): string|bool
    {
        return get_option('dw_hard_custom_login_slug');
    }

    /**
     * Get custom login page URL.
     */
    public function getCustomLoginPageUrl(): string
    {
        $slug = $this->getCustomLoginPageSlug();

        if (!$slug) {
            return wp_login_url();
        }

        return home_url('/') . $slug . '/';
    }

    /**
     * Should the default login page be removed and replaced with a custom one?
     */
    public function useCustomLoginPage(): bool
    {
        return !empty($this->getCustomLoginPageSlug());
    }

    /**
     * Filter login URL.
     */
    public function filterLoginUrl(string $url): string
    {
        return add_query_arg(
            'dw_hard_login_nonce',
            wp_create_nonce('dw_hard_login_nonce'),
            $url,
        );
    }

    /**
     * Add custom login page route.
     */
    public function addCustomLoginPageUrl(): void
    {
        add_rewrite_rule(
            regex: '^' . $this->getCustomLoginPageSlug() . '/?$',
            query: 'index.php?dw_hard_login=1',
            after: 'top',
        );
    }

    /**
     * Add custom login page query var.
     */
    function addCustomLoginPageUrlQueryVar(array $vars): array
    {
        $vars[] = 'dw_hard_login';
        return $vars;
    }

    /**
     * Add a custom login page.
     */
    public function addCustomLoginPageRedirect(): void
    {
        if (get_query_var('dw_hard_login', false) === false) {
            return;
        }

        wp_redirect(
            location: add_query_arg(
                'dw_hard_login_nonce',
                wp_create_nonce('dw_hard_login_nonce'),
                wp_login_url(),
            ),
        );

        die;
    }

    /**
     * Replace the default login page.
     */
    public function replaceDefaultLoginPage(): void
    {
        if (!Helper::isLoginPage()) {
            return;
        }

        /**
         * Doing default WordPress logout.
         */
        if (
            !empty($_GET['action'])
            and $_GET['action'] === 'logout'
            and !empty($_GET['_wpnonce'])
            and wp_verify_nonce($_GET['_wpnonce'], 'log-out') === 1
        ) {
            return;
        }

        /**
         * Doing the customized WordPress login.
         */
        if (
            !empty($_GET['dw_hard_login_nonce'])
            and wp_verify_nonce($_GET['dw_hard_login_nonce'], 'dw_hard_login_nonce') === 1
        ) {

            /**
             * We have to add the nonce to the form[action*="wp-login.php"] to keep things working after submitting the form.
             */
            if (Helper::isLoginPage()) {
                add_filter(
                    hook_name: 'site_url',
                    callback: [$this, 'filterLoginUrl'],
                );
            }

            return;
        }

        status_header(404);

        die;
    }
}

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
                'sanitize_callback' => function (mixed $value): string {
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
            id: 'dw-hard-login-section',
            title: 'Login',
            callback: function () {
                echo <<<HTML
                <p>
                    Remove the default WordPress login page and set up a custom login page.
                </p>
                HTML;
            },
            page: 'dw-hard',
        );

        add_settings_field(
            id: 'dw-hard-change-login-url-field',
            title: 'Set a custom login URL',
            callback: function () {
                $permalinkUrl = get_admin_url(
                    path: 'options-permalink.php',
                );

                $value = esc_html(get_option('dw_hard_custom_login_sliug'));

                echo <<<HTML
                <input type="text" name="dw_hard_custom_login_sliug" value="{$value}" pattern="[a-zA-Z0-9]+" maxlength="32" placeholder="supersecret">
                <p>
                    Allowed characters: a-z, A-Z, 0-9 and up to 32 characters.
                </p>
                <p>
                    After saving your custom login page URL, you have to go to reflush the permalinks. Go to the <a href="{$permalinkUrl}">Permalinks</a> page and click the <strong>Save Changes</strong> button.
                </p>
                HTML;
            },
            page: 'dw-hard',
            section: 'dw-hard-login-section',
        );
    }

    /**
     * Get the custom login page slug.
     */
    public function getCustomLoginPageSlug(): string|bool
    {
        return get_option('dw_hard_custom_login_sliug');
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
        if (
            !Helper::isLoginPage() or
            !empty($_GET['action']) and $_GET['action'] === 'logout' and !empty($_GET['_wpnonce']) and wp_verify_nonce($_GET['_wpnonce'], 'log-out') === 1 or
            !empty($_GET['dw_hard_login_nonce']) and wp_verify_nonce($_GET['dw_hard_login_nonce'], 'dw_hard_login_nonce') === 1
        ) {

            /**
             * When we are on the login page, we have to add the nonce, to the form[action*="wp-login.php"].
             */
            if (Helper::isLoginPage()) {
                add_filter(
                    hook_name: 'site_url',
                    callback: [$this, 'filterLoginUrl'],
                );
            }

            return;
        }

        header('HTTP/1.0 404 Not Found');

        @include_once get_query_template('404');

        die;
    }
}

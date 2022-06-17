<?php

declare(strict_types=1);

namespace Devidw\Hard\Admin;

use Devidw\Hard\Plugin;
use Devidw\Hard\Admin\Tabs;

class Page
{
    public function __construct()
    {
        add_action(
            hook_name: 'admin_menu',
            callback: [$this, 'addMenuEntry']
        );
    }

    public function addMenuEntry(): void
    {
        add_options_page(
            page_title: 'Hardening',
            menu_title: 'Hardening',
            capability: 'administrator',
            menu_slug: 'dw-hard',
            callback: [$this, 'renderSettingsPage'],
            position: null,
        );
    }

    public function renderSettingsPage(): void
    {
        $loader = new \Twig\Loader\FilesystemLoader(
            paths: Plugin::getPluginDir('templates'),
        );

        $twig = new \Twig\Environment(
            loader: $loader,
            // options: [
            //     'cache' => Plugin::getPluginDir('cache'),
            // ]
        );

        $twig->addExtension(new \Twig\Extension\DebugExtension());

        echo $twig->render(
            name: 'settings.twig',
            context: [
                'title' => 'Hardening',
                'url' => admin_url('options-general.php?page=dw-hard'),
                'activeTab' => Tabs::getActiveTab(),
                'tabs' => Tabs::getTabs(),
                'siteUrl' => get_site_url(),
                'formAction' => admin_url('admin-post.php'),
                'publicCoreFiles' => [],
            ]
        );
    }
}

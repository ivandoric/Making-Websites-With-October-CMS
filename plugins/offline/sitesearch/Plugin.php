<?php namespace OFFLINE\SiteSearch;

use Backend;
use System\Classes\PluginBase;

/**
 * SiteSearch Plugin Information File
 */
class Plugin extends PluginBase
{
    /**
     * Returns information about this plugin.
     *
     * @return array
     */
    public function pluginDetails()
    {
        return [
            'name'        => 'offline.sitesearch::lang.plugin.name',
            'description' => 'offline.sitesearch::lang.plugin.description',
            'author'      => 'offline.sitesearch::lang.plugin.author',
            'icon'        => 'icon-search',
            'homepage'    => 'https://github.com/OFFLINE-GmbH/oc-site-search-plugin',
        ];
    }

    /**
     * Registers any front-end components implemented in this plugin.
     *
     * @return array
     */
    public function registerComponents()
    {
        return [
            'OFFLINE\SiteSearch\Components\SearchResults'     => 'searchResults',
            'OFFLINE\SiteSearch\Components\SearchInput'       => 'searchInput',
            'OFFLINE\SiteSearch\Components\SiteSearchInclude' => 'siteSearchInclude',
        ];
    }

    /**
     * Registers any back-end permissions.
     *
     * @return array
     */
    public function registerPermissions()
    {
        return [
            'offline.sitesearch.manage_settings' => [
                'tab'   => 'offline.sitesearch::lang.plugin.name',
                'label' => 'offline.sitesearch::lang.plugin.manage_settings_permission',
            ],
        ];
    }

    /**
     * Registers any back-end settings.
     *
     * @return array
     */
    public function registerSettings()
    {
        return [
            'config' => [
                'label'       => 'offline.sitesearch::lang.plugin.name',
                'description' => 'offline.sitesearch::lang.plugin.manage_settings',
                'category'    => 'system::lang.system.categories.cms',
                'icon'        => 'icon-search',
                'class'       => 'Offline\SiteSearch\Models\Settings',
                'order'       => 500,
                'keywords'    => 'search',
                'permissions' => ['offline.sitesearch.manage_settings']
            ],
        ];
    }
}

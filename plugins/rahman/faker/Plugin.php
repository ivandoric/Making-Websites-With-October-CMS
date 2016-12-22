<?php namespace Rahman\Faker;

use Backend;
use System\Classes\PluginBase;
use Event;
use Faker\Factory;

/**
 * Faker Plugin Information File
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
            'name'        => 'Faker',
            'description' => 'Fake data for developing process.',
            'author'      => 'Rahman',
            'icon'        => 'icon-database'
        ];
    }

    public function boot()
    {
        Event::listen('cms.page.beforeDisplay', function($controller, $url, $page) {
            $controller->vars['fake'] = Factory::create();
        });
    }

    /**
     * Registers any front-end components implemented in this plugin.
     *
     * @return array
     */
    public function registerComponents()
    {
        return []; // Remove this line to activate

        return [
            'Rahman\Faker\Components\MyComponent' => 'myComponent',
        ];
    }

    /**
     * Registers any back-end permissions used by this plugin.
     *
     * @return array
     */
    public function registerPermissions()
    {
        return []; // Remove this line to activate

        return [
            'rahman.faker.some_permission' => [
                'tab' => 'Faker',
                'label' => 'Some permission'
            ],
        ];
    }

    /**
     * Registers back-end navigation items for this plugin.
     *
     * @return array
     */
    public function registerNavigation()
    {
        return []; // Remove this line to activate

        return [
            'faker' => [
                'label'       => 'Faker',
                'url'         => Backend::url('rahman/faker/mycontroller'),
                'icon'        => 'icon-leaf',
                'permissions' => ['rahman.faker.*'],
                'order'       => 500,
            ],
        ];
    }

}

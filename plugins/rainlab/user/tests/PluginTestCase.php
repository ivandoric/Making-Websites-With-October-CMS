<?php namespace RainLab\User\Tests;

use App;
use PluginTestCase as BasePluginTestCase;
use Illuminate\Foundation\AliasLoader;
use RainLab\User\Models\Settings;

abstract class PluginTestCase extends BasePluginTestCase
{
    /**
     * @var array   Plugins to refresh between tests.
     */
    protected $refreshPlugins = [
        'RainLab.User',
    ];

    /**
     * Perform test case set up.
     *
     * @return void
     */
    public function setUp()
    {
        parent::setUp();

        // reset any modified settings
        Settings::resetDefault();

        // log out after each test
        \RainLab\User\Classes\AuthManager::instance()->logout();

        // register the auth facade
        $alias = AliasLoader::getInstance();
        $alias->alias('Auth', 'RainLab\User\Facades\Auth');
    
        App::singleton('user.auth', function () {
            return \RainLab\User\Classes\AuthManager::instance();
        });
    }
}
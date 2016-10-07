<?php namespace Watchlearn\Movies;

use System\Classes\PluginBase;

class Plugin extends PluginBase
{
    public function registerComponents()
    {
    }


    public function registerFormWidgets()
    {
        return [
            'Watchlearn\Movies\FormWidgets\Actorbox' => [
                'label' => 'Actorbox field',
                'code'  => 'actorbox'
            ]    
        ];
    }

    public function registerSettings()
    {
    }
}

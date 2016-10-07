<?php namespace Watchlearn\Movies\FormWidgets;

use Backend\Classes\FormWidgetBase;
use Config;

class ActorBox extends FormWidgetBase
{
    public function widgetDetails()
    {
        return [
            'name' => 'Actorbox',
            'description' => 'Field for adding actors'
        ];
    }

    public function render(){
        return $this->makePartial('widget');
    }

    public function loadAssets()
    {
        $this->addCss('css/select2.css');
        $this->addJs('js/select2.js');
    }
}

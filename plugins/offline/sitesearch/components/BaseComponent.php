<?php

namespace OFFLINE\SiteSearch\Components;
use Cms\Classes\ComponentBase as OctoberBaseComponent;


abstract class BaseComponent extends OctoberBaseComponent
{
    /**
     * Sets a var as a property on this class
     * and as a key in $this->page.
     *
     * If no value is specified the component property
     * named $var is set as value.
     *
     * @param      $var
     * @param null $value
     */
    protected function setVar($var, $value = null)
    {
        if ($value === null) {
            $value = $this->property($var);
        }
        $this->{$var} = $this->page[$var] = $value;
    }

}
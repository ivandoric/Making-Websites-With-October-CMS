<?php namespace OFFLINE\SiteSearch\Models;

use Model;
use Cms\Classes\Page;

class Settings extends Model
{
    public $implement = ['System.Behaviors.SettingsModel'];

    // A unique code
    public $settingsCode = 'offline_sitesearch_settings';

    // Reference to field configuration
    public $settingsFields = 'fields.yaml';
    
    /**
     *
     * Returns pages list for blog page selection
     *
     * @param null $keyValue
     * @param null $fieldName
     * @return mixed
     */
    public function blogPageOptions($keyValue = null, $fieldName = null)
    {
        return Page::sortBy('baseFileName')->lists('baseFileName', 'baseFileName');
    }
}
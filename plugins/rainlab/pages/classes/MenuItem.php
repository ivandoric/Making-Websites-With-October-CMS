<?php namespace RainLab\Pages\Classes;

use ApplicationException;
use Validator;
use Lang;
use Event;

/**
 * Represents a menu item.
 * This class is used in the back-end for managing the menu items.
 * On the front-end items are represented with the
 * \RainLab\Pages\Classes\MenuItemReference objects.
 *
 * @package rainlab\pages
 * @author Alexey Bobkov, Samuel Georges
 */
class MenuItem
{
    /**
     * @var string Specifies the menu title
     */
    public $title;

    /**
     * @var array Specifies the item subitems
     */
    public $items = [];

    /**
     * @var string Specifies the parent menu item.
     * An object of the RainLab\Pages\Classes\MenuItem class or null.
     */
    public $parent;

    /**
     * @var boolean Determines whether the auto-generated menu items could have subitems.
     */
    public $nesting;

    /**
     * @var string Specifies the menu item type - URL, static page, etc.
     */
    public $type = 'url';

    /**
     * @var string Specifies the URL for URL-type items.
     */
    public $url;

    /**
     * @var string Specifies the menu item code.
     */
    public $code;

    /**
     * @var string Specifies the object identifier the item refers to.
     * The identifier could be the database identifier or an object code.
     */
    public $reference;

    /**
     * @var boolean Indicates that generated items should replace this item.
     */
    public $replace;

    /**
     * @var string Specifies the CMS page path to resolve dynamic menu items to.
     */
    public $cmsPage;

    /**
     * @var boolean Used by the system internally.
     */
    public $exists = false;

    public $fillable = [
        'title',
        'nesting',
        'type',
        'url',
        'code',
        'reference',
        'cmsPage',
        'replace',
        'viewBag'
    ];

    /**
     * @var array Contains the view bag properties.
     * This property is used by the menu editor internally.
     */
    public $viewBag = [];

    /**
     * Initializes a menu item from a data array.
     * @param array $items Specifies the menu item data.
     * @return Returns an array of the MenuItem objects.
     */
    public static function initFromArray($items)
    {
        $result = [];

        foreach ($items as $itemData) {
            $obj = new self;

            foreach ($itemData as $name => $value) {
                if ($name != 'items') {
                    if (property_exists($obj, $name)) {
                        $obj->$name = $value;
                    }
                }
                else {
                    $obj->items = self::initFromArray($value);
                }
            }

            $result[] = $obj;
        }

        return $result;
    }

    /**
     * Returns a list of registered menu item types
     * @return array Returns an array of registered item types
     */
    public function getTypeOptions($keyValue = null)
    {
        /*
         * Baked in types
         */
        $result = [
            'url' => 'URL',
            'header' => 'Header',
        ];

        $apiResult = Event::fire('pages.menuitem.listTypes');

        if (is_array($apiResult)) {
            foreach ($apiResult as $typeList) {
                if (!is_array($typeList)) {
                    continue;
                }

                foreach ($typeList as $typeCode => $typeName) {
                    $result[$typeCode] = $typeName;
                }
            }
        }

        return $result;
    }

    public function getCmsPageOptions($keyValue = null)
    {
        return []; // CMS Pages are loaded client-side
    }

    public function getReferenceOptions($keyValue = null)
    {
        return []; // References are loaded client-side
    }

    public static function getTypeInfo($type)
    {
        $result = [];
        $apiResult = Event::fire('pages.menuitem.getTypeInfo', [$type]);

        if (is_array($apiResult)) {
            foreach ($apiResult as $typeInfo) {
                if (!is_array($typeInfo)) {
                    continue;
                }

                foreach ($typeInfo as $name => $value) {
                    if ($name == 'cmsPages') {
                        $cmsPages = [];

                        foreach ($value as $page) {
                            $baseName = $page->getBaseFileName();
                            $pos = strrpos($baseName, '/');

                            $dir = $pos !== false ? substr($baseName, 0, $pos).' / ' : null;
                            $cmsPages[$baseName] = strlen($page->title)
                                ? $dir.$page->title
                                : $baseName;
                        }

                        $value = $cmsPages;
                    }

                    $result[$name] = $value;
                }
            }
        }

        return $result;
    }

    /**
     * Converts the menu item data to an array
     * @return array Returns the menu item data as array
     */
    public function toArray()
    {
        $result = [];

        foreach ($this->fillable as $property) {
            $result[$property] = $this->$property;
        }

        return $result;
    }
}

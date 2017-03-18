<?php namespace RainLab\Pages\Classes;

use Url;
use File;
use Lang;
use Yaml;
use Event;
use Config;
use Request;
use Validator;
use RainLab\Pages\Classes\MenuItem;
use RainLab\Pages\Classes\MenuItemReference;
use Cms\Classes\Theme;
use Cms\Classes\CmsObject;
use Cms\Classes\Controller as CmsController;
use October\Rain\Support\Str;
use October\Rain\Router\Helper as RouterHelper;
use ApplicationException;
use ValidationException;
use SystemException;
use DirectoryIterator;
use Exception;

/**
 * Represents a front-end menu.
 *
 * @package rainlab\pages
 * @author Alexey Bobkov, Samuel Georges
 */
class Menu extends CmsObject
{
    /**
     * @var string The container name associated with the model, eg: pages.
     */
    protected $dirName = 'meta/menus';

    /**
     * @var array Cache store used by parseContent method.
     */
    protected $contentDataCache;

    /**
     * @var array Allowable file extensions.
     */
    protected $allowedExtensions = ['yaml'];

    /**
     * @var string Default file extension.
     */
    protected $defaultExtension = 'yaml';

    /**
     * @var array The attributes that are mass assignable.
     */
    protected $fillable = [
        'content',
        'code',
        'name',
        'itemData'
    ];

    /**
     * @var array List of attribute names which are not considered "settings".
     */
    protected $purgeable = [
        'code',
        'name',
        'itemData'
    ];

    /**
     * Triggered before the menu is saved.
     * @return void
     */
    public function beforeSave()
    {
        $this->content = $this->renderContent();
    }

    /**
     * Validate custom attributes.
     * @return void
     */
    public function beforeValidate()
    {
        if (!strlen($this->code)) {
            throw new ValidationException([
                'code' => Lang::get('rainlab.pages::lang.menu.code_required')
            ]);
        }

        if (!preg_match('/^[0-9a-z\-\_]+$/i', $this->code)) {
            throw new ValidationException([
                'code' => Lang::get('rainlab.pages::lang.menu.invalid_code')
            ]);
        }
    }

    /**
     * Returns the menu code.
     * @return string
     */
    public function getCodeAttribute()
    {
        if (isset($this->attributes['code'])) {
            return $this->attributes['code'];
        }

        $place = strrpos($this->fileName, '.');

        if ($place !== false) {
            return substr($this->fileName, 0, $place);
        }

        return null;
    }

    /**
     * Sets the menu code.
     * @param string $code Specifies the file code.
     * @return \Cms\Classes\CmsObject Returns the object instance.
     */
    public function setCodeAttribute($code)
    {
        $code = trim($code);

        $this->attributes['code'] = $code;

        if (strlen($code)) {
            $this->fileName = $code.'.yaml';
        }

        return $this;
    }

    /**
     * Returns a default value for name attribute.
     * @return string
     */
    public function getNameAttribute()
    {
        if (array_key_exists('name', $this->attributes)) {
            return $this->attributes['name'];
        }

        return $this->attributes['name'] = array_get($this->parseContent(), 'name');
    }

    /**
     * Returns a default value for items attribute.
     * Items are objects of the \RainLab\Pages\Classes\MenuItem class.
     * @return array
     */
    public function getItemsAttribute()
    {
        if (array_key_exists('items', $this->attributes)) {
            return $this->attributes['items'];
        }

        if ($items = array_get($this->parseContent(), 'items')) {
            $itemObjects = MenuItem::initFromArray($items);
        }
        else {
            $itemObjects = [];
        }

        return $this->attributes['items'] = $itemObjects;
    }

    /**
     * Returns a default value for itemData attribute.
     * @return array
     */
    public function getItemDataAttribute()
    {
        if (array_key_exists('itemData', $this->attributes)) {
            return $this->attributes['itemData'];
        }

        return $this->attributes['itemData'] = array_get($this->parseContent(), 'items');
    }

    /**
     * Processes the content attribute to an array of menu data.
     * @return array|null
     */
    protected function parseContent()
    {
        if ($this->contentDataCache !== null) {
            return $this->contentDataCache;
        }

        $parsedData = Yaml::parse($this->content);

        if (!is_array($parsedData)) {
            return null;
        }

        if (!array_key_exists('name', $parsedData)) {
            throw new SystemException(sprintf('The content of the %s file is invalid: the name element is not found.', $fileName));
        }

        return $this->contentDataCache = $parsedData;
    }

    /**
     * Renders the menu data as a content string in YAML format.
     * @return string
     */
    protected function renderContent()
    {
        $contentData = [
            'name'  => $this->name,
            'items' => $this->itemData ? $this->itemData : []
        ];

        return Yaml::render($contentData);
    }

    /**
     * Initializes a cache item.
     * @param array &$item The cached item array.
     */
    public static function initCacheItem(&$item)
    {
        $obj = new static($item);
        $item['name'] = $obj->name;
        $item['items'] = $obj->items;
    }

    /**
     * Returns the menu item references.
     * This function is used on the front-end.
     * @param Cms\Classes\Page $page The current page object.
     * @return array Returns an array of the \RainLab\Pages\Classes\MenuItemReference objects.
     */
    public function generateReferences($page)
    {
        $currentUrl = Request::path();

        if (!strlen($currentUrl)) {
            $currentUrl = '/';
        }

        $currentUrl = Str::lower(Url::to($currentUrl));

        $activeMenuItem = $page->activeMenuItem ?: false;
        $iterator = function($items) use ($currentUrl, &$iterator, $activeMenuItem) {
            $result = [];

            foreach ($items as $item) {
                $parentReference = new MenuItemReference;
                $parentReference->title = $item->title;
                $parentReference->code = $item->code;
                $parentReference->viewBag = $item->viewBag;

                /*
                 * If the item type is URL, assign the reference the item's URL and compare the current URL with the item URL
                 * to determine whether the item is active.
                 */
                if ($item->type == 'url') {
                    $parentReference->url = $item->url;
                    $parentReference->isActive = $currentUrl == Str::lower($item->url) || $activeMenuItem === $item->code;
                }
                else {
                    /*
                     * If the item type is not URL, use the API to request the item type's provider to
                     * return the item URL, subitems and determine whether the item is active.
                     */
                    $apiResult = Event::fire('pages.menuitem.resolveItem', [$item->type, $item, $currentUrl, $this->theme]);
                    if (is_array($apiResult)) {
                        foreach ($apiResult as $itemInfo) {
                            if (!is_array($itemInfo)) {
                                continue;
                            }

                            if (!$item->replace && isset($itemInfo['url'])) {
                                $parentReference->url = $itemInfo['url'];
                                $parentReference->isActive = $itemInfo['isActive'] || $activeMenuItem === $item->code;
                            }

                            if (isset($itemInfo['items'])) {
                                $itemIterator = function($items) use (&$itemIterator, $parentReference) {
                                    $result = [];

                                    foreach ($items as $item) {
                                        $reference = new MenuItemReference;
                                        $reference->title = isset($item['title']) ? $item['title'] : '--no title--';
                                        $reference->url = isset($item['url']) ? $item['url'] : '#';
                                        $reference->isActive = isset($item['isActive']) ? $item['isActive'] : false;
                                        $reference->viewBag = isset($item['viewBag']) ? $item['viewBag'] : [];

                                        if (!strlen($parentReference->url)) {
                                            $parentReference->url = $reference->url;
                                            $parentReference->isActive = $reference->isActive;
                                        }

                                        if (isset($item['items'])) {
                                            $reference->items = $itemIterator($item['items']);
                                        }

                                        $result[] = $reference;
                                    }

                                    return $result;
                                };

                                $parentReference->items = $itemIterator($itemInfo['items']);
                            }
                        }
                    }
                }

                if ($item->items) {
                    $parentReference->items = $iterator($item->items);
                }

                if (!$item->replace) {
                    $result[] = $parentReference;
                }
                else {
                    foreach ($parentReference->items as $subItem) {
                        $result[] = $subItem;
                    }
                }
            }

            return $result;
        };

        $items = $iterator($this->items);

        /*
         * Populate the isChildActive property
         */
        $hasActiveChild = function($items) use (&$hasActiveChild) {
            foreach ($items as $item) {
                if ($item->isActive) {
                    return true;
                }

                $result = $hasActiveChild($item->items);
                if ($result) {
                    return $result;
                }
            }
        };

        $iterator = function($items) use (&$iterator, &$hasActiveChild) {
            foreach ($items as $item) {
                $item->isChildActive = $hasActiveChild($item->items);

                $iterator($item->items);
            }
        };

        $iterator($items);

        return $items;
    }
}

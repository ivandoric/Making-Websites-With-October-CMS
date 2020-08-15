<?php namespace RainLab\Pages\Classes;

use Url;
use Event;
use Request;
use SystemException;
use Cms\Classes\Meta;
use October\Rain\Support\Str;

/**
 * Represents a front-end menu.
 *
 * @package rainlab\pages
 * @author Alexey Bobkov, Samuel Georges
 */
class Menu extends Meta
{
    /**
     * @var string The container name associated with the model, eg: pages.
     */
    protected $dirName = 'meta/menus';

    /**
     * @var array The attributes that are mass assignable.
     */
    protected $fillable = [
        'content',
        'code',
        'name',
        'itemData',
    ];

    /**
     * @var array List of attribute names which are not considered "settings".
     */
    protected $purgeable = [
        'code',
    ];

    /**
     * @var array The rules to be applied to the data.
     */
    public $rules = [
        'code' => 'required|regex:/^[0-9a-z\-\_]+$/i',
    ];

    /**
     * @var array The array of custom error messages.
     */
    public $customMessages = [
        'required' => 'rainlab.pages::lang.menu.code_required',
        'regex'    => 'rainlab.pages::lang.menu.invalid_code',
    ];

    /**
     * Returns the menu code.
     * @return string
     */
    public function getCodeAttribute()
    {
        return $this->getBaseFileName();
    }

    /**
     * Sets the menu code.
     * @param string $code Specifies the file code.
     * @return \Cms\Classes\CmsObject Returns the object instance.
     */
    public function setCodeAttribute($code)
    {
        $code = trim($code);

        if (strlen($code)) {
            $this->fileName = $code.'.yaml';
            $this->attributes = array_merge($this->attributes, ['code' => $code]);
        }

        return $this;
    }

    /**
     * Returns a default value for items attribute.
     * Items are objects of the \RainLab\Pages\Classes\MenuItem class.
     * @return array
     */
    public function getItemsAttribute()
    {
        $items = [];
        if (!empty($this->attributes['items'])) {
            $items = MenuItem::initFromArray($this->attributes['items']);
        }

        return $items;
    }

    /**
     * Store the itemData in the items attribute
     *
     * @param array $data
     * @return void
     */
    public function setItemDataAttribute($data)
    {
        $this->items = $data;
        return $this;
    }

    /**
     * Processes the content attribute to an array of menu data.
     * @return array|null
     */
    protected function parseContent()
    {
        $parsedData = parent::parseContent();

        if (!array_key_exists('name', $parsedData)) {
            throw new SystemException(sprintf('The content of the %s file is invalid: the name element is not found.', $this->fileName));
        }

        return $parsedData;
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
                    $parentReference->isActive = $currentUrl == Str::lower(Url::to($item->url)) || $activeMenuItem === $item->code;
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
                                        $reference->code = isset($item['code']) ? $item['code'] : null;

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

        /*
         * @event pages.menu.referencesGenerated
         * Provides opportunity to dynamically change menu entries right after reference generation.
         *
         * For example you can use it to filter menu entries for user groups from RainLab.User
         * Before doing so you have to add custom field 'group' to menu viewBag using backend.form.extendFields event
         * where the group can be selected by the user. See how to do this here:
         * https://octobercms.com/docs/backend/forms#extend-form-fields
         *
         * Parameter provided is `$items` - a collection of the MenuItemReference objects passed by reference
         *
         * For example to hide entries where group is not 'registered' you can use the following code. It can
         * be used to show different menus for different user groups.
         *
         * Event::listen('pages.menu.referencesGenerated', function (&$items) {
         *     $iterator = function ($menuItems) use (&$iterator, $clusterRepository) {
         *         $result = [];
         *         foreach ($menuItems as $item) {
         *             if (isset($item->viewBag['group']) && $item->viewBag['group'] !== "registered") {
         *                 $item->viewBag['isHidden'] = "1";
         *             }
         *             if ($item->items) {
         *                 $item->items = $iterator($item->items);
         *             }
         *             $result[] = $item;
         *         }
         *         return $result;
         *     };
         *     $items = $iterator($items);
         * });
         */

        Event::fire('pages.menu.referencesGenerated', [&$items]);

        return $items;
    }
}

<?php namespace RainLab\Pages\Classes;

use Cms;
use File;
use Lang;
use Cache;
use Event;
use Route;
use Config;
use Validator;
use RainLab\Pages\Classes\Router;
use RainLab\Pages\Classes\Snippet;
use RainLab\Pages\Classes\PageList;
use Cms\Classes\Theme;
use Cms\Classes\Layout;
use Cms\Classes\Content as ContentBase;
use Cms\Classes\ComponentManager;
use System\Helpers\View as ViewHelper;
use October\Rain\Support\Str;
use October\Rain\Router\Helper as RouterHelper;
use October\Rain\Parse\Bracket as TextParser;
use October\Rain\Parse\Syntax\Parser as SyntaxParser;
use ApplicationException;

/**
 * Represents a static page.
 *
 * @package rainlab\pages
 * @author Alexey Bobkov, Samuel Georges
 */
class Page extends ContentBase
{
    public $implement = [
        '@RainLab.Translate.Behaviors.TranslatablePageUrl',
        '@RainLab.Translate.Behaviors.TranslatableCmsObject'
    ];

    /**
     * @var string The container name associated with the model, eg: pages.
     */
    protected $dirName = 'content/static-pages';

    /**
     * @var bool Wrap code section in PHP tags.
     */
    protected $wrapCode = false;

    /**
     * @var array Properties that can be set with fill()
     */
    protected $fillable = [
        'markup',
        'settings',
        'placeholders',
    ];

    /**
     * @var array List of attribute names which are not considered "settings".
     */
    protected $purgeable = ['parsedMarkup', 'placeholders'];

    /**
     * @var array The rules to be applied to the data.
     */
    public $rules = [
        'title' => 'required',
        'url'   => ['required', 'regex:/^\/[a-z0-9\/_\-\.]*$/i', 'uniqueUrl']
    ];

    /**
     * @var array The array of custom attribute names.
     */
    public $attributeNames = [
        'title' => 'title',
        'url' => 'url',
    ];

    /**
     * @var array Attributes that support translation, if available.
     */
    public $translatable = [
        'code',
        'markup',
        'viewBag[title]',
        'viewBag[meta_title]',
        'viewBag[meta_description]',
    ];

    /**
     * @var string Translation model used for translation, if available.
     */
    public $translatableModel = 'RainLab\Translate\Classes\MLStaticPage';

    /**
     * @var string Contains the page parent file name.
     * This property is used by the page editor internally.
     */
    public $parentFileName;

    protected static $menuTreeCache = null;

    protected $parentCache = null;

    protected $childrenCache = null;

    protected $processedMarkupCache = false;

    protected $processedBlockMarkupCache = [];

    /**
     * Creates an instance of the object and associates it with a CMS theme.
     * @param array $attributes
     */
    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);

        $this->customMessages = [
            'url.regex'      => Lang::get('rainlab.pages::lang.page.invalid_url'),
            'url.unique_url' => Lang::get('rainlab.pages::lang.page.url_not_unique')
        ];
    }

    //
    // CMS Object
    //

    /**
     * Sets the object attributes.
     * @param array $attributes A list of attributes to set.
     */
    public function fill(array $attributes)
    {
        parent::fill($attributes);

        /*
         * When the page is saved, copy setting properties to the view bag.
         * This is required for the back-end editors.
         */
        if (array_key_exists('settings', $attributes) && array_key_exists('viewBag', $attributes['settings'])) {
            $this->getViewBag()->setProperties($attributes['settings']['viewBag']);
            $this->fillViewBagArray();
        }
    }

    /**
     * Returns the attributes used for validation.
     * @return array
     */
    protected function getValidationAttributes()
    {
        return $this->getAttributes() + $this->viewBag;
    }

    /**
     * Validates the object properties.
     * Throws a ValidationException in case of an error.
     */
    public function beforeValidate()
    {
        $pages = Page::listInTheme($this->theme, true);

        Validator::extend('uniqueUrl', function($attribute, $value, $parameters) use ($pages) {
            $value = trim(strtolower($value));

            foreach ($pages as $existingPage) {
                if (
                    $existingPage->getBaseFileName() !== $this->getBaseFileName() &&
                    strtolower($existingPage->getViewBag()->property('url')) == $value
                ) {
                    return false;
                }
            }

            return true;
        });
    }

    /**
     * Triggered before a new object is saved.
     */
    public function beforeCreate()
    {
        $this->fileName = $this->generateFilenameFromCode();
    }

    /**
     * Triggered after a new object is saved.
     */
    public function afterCreate()
    {
        $this->appendToMeta();
    }

    /**
     * Adds this page to the meta index.
     */
    protected function appendToMeta()
    {
        $pageList = new PageList($this->theme);
        $pageList->appendPage($this);
    }

    /*
     * Generate a file name based on the URL
     */
    protected function generateFilenameFromCode()
    {
        $dir = rtrim($this->getFilePath(''), '/');

        $fileName = trim(str_replace('/', '-', $this->getViewBag()->property('url')), '-');
        if (strlen($fileName) > 200) {
            $fileName = substr($fileName, 0, 200);
        }

        if (!strlen($fileName)) {
            $fileName = 'index';
        }

        $curName = trim($fileName).'.htm';
        $counter = 2;

        while (File::exists($dir.'/'.$curName)) {
            $curName = $fileName.'-'.$counter.'.htm';
            $counter++;
        }

        return $curName;
    }

    /**
     * Deletes the object from the disk.
     * Recursively deletes subpages. Returns a list of file names of deleted pages.
     * @return array
     */
    public function delete()
    {
        $result = [];

        /*
         * Delete subpages
         */
        foreach ($this->getChildren() as $subPage) {
            $result = array_merge($result, $subPage->delete());
        }

        /*
         * Remove from meta
         */
        $this->removeFromMeta();

        /*
         * Delete the object
         */
        $result = array_merge($result, [$this->getBaseFileName()]);

        parent::delete();

        return $result;
    }

    /**
     * Removes this page to the meta index.
     */
    protected function removeFromMeta()
    {
        $pageList = new PageList($this->theme);
        $pageList->removeSubtree($this);
    }

    //
    // Public API
    //

    /**
     * Helper that makes a URL for a static page in the active theme.
     *
     * Guide for the page reference:
     * - chairs -> content/static-pages/chairs.htm
     *
     * @param mixed $page Specifies the Content file name.
     * @return string
     */
    public static function url($name)
    {
        if (!$page = static::find($name)) {
            return null;
        }

        $url = array_get($page->attributes, 'viewBag.url');

        return Cms::url($url);
    }
    
    /**
     * Determine the default layout for a new page
     * @param \RainLab\Pages\Classes\Page $parentPage
     */
    public function setDefaultLayout($parentPage)
    {
        // Check parent page for a defined child layout
        if ($parentPage) {
            $layout = Layout::load($this->theme, $parentPage->layout);
            $component = $layout ? $layout->getComponent('staticPage') : null;
            $childLayoutName = $component ? $component->property('childLayout', null) : null;
            if ($childLayoutName) {
                $this->getViewBag()->setProperty('layout', $childLayoutName);
                $this->fillViewBagArray();
                return;
            }
        }
        
        // Check theme layouts for one marked as the default
        foreach (Layout::listInTheme($this->theme) as $layout) {
            $component = $layout->getComponent('staticPage');
            if ($component && $component->property('default', false)) {
                $this->getViewBag()->setProperty('layout', $layout->getBaseFileName());
                $this->fillViewBagArray();
                return;
            }
        }
    }

    //
    // Getters
    //

    /**
     * Returns the parent page that belongs to this one, or null.
     * @return mixed
     */
    public function getParent()
    {
        if ($this->parentCache !== null) {
            return $this->parentCache;
        }

        $pageList = new PageList($this->theme);

        $parent = null;
        if ($fileName = $pageList->getPageParent($this)) {
            $parent = static::load($this->theme, $fileName);
        }

        return $this->parentCache = $parent;
    }

    /**
     * Returns all the child pages that belong to this one.
     * @return array
     */
    public function getChildren()
    {
        if ($this->childrenCache !== null) {
            return $this->childrenCache;
        }

        $children = [];
        $pageList = new PageList($this->theme);

        $subtree = $pageList->getPageSubTree($this);

        foreach ($subtree as $fileName => $subPages) {
            $subPage = static::load($this->theme, $fileName);
            if ($subPage) {
                $children[] = $subPage;
            }
        }

        return $this->childrenCache = $children;
    }

    /**
     * Returns a list of layouts available in the theme.
     * This method is used by the form widget.
     * @return array Returns an array of strings.
     */
    public function getLayoutOptions()
    {
        $result = [];
        $layouts = Layout::listInTheme($this->theme, true);

        foreach ($layouts as $layout) {
            if (!$layout->hasComponent('staticPage')) {
                continue;
            }

            $baseName = $layout->getBaseFileName();
            $result[$baseName] = strlen($layout->description) ? $layout->description : $baseName;
        }

        if (!$result) {
            $result[null] = Lang::get('rainlab.pages::lang.page.layouts_not_found');
        }

        return $result;
    }

    /**
     * Looks up the Layout Cms object for this page.
     * @return Cms\Classes\Layout
     */
    public function getLayoutObject()
    {
        $viewBag = $this->getViewBag();
        $layout = $viewBag->property('layout');

        if (!$layout) {
            $layouts = $this->getLayoutOptions();
            $layout = count($layouts) ? array_keys($layouts)[0] : null;
        }

        if (!$layout) {
            return null;
        }

        $layout = Layout::load($this->theme, $layout);
        if (!$layout) {
            return null;
        }

        return $layout;
    }

    /**
     * Returns the Twig content string
     */
    public function getTwigContent()
    {
        return $this->code;
    }

    //
    // Syntax field processing
    //

    public function listLayoutSyntaxFields()
    {
        if (!$layout = $this->getLayoutObject()) {
            return [];
        }

        $syntax = SyntaxParser::parse($layout->markup, ['tagPrefix' => 'page:']);
        $result = $syntax->toEditor();

        return $result;
    }

    //
    // Placeholder processing
    //

    /**
     * Returns information about placeholders defined in the page layout.
     * @return array Returns an associative array of the placeholder name and codes.
     */
    public function listLayoutPlaceholders()
    {
        if (!$layout = $this->getLayoutObject()) {
            return [];
        }

        $result = [];
        $bodyNode = $layout->getTwigNodeTree()->getNode('body')->getNode(0);
        $nodes = $this->flattenTwigNode($bodyNode);

        foreach ($nodes as $node) {
            if (!$node instanceof \Cms\Twig\PlaceholderNode) {
                continue;
            }

            $title = $node->hasAttribute('title') ? trim($node->getAttribute('title')) : null;
            if (!strlen($title)) {
                $title = $node->getAttribute('name');
            }

            $type = $node->hasAttribute('type') ? trim($node->getAttribute('type')) : null;
            $ignore = $node->hasAttribute('ignore') ? trim($node->getAttribute('ignore')) : false;

            $placeholderInfo = [
                'title'  => $title,
                'type'   => $type ?: 'html',
                'ignore' => $ignore
            ];

            $result[$node->getAttribute('name')] = $placeholderInfo;
        }

        return $result;
    }

    /**
     * Recursively flattens a twig node and children
     * @param $node
     * @return array A flat array of twig nodes
     */
    protected function flattenTwigNode($node)
    {
        $result = [];
        if (!$node instanceof \Twig_Node) {
            return $result;
        }

        foreach ($node as $subNode) {
            $flatNodes = $this->flattenTwigNode($subNode);
            $result = array_merge($result, [$subNode], $flatNodes);
        }

        return $result;
    }

    /**
     * Parses the page placeholder {% put %} tags and extracts the placeholder values.
     * @return array Returns an associative array of the placeholder names and values.
     */
    public function getPlaceholdersAttribute()
    {
        if (!strlen($this->code)) {
            return [];
        }

        if ($placeholders = array_get($this->attributes, 'placeholders')) {
            return $placeholders;
        }

        $bodyNode = $this->getTwigNodeTree($this->code)->getNode('body')->getNode(0);
        if ($bodyNode instanceof \Cms\Twig\PutNode) {
            $bodyNode = [$bodyNode];
        }

        $result = [];
        foreach ($bodyNode as $node) {
            if (!$node instanceof \Cms\Twig\PutNode) {
                continue;
            }

            $bodyNode = $node->getNode('body');
            $result[$node->getAttribute('name')] = trim($bodyNode->getAttribute('data'));
        }

        $this->attributes['placeholders'] = $result;

        return $result;
    }

    /**
     * Takes an array of placeholder data (key: code, value: content) and renders
     * it as a single string of Twig markup against the "code" attribute.
     * @param array  $value
     * @return void
     */
    public function setPlaceholdersAttribute($value)
    {
        if (!is_array($value)) {
            return;
        }

        // Prune any attempt at setting a placeholder that
        // is not actually defined by this pages layout.
        $placeholders = array_intersect_key($value, $this->listLayoutPlaceholders());

        $result = '';

        foreach ($placeholders as $code => $content) {
            if (!strlen(trim($content))) {
                continue;
            }

            $result .= '{% put '.$code.' %}'.PHP_EOL;
            $result .= $content.PHP_EOL;
            $result .= '{% endput %}'.PHP_EOL;
            $result .= PHP_EOL;
        }

        $this->attributes['code'] = trim($result);
        $this->attributes['placeholders'] = $placeholders;
    }

    public function getProcessedMarkup()
    {
        if ($this->processedMarkupCache !== false) {
            return $this->processedMarkupCache;
        }

        /*
         * Process snippets
         */
        $markup = Snippet::processPageMarkup(
            $this->getFileName(),
            $this->theme,
            $this->markup
        );

        /*
         * Inject global view variables
         */
        $globalVars = ViewHelper::getGlobalVars();
        if (!empty($globalVars)) {
            $markup = TextParser::parse($markup, $globalVars);
        }

        return $this->processedMarkupCache = $markup;
    }

    public function getProcessedPlaceholderMarkup($placeholderName, $placeholderContents)
    {
        if (array_key_exists($placeholderName, $this->processedBlockMarkupCache)) {
            return $this->processedBlockMarkupCache[$placeholderName];
        }

        /*
         * Process snippets
         */
        $markup = Snippet::processPageMarkup(
            $this->getFileName().md5($placeholderName),
            $this->theme,
            $placeholderContents
        );

        /*
         * Inject global view variables
         */
        $globalVars = ViewHelper::getGlobalVars();
        if (!empty($globalVars)) {
            $markup = TextParser::parse($markup, $globalVars);
        }

        return $this->processedBlockMarkupCache[$placeholderName] = $markup;
    }

    //
    // Snippets
    //

    /**
     * Initializes CMS components associated with the page.
     */
    public function initCmsComponents($cmsController)
    {
        $snippetComponents = Snippet::listPageComponents(
            $this->getFileName(),
            $this->theme,
            $this->markup.$this->code
        );

        $componentManager = ComponentManager::instance();
        foreach ($snippetComponents as $componentInfo) {
            // Register components for snippet-based components
            // if they're not defined yet. This is required because
            // not all snippet components are registered as components,
            // but it's safe to register them in render-time.

            if (!$componentManager->hasComponent($componentInfo['class'])) {
                $componentManager->registerComponent($componentInfo['class'], $componentInfo['alias']);
            }

            $cmsController->addComponent(
                $componentInfo['class'],
                $componentInfo['alias'],
                $componentInfo['properties']
            );
        }
    }

    //
    // Static Menu API
    //

    /**
     * Returns a cache key for this record.
     */
    protected static function getMenuCacheKey($theme)
    {
        $key = crc32($theme->getPath()).'static-page-menu';
        Event::fire('pages.page.getMenuCacheKey', [&$key]);
        return $key;
    }

    /**
     * Returns whether the specified URLs are equal.
     */
    protected static function urlsAreEqual($url, $other)
    {
        return rawurldecode($url) === rawurldecode($other);
    }

    /**
     * Clears the menu item cache
     * @param \Cms\Classes\Theme $theme Specifies the current theme.
     */
    public static function clearMenuCache($theme)
    {
        Cache::forget(self::getMenuCacheKey($theme));
    }

    /**
     * Handler for the pages.menuitem.getTypeInfo event.
     * Returns a menu item type information. The type information is returned as array
     * with the following elements:
     * - references - a list of the item type reference options. The options are returned in the
     *   ["key"] => "title" format for options that don't have sub-options, and in the format
     *   ["key"] => ["title"=>"Option title", "items"=>[...]] for options that have sub-options. Optional,
     *   required only if the menu item type requires references.
     * - nesting - Boolean value indicating whether the item type supports nested items. Optional,
     *   false if omitted.
     * - dynamicItems - Boolean value indicating whether the item type could generate new menu items.
     *   Optional, false if omitted.
     * - cmsPages - a list of CMS pages (objects of the Cms\Classes\Page class), if the item type requires a CMS page reference to 
     *   resolve the item URL.
     * @param string $type Specifies the menu item type
     * @return array Returns an array
     */
    public static function getMenuTypeInfo($type)
    {
        if ($type == 'all-static-pages') {
            return [
                'dynamicItems' => true
            ];
        }

        if ($type == 'static-page') {
            return [
                'references'   => self::listStaticPageMenuOptions(),
                'nesting'      => true,
                'dynamicItems' => true
            ];
        }
    }

    /**
     * Handler for the pages.menuitem.resolveItem event.
     * Returns information about a menu item. The result is an array
     * with the following keys:
     * - url - the menu item URL. Not required for menu item types that return all available records.
     *   The URL should be returned relative to the website root and include the subdirectory, if any.
     *   Use the Cms::url() helper to generate the URLs.
     * - isActive - determines whether the menu item is active. Not required for menu item types that 
     *   return all available records.
     * - items - an array of arrays with the same keys (url, isActive, items) + the title key. 
     *   The items array should be added only if the $item's $nesting property value is TRUE.
     * @param \RainLab\Pages\Classes\MenuItem $item Specifies the menu item.
     * @param \Cms\Classes\Theme $theme Specifies the current theme.
     * @param string $url Specifies the current page URL, normalized, in lower case
     * The URL is specified relative to the website root, it includes the subdirectory name, if any.
     * @return mixed Returns an array. Returns null if the item cannot be resolved.
     */
    public static function resolveMenuItem($item, $url, $theme)
    {
        $tree = self::buildMenuTree($theme);

        if ($item->type == 'static-page' && !isset($tree[$item->reference])) {
            return;
        }

        $result = [];

        if ($item->type == 'static-page') {
            $pageInfo = $tree[$item->reference];
            $result['url'] = Cms::url($pageInfo['url']);
            $result['mtime'] = $pageInfo['mtime'];
            $result['isActive'] = self::urlsAreEqual($result['url'], $url);
        }

        if ($item->nesting || $item->type == 'all-static-pages') {
            $iterator = function($items) use (&$iterator, &$tree, $url) {
                $branch = [];

                foreach ($items as $itemName) {
                    if (!isset($tree[$itemName])) {
                        continue;
                    }

                    $itemInfo = $tree[$itemName];

                    if ($itemInfo['navigation_hidden']) {
                        continue;
                    }

                    $branchItem = [];
                    $branchItem['url'] = Cms::url($itemInfo['url']);
                    $branchItem['isActive'] = self::urlsAreEqual($branchItem['url'], $url);
                    $branchItem['title'] = $itemInfo['title'];
                    $branchItem['mtime'] = $itemInfo['mtime'];

                    if ($itemInfo['items']) {
                        $branchItem['items'] = $iterator($itemInfo['items']);
                    }

                    $branch[] = $branchItem;
                }

                return $branch;
            };

            $result['items'] = $iterator($item->type == 'static-page' ? $pageInfo['items'] : $tree['--root-pages--']);
        }

        return $result;
    }

    /**
     * Handler for the backend.richeditor.getTypeInfo event.
     * Returns a menu item type information. The type information is returned as array
     * @param string $type Specifies the page link type
     * @return array
     */
    public static function getRichEditorTypeInfo($type)
    {
        if ($type == 'static-page') {

            $pages = self::listStaticPageMenuOptions();

            $iterator = function($pages) use (&$iterator) {
                $result = [];
                foreach ($pages as $pageFile => $page) {
                    $url = self::url($pageFile);

                    if (is_array($page)) {
                        $result[$url] = [
                            'title' => array_get($page, 'title', []),
                            'links' => $iterator(array_get($page, 'items', []))
                        ];
                    }
                    else {
                        $result[$url] = $page;
                    }
                }

                return $result;
            };

            return $iterator($pages);
        }

        return [];
    }

    /**
     * Builds and caches a menu item tree.
     * This method is used internally for menu items and breadcrumbs.
     * @param \Cms\Classes\Theme $theme Specifies the current theme.
     * @return array Returns an array containing the page information
     */
    public static function buildMenuTree($theme)
    {
        if (self::$menuTreeCache !== null) {
            return self::$menuTreeCache;
        }

        $key = self::getMenuCacheKey($theme);

        $cached = Cache::get($key, false);
        $unserialized = $cached ? @unserialize($cached) : false;

        if ($unserialized !== false) {
            return self::$menuTreeCache = $unserialized;
        }

        $menuTree = [
            '--root-pages--' => []
        ];

        $iterator = function($items, $parent, $level) use (&$menuTree, &$iterator) {
            $result = [];

            foreach ($items as $item) {
                $viewBag = $item->page->viewBag;
                $pageCode = $item->page->getBaseFileName();
                $pageUrl = Str::lower(RouterHelper::normalizeUrl(array_get($viewBag, 'url')));

                $itemData = [
                    'url'    => $pageUrl,
                    'title'  => array_get($viewBag, 'title'),
                    'mtime'  => $item->page->mtime,
                    'items'  => $iterator($item->subpages, $pageCode, $level+1),
                    'parent' => $parent,
                    'navigation_hidden' => array_get($viewBag, 'navigation_hidden')
                ];

                if ($level == 0) {
                    $menuTree['--root-pages--'][] = $pageCode;
                }

                $result[] = $pageCode;
                $menuTree[$pageCode] = $itemData;
            }

            return $result;
        };

        $pageList = new PageList($theme);
        $iterator($pageList->getPageTree(), null, 0);

        self::$menuTreeCache = $menuTree;
        Cache::put($key, serialize($menuTree), Config::get('cms.parsedPageCacheTTL', 10));

        return self::$menuTreeCache;
    }

    /**
     * Returns a list of options for the Reference drop-down menu in the
     * menu item configuration form, when the Static Page item type is selected.
     * @return array Returns an array
     */
    protected static function listStaticPageMenuOptions()
    {
        $theme = Theme::getEditTheme();

        $pageList = new PageList($theme);
        $pageTree = $pageList->getPageTree(true);

        $iterator = function($pages) use (&$iterator) {
            $result = [];

            foreach ($pages as $pageInfo) {
                $pageName = $pageInfo->page->getViewBag()->property('title');
                $fileName = $pageInfo->page->getBaseFileName();

                if (!$pageInfo->subpages) {
                    $result[$fileName] = $pageName;
                }
                else {
                    $result[$fileName] = [
                        'title' => $pageName,
                        'items' => $iterator($pageInfo->subpages)
                    ];
                }
            }

            return $result;
        };

        return $iterator($pageTree);
    }
}

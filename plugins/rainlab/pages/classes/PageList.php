<?php namespace RainLab\Pages\Classes;

use Yaml;
use Lang;
use File;
use ApplicationException;
use RainLab\Pages\Classes\Page;
use SystemException;
use DirectoryIterator;

/**
 * The page list class reads and manages the static page hierarchy.
 *
 * @package rainlab\pages
 * @author Alexey Bobkov, Samuel Georges
 */
class PageList
{
    protected $theme;

    protected static $configCache = false;

    /**
     * Creates the page list object.
     * @param \Cms\Classes\Theme $theme Specifies a parent theme.
     */
    public function __construct($theme)
    {
        $this->theme = $theme;
    }

    /**
     * Returns a list of static pages in the specified theme.
     * This method is used internally by the system.
     * @param boolean $skipCache Indicates if objects should be reloaded from the disk bypassing the cache.
     * @return array Returns an array of static pages.
     */
    public function listPages($skipCache = false)
    {
        return Page::listInTheme($this->theme, $skipCache);
    }

    /**
     * Returns a list of top-level pages with subpages.
     * The method uses the theme's meta/static-pages.yaml file to build the hierarchy. The pages are returned
     * in the order defined in the YAML file. The result of the method is used for building the back-end UI
     * and for generating the menus.
     * @param boolean $skipCache Indicates if objects should be reloaded from the disk bypassing the cache.
     * @return array Returns a nested array of objects: object('page': $pageObj, 'subpages'=>[...])
     */
    public function getPageTree($skipCache = false)
    {
        $pages = $this->listPages($skipCache);
        $config = $this->getPagesConfig();

        $iterator = function($configPages) use (&$iterator, &$pages) {
            $result = [];

            foreach ($configPages as $fileName => $subpages) {
                $pageObject = null;
                foreach ($pages as $page) {
                    if ($page->getBaseFileName() == $fileName) {
                        $pageObject = $page;
                        break;
                    }
                }

                if ($pageObject === null) {
                    continue;
                }

                $result[] = (object)[
                    'page'     => $pageObject,
                    'subpages' => $iterator($subpages)
                ];
            }

            return $result;
        };

        return $iterator($config['static-pages']);
    }

    /**
     * Returns the parent name of the specified page.
     * @param \Cms\Classes\Page $page Specifies a page object.
     * @param string Returns the parent page name.
     */
    public function getPageParent($page)
    {
        $pagesConfig = $this->getPagesConfig();
        $requestedFileName = $page->getBaseFileName();

        $parent = null;

        $iterator = function($configPages) use (&$iterator, &$parent, $requestedFileName) {
            foreach ($configPages as $fileName => $subpages) {
                if ($fileName == $requestedFileName) {
                    return true;
                }

                if ($iterator($subpages) == true && is_null($parent)) {

                    $parent = $fileName;

                    return true;
                }
            }
        };

        $iterator($pagesConfig['static-pages']);

        return $parent;
    }

    /**
     * Returns a part of the page hierarchy starting from the specified page.
     * @param \Cms\Classes\Page $page Specifies a page object.
     * @param array Returns a nested array of page names.
     */
    public function getPageSubTree($page)
    {
        $pagesConfig = $this->getPagesConfig();
        $requestedFileName = $page->getBaseFileName();

        $subTree = [];

        $iterator = function($configPages) use (&$iterator, &$subTree, $requestedFileName) {
            if (is_array($configPages)) {
                foreach ($configPages as $fileName => $subpages) {
                    if ($fileName == $requestedFileName) {
                        $subTree = $subpages;

                        return true;
                    }

                    if ($iterator($subpages) === true) {
                        return true;
                    }
                }
            }
        };

        $iterator($pagesConfig['static-pages']);

        return $subTree;
    }

    /**
     * Updates the page hierarchy structure in the theme's meta/static-pages.yaml file.
     * @param array $structure A nested associative array representing the page structure
     */
    public function updateStructure($structure)
    {
        $originalData = $this->getPagesConfig();
        $originalData['static-pages'] = $structure;

        $yamlData = Yaml::render($originalData);

        $filePath = $this->getConfigFilePath();
        $dirPath = dirname($filePath);

        if (!file_exists($dirPath) || !is_dir($dirPath)) {
            if (!File::makeDirectory($dirPath, 0777, true, true)) {
                throw new ApplicationException(Lang::get('cms::lang.cms_object.error_creating_directory', ['name' => $dirPath]));
            }
        }

        if (@File::put($filePath, $yamlData) === false) {
            throw new ApplicationException(Lang::get('cms::lang.cms_object.error_saving', ['name' => $filePath]));
        }
    }

    /**
     * Appends page to the page hierarchy.
     * The page can be added to the end of the hierarchy or as a subpage to any existing page.
     */
    public function appendPage($page)
    {
        $parent = $page->parentFileName;

        $originalData = $this->getPagesConfig();
        $structure = $originalData['static-pages'];

        if (!strlen($parent)) {
            $structure[$page->getBaseFileName()] = [];
        }
        else {
            $iterator = function(&$configPages) use (&$iterator, $parent, $page) {
                foreach ($configPages as $fileName => &$subpages) {
                    if ($fileName == $parent) {
                        $subpages[$page->getBaseFileName()] = [];

                        return true;
                    }

                    if ($iterator($subpages) == true)
                        return true;
                }
            };

            $iterator($structure);
        }

        $this->updateStructure($structure);
    }

    /**
     * Removes a part of the page hierarchy starting from the specified page.
     * @param \Cms\Classes\Page $page Specifies a page object.
     */
    public function removeSubtree($page)
    {
        $pagesConfig = $this->getPagesConfig();
        $requestedFileName = $page->getBaseFileName();

        $tree = [];

        $iterator = function($configPages) use (&$iterator, &$pages, $requestedFileName) {
            $result = [];

            foreach ($configPages as $fileName => $subpages) {
                if ($requestedFileName != $fileName) {
                    $result[$fileName] = $iterator($subpages);
                }
            }

            return $result;
        };

        $updatedStructure = $iterator($pagesConfig['static-pages']);
        $this->updateStructure($updatedStructure);
    }

    /**
     * Returns the parsed meta/static-pages.yaml file contents.
     * @return mixed
     */
    protected function getPagesConfig()
    {
        if (self::$configCache !== false) {
            return self::$configCache;
        }

        $filePath = $this->getConfigFilePath();

        if (!file_exists($filePath)) {
            return self::$configCache = ['static-pages' => []];
        }

        $config = Yaml::parse(File::get($filePath));
        if (!array_key_exists('static-pages', $config)) {
            throw new SystemException('The content of the theme meta/static-pages.yaml file is invalid: the "static-pages" root element is not found.');
        }

        return self::$configCache = $config;
    }

    /**
     * Returns an absolute path to the meta/static-pages.yaml file.
     * @return string
     */
    protected function getConfigFilePath()
    {
        return $this->theme->getPath().'/meta/static-pages.yaml';
    }
}

<?php namespace RainLab\Pages\Components;

use Request;
use Cms\Classes\Theme;
use Cms\Classes\ComponentBase;
use RainLab\Pages\Classes\Router;

/**
 * The static page component.
 *
 * @package rainlab\pages
 * @author Alexey Bobkov, Samuel Georges
 */
class StaticPage extends ComponentBase
{
    /**
     * @var \RainLab\Pages\Classes\Page A reference to the static page object
     */
    public $pageObject;

    /**
     * @var string The static page title
     */
    public $title;

    /**
     * @var array Extra data added by syntax fields.
     */
    public $extraData = [];

    /**
     * @var string Content cache.
     */
    protected $contentCached = false;

    public function componentDetails()
    {
        return [
            'name'        => 'rainlab.pages::lang.component.static_page_name',
            'description' => 'rainlab.pages::lang.component.static_page_description'
        ];
    }
    
    public function defineProperties()
    {
        return [
            'useContent' => [
                'title'             => 'rainlab.pages::lang.component.static_page_use_content_name',
                'description'       => 'rainlab.pages::lang.component.static_page_use_content_description',
                'default'           => 1,
                'type'              => 'checkbox',
                'showExternalParam' => false
            ],
            'default' => [
                'title'             => 'rainlab.pages::lang.component.static_page_default_name',
                'description'       => 'rainlab.pages::lang.component.static_page_default_description',
                'default'           => 0,
                'type'              => 'checkbox',
                'showExternalParam' => false
            ],
            'childLayout' => [
                'title'             => 'rainlab.pages::lang.component.static_page_child_layout_name',
                'description'       => 'rainlab.pages::lang.component.static_page_child_layout_description',
                'type'              => 'string',
                'showExternalParam' => false
            ]
            
        ];
    }

    public function onRun()
    {
        $url = $this->getRouter()->getUrl();

        if (!strlen($url)) {
            $url = '/';
        }

        $router = new Router(Theme::getActiveTheme());
        $this->pageObject = $this->page['page'] = $router->findByUrl($url);

        if ($this->pageObject) {
            $this->title = $this->page['title'] = array_get($this->pageObject->viewBag, 'title');
            $this->extraData = $this->page['extraData'] = $this->defineExtraData();
        }
    }

    public function page()
    {
        return $this->pageObject;
    }

    public function parent()
    {
        return $this->pageObject ? $this->pageObject->getParent() : null;
    }

    public function children()
    {
        return $this->pageObject ? $this->pageObject->getChildren() : null;
    }

    public function content()
    {
        // Evaluate the content property only when it's requested in the
        // render time. Calling the page's getProcessedMarkup() method in the
        // onRun() handler is too early as it triggers rendering component-based
        // snippets defined on the static page too early in the page life cycle. -ab

        if ($this->contentCached !== false) {
            return $this->contentCached;
        }

        if ($this->pageObject) {
            return $this->contentCached = $this->pageObject->getProcessedMarkup();
        }

        $this->contentCached = '';
    }

    /**
     * Find foreign view bag values and add them to
     * the component and page vars.
     */
    protected function defineExtraData()
    {
        $standardProperties = [
            'title',
            'url',
            'layout',
            'is_hidden',
            'navigation_hidden',
            'meta_title',
            'meta_description'
        ];

        $extraData = array_diff_key(
            $this->pageObject->viewBag,
            array_flip($standardProperties)
        );

        foreach ($extraData as $key => $value) {
            $this->page[$key] = $value;
        }

        return $extraData;
    }

    /**
     * Implements the getter functionality.
     * @param  string  $name
     * @return void
     */
    public function __get($name)
    {
        if (array_key_exists($name, $this->extraData)) {
            return $this->extraData[$name];
        }

        return null;
    }

    /**
     * Determine if an attribute exists on the object.
     * @param  string  $key
     * @return void
     */
    public function __isset($key)
    {
        if (array_key_exists($key, $this->extraData)) {
            return true;
        }

        return false;
    }
}

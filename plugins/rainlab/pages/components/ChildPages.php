<?php namespace RainLab\Pages\Components;

use Cms\Classes\ComponentBase;

class ChildPages extends ComponentBase
{
    /**
     * @var \RainLab\Pages\Components\StaticPage A reference to the static page component
     */
    protected $staticPageComponent;

    /**
     * @var array Array of \RainLab\Pages\Classes\Page references to the child static page objects for the current page
     */
    protected $childPages;

    /**
     * @var array Child pages data
     * [
     *      'url' => '',
     *      'title' => '',
     *      'page' => \RainLab\Pages\Classes\Page,
     *      'viewBag' => array,
     *      'is_hidden' => bool,
     *      'navigation_hidden' => bool,
     * ]
     */
    public $pages = [];

    public function componentDetails()
    {
        return [
            'name'        => 'rainlab.pages::lang.component.child_pages_name',
            'description' => 'rainlab.pages::lang.component.child_pages_description'
        ];
    }

    public function onRun()
    {
        // Check if the staticPage component is attached to the rendering template
        $this->staticPageComponent = $this->findComponentByName('staticPage');
        if ($this->staticPageComponent->pageObject) {
            $this->childPages = $this->staticPageComponent->pageObject->getChildren();

            if ($this->childPages) {
                foreach ($this->childPages as $childPage) {
                    $viewBag = $childPage->settings['components']['viewBag'];
                    $this->pages = array_merge($this->pages, [[
                        'url'                => @$viewBag['url'],
                        'title'              => @$viewBag['title'],
                        'page'               => $childPage,
                        'viewBag'            => $viewBag,
                        'is_hidden'          => @$viewBag['is_hidden'],
                        'navigation_hidden'  => @$viewBag['navigation_hidden'],
                    ]]);
                }
            }
        }
    }
}
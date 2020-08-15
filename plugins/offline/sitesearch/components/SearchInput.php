<?php namespace OFFLINE\SiteSearch\Components;

use Cms\Classes\Page;
use DomainException;
use OFFLINE\SiteSearch\Classes\ResultCollection;
use OFFLINE\SiteSearch\Classes\SearchService;
use Request;

class SearchInput extends BaseComponent
{
    /**
     * The user's search query.
     *
     * @var string
     */
    public $query;

    /**
     * Whether or not to use the autocomplete feature.
     *
     * @var boolean
     */
    public $useAutoComplete = true;

    /**
     * Display no more than this many autocomplete results.
     *
     * @var int
     */
    public $autoCompleteResultCount = 5;
    /**
     * Whether or not to display a provider badge for reach result.
     *
     * @var int
     */
    public $showProviderBadge;
    /**
     * The "Show all results" link will point to this page.
     *
     * @var int
     */
    public $searchPage;

    /**
     * The component's details.
     *
     * @return array
     */
    public function componentDetails()
    {
        return [
            'name'        => 'offline.sitesearch::lang.searchInput.title',
            'description' => 'offline.sitesearch::lang.searchInput.description',
        ];
    }

    /**
     * The component's properties.
     *
     * @return array
     */
    public function defineProperties()
    {
        return [
            'useAutoComplete' => [
                'title'       => 'offline.sitesearch::lang.searchInput.properties.use_auto_complete.title',
                'type'        => 'checkbox',
                'default'     => 1,
            ],
            'autoCompleteResultCount' => [
                'title'             => 'offline.sitesearch::lang.searchInput.properties.auto_complete_result_count.title',
                'type'              => 'string',
                'validationPattern' => '^[0-9]+$',
                'validationMessage' => 'Please enter only numbers',
                'default'           => '5',
            ],
            'showProviderBadge' => [
                'title'       => 'offline.sitesearch::lang.searchResults.properties.provider_badge.title',
                'description' => 'offline.sitesearch::lang.searchResults.properties.provider_badge.description',
                'type'        => 'checkbox',
                'default'     => 1,
            ],
            'searchPage'              => [
                'title'       => 'offline.sitesearch::lang.searchInput.properties.search_page.title',
                'description' => 'offline.sitesearch::lang.searchInput.properties.search_page.description',
                'type'        => 'dropdown',
            ],
        ];
    }

    /**
     * Returns all available pages.
     *
     * @return array
     */
    public function getSearchPageOptions()
    {
        $pages = Page::all();

        $options = $pages->pluck('title', 'fileName')->toArray();

        return ['' => trans('offline.sitesearch::lang.searchInput.properties.search_page.null_value'),] + $options;
    }

    /**
     * Triggered on usual page load.
     *
     * @return void
     */
    public function onRun()
    {
        $this->setVar('useAutoComplete');
        $this->setVar('searchPage');
        $this->setVar('query', input('q', ''));
    }

    /**
     * Triggered by October's AJAX framework when
     * the users enters a query.
     *
     * @return array
     * @throws \DomainException
     */
    public function onType()
    {
        $this->setVar('query', post('q', ''));
        $this->setVar('useAutoComplete');
        $this->setVar('searchPage');
        $this->setVar('autoCompleteResultCount');
        $this->setVar('showProviderBadge');

        $results = $this->search();

        $this->setVar('results', $results);
    }

    /**
     * Fetch the search results.
     *
     * @return ResultCollection
     * @throws DomainException
     */
    protected function search()
    {
        $search = new SearchService($this->query, $this->controller);

        return $search->results();
    }
}

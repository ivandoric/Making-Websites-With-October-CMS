<?php namespace OFFLINE\SiteSearch\Components;

use DomainException;
use Event;
use Illuminate\Pagination\LengthAwarePaginator;
use OFFLINE\SiteSearch\Classes\ResultCollection;
use OFFLINE\SiteSearch\Classes\SearchService;
use Request;
use Url;

/**
 * SearchResults Component
 * @package OFFLINE\SiteSearch\Components
 */
class SearchResults extends BaseComponent
{
    /**
     * The message to display when no results are returned.
     *
     * @var string
     */
    public $noResultsMessage;
    /**
     * What link text to display below each result.
     *
     * @var string
     */
    public $visitPageMessage;
    /**
     * Whether or not to display a provider badge for reach result.
     *
     * @var int
     */
    public $showProviderBadge;
    /**
     * The user's search query.
     *
     * @var string
     */
    public $query;
    /**
     * @var int
     */
    protected $resultsPerPage = 10;
    /**
     * The collection of all results.
     *
     * @var ResultCollection
     */
    public $resultCollection;
    /**
     * The current page number.
     *
     * @var int
     */
    protected $pageNumber;
    /**
     * The developer's forced search query.
     * This query is used instead of $query if set.
     *
     * @var string
     */
    protected $forcedQuery;

    /**
     * The component's details.
     *
     * @return array
     */
    public function componentDetails()
    {
        return [
            'name'        => 'offline.sitesearch::lang.searchResults.title',
            'description' => 'offline.sitesearch::lang.searchResults.description',
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
            'resultsPerPage'    => [
                'title'             => 'offline.sitesearch::lang.searchResults.properties.results_per_page.title',
                'type'              => 'string',
                'validationPattern' => '^[0-9]+$',
                'validationMessage' => 'Please enter only numbers',
                'default'           => '10',
            ],
            'showProviderBadge' => [
                'title'       => 'offline.sitesearch::lang.searchResults.properties.provider_badge.title',
                'description' => 'offline.sitesearch::lang.searchResults.properties.provider_badge.description',
                'type'        => 'checkbox',
                'default'     => 1,
            ],
            'noResultsMessage'  => [
                'title'             => 'offline.sitesearch::lang.searchResults.properties.no_results.title',
                'description'       => 'offline.sitesearch::lang.searchResults.properties.no_results.description',
                'type'              => 'string',
                'default'           => 'Your search returned no results.',
                'showExternalParam' => false,
            ],
            'visitPageMessage'  => [
                'title'             => 'offline.sitesearch::lang.searchResults.properties.visit_page.title',
                'description'       => 'offline.sitesearch::lang.searchResults.properties.visit_page.description',
                'type'              => 'string',
                'default'           => 'Visit page',
                'showExternalParam' => false,
            ],
        ];
    }

    /**
     * Component setup.
     *
     * @return void
     */
    public function onRun()
    {
        $this->prepareVars();

        $this->resultCollection = $this->search();
    }


    /**
     * Force a query to be used.
     *
     * This can be useful if you want to process the
     * "q" GET parameter yourself and then use a modified query
     * for the search.
     *
     * @param $query
     */
    public function forceQuery($query)
    {
        $this->forcedQuery = $query;
    }

    /**
     * Setup all needed variables.
     *
     * @return void
     */
    protected function prepareVars()
    {
        $query = $this->forcedQuery ? $this->forcedQuery : Request::get('q', '');

        $this->setVar('pageNumber', Request::get('page', 1));
        $this->setVar('query', $query);
        $this->setVar('noResultsMessage');
        $this->setVar('visitPageMessage');
        $this->setVar('showProviderBadge');
        $this->setVar('resultsPerPage');
    }

    /**
     * Fetch the search results.
     *
     * @throws DomainException
     * @return ResultCollection
     */
    protected function search()
    {
        $search = new SearchService($this->query, $this->controller);

        return $search->results();
    }

    /**
     * Return the paginated results.
     *
     * @return Paginator
     */
    public function results()
    {
        $paginator = new LengthAwarePaginator(
            $this->getPaginatorSlice($this->resultCollection),
            $this->resultCollection->count(),
            $this->resultsPerPage,
            $this->pageNumber
        );

        $pageUrl = Url::to(Request::url());

        return $paginator->setPath($pageUrl)->appends('q', $this->query);
    }

    /**
     * Return number of last page.
     *
     * @return int
     */
    public function lastPage()
    {
        return (int)ceil($this->resultCollection->count() / $this->resultsPerPage);
    }

    /**
     * Returns the slice for the current page + 1
     * extra element to make the pagination work.
     *
     * @param $results
     *
     * @return ResultCollection
     */
    protected function getPaginatorSlice($results)
    {
        return $results->slice(($this->pageNumber - 1) * $this->resultsPerPage, $this->resultsPerPage);
    }
}

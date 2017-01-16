<?php namespace OFFLINE\SiteSearch\Components;

use Cms\Classes\ComponentBase;
use DomainException;
use Illuminate\Pagination\LengthAwarePaginator;
use OFFLINE\SiteSearch\Classes\Providers\ArrizalaminPortfolioResultsProvider;
use OFFLINE\SiteSearch\Classes\Providers\CmsPagesResultsProvider;
use OFFLINE\SiteSearch\Classes\Providers\FeeglewebOctoshopProductsResultsProvider;
use OFFLINE\SiteSearch\Classes\Providers\GenericResultsProvider;
use OFFLINE\SiteSearch\Classes\Providers\IndikatorNewsResultsProvider;
use OFFLINE\SiteSearch\Classes\Providers\JiriJKShopResultsProvider;
use OFFLINE\SiteSearch\Classes\Providers\OfflineSnipcartShopResultsProvider;
use OFFLINE\SiteSearch\Classes\Providers\RadiantWebProBlogResultsProvider;
use OFFLINE\SiteSearch\Classes\Providers\RainlabBlogResultsProvider;
use OFFLINE\SiteSearch\Classes\Providers\RainlabPagesResultsProvider;
use OFFLINE\SiteSearch\Classes\Providers\ResponsivShowcaseResultsProvider;
use OFFLINE\SiteSearch\Classes\Providers\VojtaSvobodaBrandsResultsProvider;
use OFFLINE\SiteSearch\Classes\ResultCollection;
use Request;

/**
 * SearchResults Component
 * @package OFFLINE\SiteSearch\Components
 */
class SearchResults extends ComponentBase
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
    protected $resultCollection;
    /**
     * The current page number.
     *
     * @var int
     */
    protected $pageNumber;

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

        $this->resultCollection = $this->buildResultCollection();
    }

    /**
     * Setup all needed variables.
     *
     * @return void
     */
    protected function prepareVars()
    {
        $this->setVar('pageNumber', Request::get('page', 1));
        $this->setVar('query', Request::get('q', ''));
        $this->setVar('noResultsMessage');
        $this->setVar('visitPageMessage');
        $this->setVar('showProviderBadge');
        $this->setVar('resultsPerPage');
    }

    /**
     * Sets a var as a property on this class
     * and as a key in $this->page.
     *
     * If no value is specified the component property
     * named $var is set as value.
     *
     * @param      $var
     * @param null $value
     */
    protected function setVar($var, $value = null)
    {
        if ($value === null) {
            $value = $this->property($var);
        }
        $this->{$var} = $this->page[$var] = $value;
    }

    /**
     * Call all result providers and combine
     * their results in a ResultCollection.
     *
     * @throws DomainException
     * @return ResultCollection
     */
    protected function buildResultCollection()
    {
        $results = new ResultCollection();
        $results->setQuery($this->query);
        if ($this->query !== '') {
            $results->addMany([
                (new OfflineSnipcartShopResultsProvider($this->query))->search()->results(),
                (new RadiantWebProBlogResultsProvider($this->query))->search()->results(),
                (new FeeglewebOctoshopProductsResultsProvider($this->query))->search()->results(),
                (new JiriJKShopResultsProvider($this->query))->search()->results(),
                (new IndikatorNewsResultsProvider($this->query))->search()->results(),
                (new ArrizalaminPortfolioResultsProvider($this->query))->search()->results(),
                (new ResponsivShowcaseResultsProvider($this->query))->search()->results(),
                (new RainlabBlogResultsProvider($this->query, $this->controller))->search()->results(),
                (new RainlabPagesResultsProvider($this->query))->search()->results(),
                (new CmsPagesResultsProvider($this->query))->search()->results(),
                (new GenericResultsProvider($this->query))->search()->results(),
                (new VojtaSvobodaBrandsResultsProvider($this->query))->search()->results(),
            ]);
        }

        $results = $results->sortByDesc('relevance');

        return $results;
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

        return $paginator->setPath(\Url::to($this->getPageUrl()))->appends('q', $this->query);
    }

    /**
     * Try to get the page's url.
     *
     * @return string
     */
    public function getPageUrl()
    {
        // Component sits in cms page
        if (isset($this->page->settings['url'])) {
            return $this->page->settings['url'];
        }

        // Component sits in static page via snippet
        if (isset($this->page->apiBag['staticPage'])) {
            return $this->page->apiBag['staticPage']->viewBag['url'];
        }

        return '';
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

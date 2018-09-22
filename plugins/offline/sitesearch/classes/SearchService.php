<?php

namespace OFFLINE\SiteSearch\Classes;


use Cms\Classes\Controller;
use Event;
use Illuminate\Support\Collection;
use LogicException;
use OFFLINE\SiteSearch\Classes\Providers\ArrizalaminPortfolioResultsProvider;
use OFFLINE\SiteSearch\Classes\Providers\CmsPagesResultsProvider;
use OFFLINE\SiteSearch\Classes\Providers\FeeglewebOctoshopProductsResultsProvider;
use OFFLINE\SiteSearch\Classes\Providers\GenericResultsProvider;
use OFFLINE\SiteSearch\Classes\Providers\GrakerPhotoAlbumsResultsProvider;
use OFFLINE\SiteSearch\Classes\Providers\IndikatorNewsResultsProvider;
use OFFLINE\SiteSearch\Classes\Providers\JiriJKShopResultsProvider;
use OFFLINE\SiteSearch\Classes\Providers\OfflineSnipcartShopResultsProvider;
use OFFLINE\SiteSearch\Classes\Providers\RadiantWebProBlogResultsProvider;
use OFFLINE\SiteSearch\Classes\Providers\RainlabBlogResultsProvider;
use OFFLINE\SiteSearch\Classes\Providers\RainlabPagesResultsProvider;
use OFFLINE\SiteSearch\Classes\Providers\ResponsivShowcaseResultsProvider;
use OFFLINE\SiteSearch\Classes\Providers\ResultsProvider;
use OFFLINE\SiteSearch\Classes\Providers\VojtaSvobodaBrandsResultsProvider;

class SearchService
{
    /**
     * @var string
     */
    public $query;
    /**
     * @var Controller
     */
    public $controller;

    public function __construct($query, $controller = null)
    {
        $this->query      = $query;
        $this->controller = $controller ?: new Controller();
    }

    /**
     * Fetch all available results for the provided query
     *
     * @return ResultCollection
     * @throws \DomainException
     */
    public function results()
    {
        $resultsCollection = new ResultCollection();
        $resultsCollection->setQuery($this->query);

        if ($this->query === '') {
            return $resultsCollection;
        }

        $results = $this->resultsProviders();

        $results = $results->map(function (ResultsProvider $provider) {
            $provider->setQuery($this->query);
            $provider->search();

            return $provider->results();
        });

        $resultsCollection->addMany($results->toArray());

        return $resultsCollection->sortByDesc('relevance');
    }

    /**
     * Returns all native and the additional results providers.
     *
     * @return Collection
     */
    protected function resultsProviders()
    {
        return collect($this->nativeResultsProviders())
            ->merge($this->additionalResultsProviders());
    }

    /**
     * Return all natively supported results providers.
     *
     * @return ResultsProvider[]
     */
    protected function nativeResultsProviders()
    {
        return [
            new OfflineSnipcartShopResultsProvider(),
            new RadiantWebProBlogResultsProvider($this->query, $this->controller),
            new FeeglewebOctoshopProductsResultsProvider(),
            new JiriJKShopResultsProvider(),
            new IndikatorNewsResultsProvider(),
            new ArrizalaminPortfolioResultsProvider(),
            new ResponsivShowcaseResultsProvider(),
            new RainlabBlogResultsProvider($this->query, $this->controller),
            new RainlabPagesResultsProvider(),
            new CmsPagesResultsProvider(),
            new GenericResultsProvider(),
            new VojtaSvobodaBrandsResultsProvider(),
            new GrakerPhotoAlbumsResultsProvider($this->query, $this->controller),
        ];
    }

    /**
     * Gather all additional ResultsProviders that
     * are registered by other plugins.
     *
     * @return ResultsProvider[]
     * @throws \LogicException
     */
    protected function additionalResultsProviders()
    {
        $returns = collect(Event::fire('offline.sitesearch.extend'))->flatten();

        $returns->each(function ($return) {
            if ( ! $return instanceof ResultsProvider) {
                throw new LogicException('The offline.sitesearch.extend listener needs to return a ResultsProvider instance.');
            }
        });

        return $returns->toArray();
    }
}
<?php
namespace OFFLINE\SiteSearch\Classes\Providers;

use DomainException;
use Event;
use OFFLINE\SiteSearch\Classes\Result;

/**
 * Handles results that are provided by
 * other plugins via the event system.
 *
 * @package OFFLINE\SiteSearch\Classes\Providers
 */
class GenericResultsProvider extends ResultsProvider
{
    /**
     * Runs the search for this provider.
     *
     * @throws DomainException
     * @return ResultsProvider
     */
    public function search()
    {
        $returns = array_filter(Event::fire('offline.sitesearch.query', $this->query));

        foreach ($returns as $return) {
            $results  = array_key_exists('results', $return) ? $return['results'] : [];
            $provider = array_key_exists('provider', $return) ? $return['provider'] : '';

            $this->addResultsForProvider($results, $provider);
        }

        return $this;
    }

    /**
     * Adds a result to the ResultBag.
     *
     * @param $returns
     * @param $provider
     *
     * @throws DomainException
     */
    protected function addResultsForProvider($returns, $provider)
    {
        foreach ($returns as $return) {
            if ( ! $this->validate($return)) {
                continue;
            };

            $relevance = isset($return['relevance']) ? $return['relevance'] : 1;

            $result = new Result($this->query, $relevance, $provider);
            foreach ($return as $key => $value) {
                $result->{$key} = $value;
            }

            $result->identifier = $provider;

            $this->addResult($result, $provider);
        }
    }

    /**
     * Validates that all mandatory keys are
     * available in the provided results array
     *
     * @param $return
     *
     * @throws DomainException
     * @return array
     */
    protected function validate($return)
    {
        if ( ! is_array($return)) {
            return false;
        }
        if ( ! array_key_exists('title', $return)) {
            return false;
        }

        return true;
    }

    /**
     * Display name for this provider.
     *
     * @return string
     */
    public function displayName()
    {
        return 'Generic';
    }

    /**
     * Returns the plugin's identifier string.
     *
     * @return string
     */
    public function identifier()
    {
        return 'Generic';
    }
}

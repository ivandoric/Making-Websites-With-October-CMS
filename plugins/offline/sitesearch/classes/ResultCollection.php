<?php

namespace OFFLINE\SiteSearch\Classes;

use DomainException;
use Illuminate\Support\Collection;

/**
 * A collection of all search results
 * from all providers.
 *
 * @package OFFLINE\SiteSearch\Classes
 */
class ResultCollection extends Collection
{
    /**
     * The user's search query.
     *
     * @var string
     */
    protected $query;

    /**
     * @param string $query
     */
    public function setQuery($query)
    {
        $this->query = $query;
    }

    /**
     * Adds results from multiple providers at once.
     *
     * @param array $resultsArray
     *
     * @return ResultCollection
     * @throws \DomainException
     */
    public function addMany(array $resultsArray)
    {
        foreach ($resultsArray as $results) {
            $this->add($results);
        }

        return $this;
    }

    /**
     * Adds all the results from a single provider.
     *
     * @param $results
     *
     * @throws DomainException
     * @return ResultCollection
     */
    public function add($results)
    {
        if ( ! is_array($results)) {
            $results = [$results];
        }

        foreach ($results as $result) {
            $this->isValidResult($result)
                 ->push($result);
        }

        return $this;
    }

    /**
     * Checks if the given $result is a Result instance.
     *
     * @param $result
     *
     * @throws DomainException
     * @return ResultCollection
     */
    protected function isValidResult($result)
    {
        if ( ! $result instanceof Result) {
            throw new DomainException('You can only put Result instances into a ResultCollection');
        }

        return $this;
    }

    /**
     * Return all results as an array.
     *
     * @return array
     */
    public function results()
    {
        return $this->all();
    }
}
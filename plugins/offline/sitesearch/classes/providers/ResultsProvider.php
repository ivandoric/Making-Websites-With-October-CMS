<?php

namespace OFFLINE\SiteSearch\Classes\Providers;

use OFFLINE\SiteSearch\Classes\Result;
use RainLab\Translate\Classes\Translator;
use System\Classes\PluginManager;
use System\Models\File;

/**
 * Abstract base class for result providers
 *
 * @package OFFLINE\SiteSearch\Classes\Providers
 */
abstract class ResultsProvider
{
    /**
     * The plugins identifier string.
     *
     * @var string
     */
    protected $identifier = '';
    /**
     * The array to store all results in.
     *
     * @var array
     */
    protected $results = [];
    /**
     * The users search query.
     *
     * @var string
     */
    protected $query;
    /**
     * The display name for this provider.
     *
     * @var string
     */
    protected $displayName;
    /**
     * An instance of a RainLab.Translate Translator class if available.
     *
     * @var Translator|bool
     */
    protected $translator;

    /**
     * ResultsProvider constructor.
     *
     * @param $query
     */
    public function __construct($query = null)
    {
        $this->setQuery($query);
        
        $this->identifier  = $this->identifier();
        $this->displayName = $this->displayName();
        $this->translator  = $this->translator();
    }

    /**
     * Search your contents for matching models.
     *
     * Create a new instance of the OFFLINE\SiteSearch\Classes\Result class
     * for every one of your results. Then add it to the results collection
     * by calling the addResult() method.
     *
     * @see the SiteSearch native providers for more examples.
     *
     * @return ResultsProvider
     */
    abstract public function search();

    /**
     * The display name for this provider.
     *
     * The returned string from this method is displayed
     * as a badge for each individual result.
     *
     * @return string
     */
    abstract public function displayName();

    /**
     * A unique identifier for this provider.
     *
     * It is recommended to return the plugin identifier
     * string used by October. Eg: OFFLINE.SiteSearch
     *
     * @return string
     */
    abstract public function identifier();

    /**
     * Adds a result to the results array.
     *
     * @param Result $result
     * @param null   $provider
     *
     * @return ResultsProvider
     */
    public function addResult(Result $result, $provider = null)
    {
        if ($provider === null) {
            $result->provider = $this->displayName;
        }

        if ( ! $result->identifier) {
            $result->identifier = $this->identifier();
        }

        $this->results[] = $result;

        return $this;
    }

    /**
     * Return this provider's results array.
     *
     * @return array
     */
    public function results()
    {
        return $this->results;
    }

    /**
     * Sets the query for this provider.
     *
     * @return ResultsProvider
     */
    public function setQuery($query)
    {
        $this->query = trim($query);

        return $this;
    }

    /**
     * Returns a new Result instance.
     *
     * @param int $relevance
     *
     * @return Result
     */
    protected function newResult($relevance = 1)
    {
        return new Result($this->query, $relevance);
    }

    /**
     * Check's if a plugin is installed and enabled.
     *
     * @param string Plugin identifier
     *
     * @return bool
     */
    protected function isPluginAvailable($name)
    {
        return PluginManager::instance()->hasPlugin($name)
            && ! PluginManager::instance()->isDisabled($name);
    }


    /**
     * Check if the Rainlab.Translate plugin is installed
     * and if yes, get the translator instance.
     *
     * @return Translator|bool
     */
    protected function translator()
    {
        return $this->isPluginAvailable('RainLab.Translate')
            ? Translator::instance()
            : false;
    }

    /**
     * Extract the item's thumb.
     *
     * @param $images
     *
     * @return null|File
     */
    protected function getThumb($images)
    {
        if (count($images) < 1) {
            return null;
        }

        $image = $images->first();
        if ( ! $image instanceof File) {
            return null;
        }

        return $image;
    }

    /**
     * Give old results an age penalty to list them below newer results.
     *
     * @param $ageInDays
     *
     * @return float
     * @deprecated use static::agePenaltyForDays()
     */
    protected function getAgePenalty($ageInDays)
    {
        return static::agePenaltyForDays($ageInDays);
    }

    /**
     * Give old results an age penalty to list them below newer results.
     *
     * @param $ageInDays
     * @param $penaltyPerDay
     * @param $maxPenalty
     *
     * @return float
     */
    public static function agePenaltyForDays($ageInDays, $penaltyPerDay = 0.003, $maxPenalty = .9)
    {
        $penalty = $ageInDays * $penaltyPerDay;

        return $penalty > $maxPenalty ? $maxPenalty : $penalty;
    }
}

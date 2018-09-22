<?php

namespace OFFLINE\SiteSearch\Classes;

use Config;
use Html;
use October\Rain\Database\Model;
use OFFLINE\SiteSearch\Models\Settings;
use Str;
use System\Models\File;
use URL;

/**
 * Object to store a result's data.
 *
 * @package OFFLINE\SiteSearch\Classes
 */
class Result
{
    /**
     * @var string
     */
    public $excerpt;
    /**
     * @var float
     */
    public $relevance;
    /**
     * @var string
     */
    public $provider;
    /**
     * @var string
     */
    public $identifier;
    /**
     * @var string
     */
    public $query;
    /**
     * @var mixed
     */
    public $meta;
    /**
     * @var Model
     */
    public $model;

    /**
     * Result constructor.
     *
     * @param              $query
     * @param int          $relevance
     * @param string       $provider
     */
    public function __construct($query, $relevance = 1, $provider = '')
    {
        $this->setQuery($query);
        $this->setRelevance($relevance);
        $this->setProvider($provider);
    }

    /**
     * Set a property on the Result.
     *
     * Since only public properties can be accessed via Twig and it
     * does not support __get, we need to make all properties public.
     * This method tries to ensure that only valid properties
     * are set and all values are passed through the respective
     * setter method.
     *
     * @param $property
     * @param $value
     *
     * @throws \InvalidArgumentException
     */
    public function __set($property, $value)
    {
        $method = 'set' . ucfirst($property);
        if ( ! method_exists($this, $method)) {
            throw new \InvalidArgumentException(sprintf('"%s" is not a valid property to set.', $property));
        }

        call_user_func([$this, $method], $value);
    }

    /**
     * @param $query
     *
     * @return Result
     */
    public function setQuery($query)
    {
        $this->query = $query;

        return $this;
    }

    /**
     * @param $meta
     *
     * @return Result
     */
    public function setMeta($meta)
    {
        $this->meta = $meta;

        return $this;
    }

    /**
     * @param float $relevance
     *
     * @return $this
     */
    public function setRelevance($relevance)
    {
        $this->relevance = (float)$relevance;

        return $this;
    }

    /**
     * @param string $provider
     *
     * @return $this
     */
    public function setProvider($provider)
    {
        $this->provider = $provider;

        return $this;
    }

    /**
     * @param string $title
     *
     * @return Result
     */
    public function setTitle($title)
    {
        $this->title = $this->markQuery($this->prepare($title));

        return $this;
    }

    /**
     * Sets the text property and creates
     * a separate excerpt to display in the results
     * listing.
     *
     * @param string $text
     *
     * @return Result
     */
    public function setText($text)
    {
        $this->text    = $this->prepare($text);
        $this->excerpt = $this->createExcerpt($this->text);

        return $this;
    }

    /**
     * @param string $url
     *
     * @return Result
     */
    public function setUrl($url)
    {
        // If a provider returns the absolute URL to a result
        // remove the base url to make sure every result can
        // be linked by using the "app" filter in Twig.
        $baseUrl = URL::to('/');
        if (starts_with($url, $baseUrl)) {
            $url = str_replace($baseUrl, '', $url);
        }

        $this->url = $url;

        return $this;
    }

    /**
     * @param File $thumb
     *
     * @return Result
     */
    public function setThumb(File $thumb = null)
    {
        $this->thumb = $thumb;

        return $this;
    }

    /**
     * @param Model $model
     *
     * @return Result
     */
    public function setModel($model = null)
    {
        $this->model = $model;

        return $this;
    }

    /**
     * Shortens a string and removes all HTML.
     *
     * @param string $string
     *
     * @return string
     */
    protected function prepare($string)
    {
        // Add a space before each tag to prevent
        // paragraphs from sticking together after
        // removing the html.
        $string = str_replace('<', ' <', $string);

        return Html::strip($string);
    }

    /**
     * Creates an excerpt of the query-relevant parts of $text
     * to display below a search result.
     *
     * @param $text
     *
     * @return string
     */
    protected function createExcerpt($text)
    {
        $length = Settings::get('excerpt_length', 250);

        $loweredText  = mb_strtolower($text);
        $loweredQuery = mb_strtolower($this->query);

        $position = mb_strpos($loweredText, $loweredQuery);
        $start    = (int)$position - ($length / 2);

        if ($start < 0) {
            $excerpt = Str::limit($text, $length);
        } else {
            // The relevant part is in the middle of the string,
            // so surround it with "..."
            $excerpt = '...' . trim(mb_substr($text, $start, $length)) . '...';
        }

        return $this->markQuery($excerpt);
    }


    /**
     * Surrounds all instances of the query
     * in $text with <mark> tags.
     *
     * @param $text
     *
     * @return string
     */
    private function markQuery($text)
    {
        // Only mark the query if this feature is enabled
        if ( ! Settings::get('mark_results', true)) {
            return $text;
        }

        return (string)preg_replace('/(' . preg_quote($this->query, '/') . ')/iu', '<mark>$0</mark>', $text);
    }

}

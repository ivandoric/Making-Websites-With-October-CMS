<?php

namespace OFFLINE\SiteSearch\Classes;

use Config;
use Html;
use OFFLINE\SiteSearch\Models\Settings;
use Str;
use System\Models\File;

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
    public $query;
    /**
     * @var mixed
     */
    public $meta;

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
        $this->excerpt = $this->createExcerpt(
            $this->markQuery($this->text)
        );

        return $this;
    }

    /**
     * @param string $url
     *
     * @return Result
     */
    public function setUrl($url)
    {
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

        $position = mb_strpos($loweredText, '<mark>' . $loweredQuery . '</mark>');
        $start    = (int)$position - ($length / 2);

        if ($start < 0) {
            $excerpt = Str::limit($text, $length);
        } else {
            // The relevant part is in the middle of the string,
            // so surround it with "..."
            $excerpt = '...' . trim(mb_substr($text, $start, $length)) . '...';
        }

        return $this->checkBorders($excerpt);
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

        return (string)preg_replace('/(' . preg_quote($this->query, '/') . ')/i', '<mark>$0</mark>', $text);
    }


    /**
     * Checks for unclosed/broken <mark> tags on the
     * end of the excerpt and removes it if found.
     *
     * @param string $excerpt
     *
     * @return string
     */
    protected function checkBorders($excerpt)
    {
        // count opening and closing tags
        $openings = substr_count($excerpt, '<mark>');
        $closings = substr_count($excerpt, '</mark>');
        if ($openings !== $closings) {
            // last mark tag seems to be broken, remove it
            $position = mb_strrpos($excerpt, '<mark>');
            $excerpt  = trim(mb_substr($excerpt, 0, $position)) . '...';
        }

        return $excerpt;
    }

}

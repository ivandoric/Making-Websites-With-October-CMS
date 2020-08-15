<?php

namespace OFFLINE\SiteSearch\Classes\Providers;

use Cms\Classes\Controller;
use Cms\Classes\Page;
use Cms\Classes\Theme;
use Illuminate\Database\Eloquent\Collection;
use OFFLINE\SiteSearch\Classes\Result;
use OFFLINE\SiteSearch\Classes\ResultData;
use OFFLINE\SiteSearch\Models\Settings;

/**
 * Searches the contents of native cms pages.
 *
 * @package OFFLINE\SiteSearch\Classes\Providers
 */
class CmsPagesResultsProvider extends ResultsProvider
{
    /**
     * Runs the search for this provider.
     *
     * @return ResultsProvider
     */
    public function search()
    {
        if ( ! $this->isEnabled()) {
            return $this;
        }

        foreach ($this->pages() as $page) {
            // Only include pages that contain the siteSearchInclude component.
            if ( ! $page->hasComponent('siteSearchInclude')) {
                continue;
            }

            // Render page in empty layout.
            $page->settings['layout'] = null;

            $contents = \Html::strip((new Controller())->runPage($page));
            if ( ! $this->containsQueryIn($contents, $page)) {
                continue;
            }

            $relevance = $this->containsQuery($page->settings['title']) ? 2 : 1;

            $result        = new Result($this->query, $relevance);
            $result->title = $page->settings['title'];
            $result->text  = $contents;
            $result->url   = $page->settings['url'];
            $result->model = $page;

            $this->addResult($result);
        }

        return $this;
    }

    /**
     * Checks if this provider is enabled
     * in the config.
     *
     * @return bool
     */
    protected function isEnabled()
    {
        return Settings::get('cms_pages_enabled', false);
    }

    /**
     * Get all cms pages.
     *
     * @return Collection
     */
    protected function pages()
    {
        return Page::listInTheme(Theme::getActiveTheme(), true);
    }

    /**
     * Checks if $subjects contains the query string.
     *
     * @param $subject
     *
     * @return bool
     */
    protected function containsQuery($subject)
    {
        return mb_strpos(strtolower($subject), strtolower($this->query)) !== false;
    }

    /**
     * Display name for this provider.
     *
     * @return mixed
     */
    public function displayName()
    {
        return Settings::get('cms_pages_label', 'Page');
    }

    /**
     * Search the query in the title and contents of the page.
     *
     * @param $contents
     * @param $page
     *
     * @return bool
     */
    protected function containsQueryIn($contents, $page)
    {
        return $this->containsQuery($contents) || $this->containsQuery($page->settings['title']);
    }

    /**
     * Returns the plugin's identifier string.
     *
     * @return string
     */
    public function identifier()
    {
        return 'CMS.Page';
    }
}

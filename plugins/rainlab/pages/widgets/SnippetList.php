<?php namespace RainLab\Pages\Widgets;

use Backend\Classes\WidgetBase;
use RainLab\Pages\Classes\Snippet;
use RainLab\Pages\Classes\SnippetManager;
use Cms\Classes\Theme;
use Input;
use Response;
use Request;
use Str;
use Lang;

/**
 * Snippet list widget.
 *
 * @package rainlab\pages
 * @author Alexey Bobkov, Samuel Georges
 */
class SnippetList extends WidgetBase
{
    use \Backend\Traits\SearchableWidget;

    protected $theme;

    protected $dataIdPrefix;

    public $noRecordsMessage = 'rainlab.pages::lang.snippet.no_records';

    public function __construct($controller, $alias)
    {
        $this->alias = $alias;
        $this->theme = Theme::getEditTheme();
        $this->dataIdPrefix = 'snippet-'.$this->theme->getDirName();

        parent::__construct($controller, []);
        $this->bindToController();
    }

    /**
     * Renders the widget.
     * @return string
     */
    public function render()
    {
        return $this->makePartial('body', [
            'data' => $this->getData()
        ]);
    }

    /*
     * Event handlers
     */

    public function onSearch()
    {
        $this->setSearchTerm(Input::get('search'));

        return $this->updateList();
    }

    /*
     * Methods for the internal use
     */

    protected function getData()
    {
        $manager = SnippetManager::instance();
        $snippets = $manager->listSnippets($this->theme);

        $searchTerm = Str::lower($this->getSearchTerm());

        if (strlen($searchTerm)) {
            $words = explode(' ', $searchTerm);
            $filteredSnippets = [];

            foreach ($snippets as $snippet) {
                if ($this->textMatchesSearch($words, $snippet->getName().' '.$snippet->code.' '.$snippet->getDescription())) {
                    $filteredSnippets[] = $snippet;
                }
            }

            $snippets = $filteredSnippets;
        }

        usort($snippets, function($a, $b) {
            return strcmp($a->getName(), $b->getName());
        });

        return $snippets;
    }

    protected function updateList()
    {
        return ['#'.$this->getId('snippet-list') => $this->makePartial('items', ['items' => $this->getData()])];
    }

    protected function getThemeSessionKey($prefix)
    {
        return $prefix.$this->theme->getDirName();
    }    

    protected function getSession($key = null, $default = null)
    {
        $key = strlen($key) ? $this->getThemeSessionKey($key) : $key;

        return parent::getSession($key, $default);
    }

    protected function putSession($key, $value) 
    {
        return parent::putSession($this->getThemeSessionKey($key), $value);
    }
}

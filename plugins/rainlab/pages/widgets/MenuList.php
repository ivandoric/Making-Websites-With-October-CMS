<?php namespace RainLab\Pages\Widgets;

use Str;
use Lang;
use Input;
use Request;
use Response;
use RainLab\Pages\Classes\Menu;
use Backend\Classes\WidgetBase;
use Cms\Classes\Theme;

/**
 * Menu list widget.
 *
 * @package rainlab\pages
 * @author Alexey Bobkov, Samuel Georges
 */
class MenuList extends WidgetBase
{
    use \Backend\Traits\SearchableWidget;
    use \Backend\Traits\SelectableWidget;

    protected $theme;

    protected $dataIdPrefix;

    /**
     * @var string Message to display when the Delete button is clicked.
     */
    public $deleteConfirmation = 'rainlab.pages::lang.menu.delete_confirmation';

    public $noRecordsMessage = 'rainlab.pages::lang.menu.no_records';

    public function __construct($controller, $alias)
    {
        $this->alias = $alias;
        $this->theme = Theme::getEditTheme();
        $this->dataIdPrefix = 'page-'.$this->theme->getDirName();

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

    //
    // Event handlers
    //

    public function onUpdate()
    {
        $this->extendSelection();

        return $this->updateList();
    }

    public function onSearch()
    {
        $this->setSearchTerm(Input::get('search'));
        $this->extendSelection();

        return $this->updateList();
    }

    //
    // Methods for the internal use
    //

    protected function getData()
    {
        $menus = Menu::listInTheme($this->theme, true);

        $searchTerm = Str::lower($this->getSearchTerm());

        if (strlen($searchTerm)) {
            $words = explode(' ', $searchTerm);
            $filteredMenus = [];

            foreach ($menus as $menu) {
                if ($this->textMatchesSearch($words, $menu->name.' '.$menu->fileName)) {
                    $filteredMenus[] = $menu;
                }
            }

            $menus = $filteredMenus;
        }

        return $menus;
    }

    protected function updateList()
    {
        $vars = ['items' => $this->getData()];
        return ['#'.$this->getId('menu-list') => $this->makePartial('items', $vars)];
    }

    protected function getThemeSessionKey($prefix)
    {
        return $prefix . $this->theme->getDirName();
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

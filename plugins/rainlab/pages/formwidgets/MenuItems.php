<?php namespace RainLab\Pages\FormWidgets;

use Request;
use Backend\Classes\FormWidgetBase;
use RainLab\Pages\Classes\MenuItem;

/**
 * Menu items widget.
 *
 * @package october\backend
 * @author Alexey Bobkov, Samuel Georges
 */
class MenuItems extends FormWidgetBase
{
    protected $typeListCache = false;
    protected $typeInfoCache = [];

    /**
     * {@inheritDoc}
     */
    protected $defaultAlias = 'menuitems';

    public $addSubitemLabel = 'rainlab.pages::lang.menu.add_subitem';

    public $noRecordsMessage = 'rainlab.pages::lang.menu.no_records';

    public $titleRequiredMessage = 'rainlab.pages::lang.menuitem.title_required';

    public $referenceRequiredMessage = 'rainlab.pages::lang.menuitem.reference_required';

    public $urlRequiredMessage = 'rainlab.pages::lang.menuitem.url_required';

    public $cmsPageRequiredMessage = 'rainlab.pages::lang.menuitem.cms_page_required';
    
    public $newItemTitle = 'rainlab.pages::lang.menuitem.new_item';

    /**
     * {@inheritDoc}
     */
    public function init()
    {
    }

    /**
     * {@inheritDoc}
     */
    public function render()
    {
        $this->prepareVars();

        return $this->makePartial('menuitems');
    }

    /**
     * Prepares the list data
     */
    public function prepareVars()
    {
        $menuItem = new MenuItem;

        $this->vars['itemProperties'] = json_encode($menuItem->fillable);
        $this->vars['items'] = $this->model->items;

        $emptyItem = new MenuItem;
        $emptyItem->title = trans($this->newItemTitle);
        $emptyItem->type = 'url';
        $emptyItem->url = '/';

        $this->vars['emptyItem'] = $emptyItem;

        $widgetConfig = $this->makeConfig('~/plugins/rainlab/pages/classes/menuitem/fields.yaml');
        $widgetConfig->model = $menuItem;
        $widgetConfig->alias = $this->alias.'MenuItem';

        $this->vars['itemFormWidget'] = $this->makeWidget('Backend\Widgets\Form', $widgetConfig);
    }

    /**
     * {@inheritDoc}
     */
    protected function loadAssets()
    {
        $this->addJs('js/menu-items-editor.js', 'core');
    }

    /**
     * {@inheritDoc}
     */
    public function getSaveValue($value)
    {
        return strlen($value) ? $value : null;
    }

    //
    // Methods for the internal use
    //

    /**
     * Returns the item reference description.
     * @param \RainLab\Pages\Classes\MenuItem $item Specifies the menu item
     * @return string 
     */
    protected function getReferenceDescription($item)
    {
        if ($this->typeListCache === false) {
            $this->typeListCache = $item->getTypeOptions();
        }

        if (!isset($this->typeInfoCache[$item->type])) {
            $this->typeInfoCache[$item->type] = MenuItem::getTypeInfo($item->type);
        }

        if (isset($this->typeInfoCache[$item->type])) {
            $result = trans($this->typeListCache[$item->type]);

            if ($item->type !== 'url') {
                if (isset($this->typeInfoCache[$item->type]['references'])) {
                    $result .= ': '.$this->findReferenceName($item->reference, $this->typeInfoCache[$item->type]['references']);
                }
            }
            else {
                $result .= ': '.$item->url;
            }

        }
        else {
            $result = trans('rainlab.pages::lang.menuitem.unknown_type');
        }

        return $result;
    }

    protected function findReferenceName($search, $typeOptionList)
    {
        $iterator = function($optionList, $path) use ($search, &$iterator) {
            foreach ($optionList as $reference => $info) {
                if ($reference == $search) {
                    $result = $this->getMenuItemTitle($info);

                    return strlen($path) ? $path.' / ' .$result : $result;
                }

                if (is_array($info) && isset($info['items'])) {
                    $result = $iterator($info['items'], $path.' / '.$this->getMenuItemTitle($info));

                    if (strlen($result)) {
                        return strlen($path) ? $path.' / '.$result : $result;
                    }
                }
            }
        };

        $result = $iterator($typeOptionList, null);
        if (!strlen($result)) {
            $result = trans('rainlab.pages::lang.menuitem.unnamed');
        }

        $result = preg_replace('|^\s+\/|', '', $result);

        return $result;
    }

    protected function getMenuItemTitle($itemInfo)
    {
        if (is_array($itemInfo)) {
            if (!array_key_exists('title', $itemInfo) || !strlen($itemInfo['title'])) {
                return trans('rainlab.pages::lang.menuitem.unnamed');
            }

            return $itemInfo['title'];
        }

        return strlen($itemInfo) ? $itemInfo : trans('rainlab.pages::lang.menuitem.unnamed');
    }
}

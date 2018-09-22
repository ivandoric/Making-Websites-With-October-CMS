<?php namespace RainLab\Pages\FormWidgets;

use Backend\Classes\FormWidgetBase;
use Cms\Classes\Theme;
use RainLab\Pages\Classes\Page;

/**
 * Static page picker widget
 *
 * @package october\backend
 * @author Alexey Bobkov, Samuel Georges
 */
class PagePicker extends FormWidgetBase
{
    protected $indent = '&nbsp;&nbsp;&nbsp;';

    /**
     * @inheritDoc
     */
    public function render()
    {
        $this->prepareVars();
        return $this->makePartial('~/modules/backend/widgets/form/partials/_field_dropdown.htm');
    }

    /**
     * Prepares the view data
     */
    public function prepareVars()
    {
        $this->vars['field'] = $this->makeFormField();
    }

    /**
     * @return \Backend\Classes\FormField
     */
    protected function makeFormField()
    {
        $field = clone $this->formField;
        $field->type = 'dropdown';

        $tree = Page::buildMenuTree(Theme::getEditTheme());
        $indent = $field->getConfig('indent', $this->indent);

        // Flatten page tree for dropdown options
        $options = [];
        $iterator = function($items, $depth=0) use(&$iterator, &$tree, &$options, $indent) {

            foreach ($items as $code) {
                $itemData = $tree[$code];
                $options[$code] = str_repeat($indent, $depth) . $itemData['title'];
                if (!empty($itemData['items'])) {
                    $iterator($itemData['items'], $depth+1);
                }
            }

            return $options;
        };

        $field->options = $iterator($tree['--root-pages--']);

        return $field;
    }
}
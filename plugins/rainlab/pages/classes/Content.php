<?php namespace RainLab\Pages\Classes;

use Cms\Classes\Content as ContentBase;

/**
 * Represents a content template.
 *
 * @package rainlab\pages
 * @author Alexey Bobkov, Samuel Georges
 */
class Content extends ContentBase
{
    public $implement = ['@RainLab.Translate.Behaviors.TranslatableCmsObject'];

    /**
     * @var array Attributes that support translation, if available.
     */
    public $translatable = [
        'markup'
    ];

    public $translatableModel = 'RainLab\Translate\Classes\MLContent';

    /**
     * Converts the content object file name in to something nicer
     * for humans to read.
     * @return string
     */
    public function getNiceTitleAttribute()
    {
        $title = basename($this->getBaseFileName());
        $title = ucwords(str_replace(['-', '_'], ' ', $title));
        return $title;
    }
}

<?php namespace RainLab\Pages\Controllers;

use Url;
use Lang;
use Flash;
use Event;
use Config;
use Request;
use Response;
use BackendMenu;
use Cms\Classes\Layout;
use Cms\Classes\Theme;
use Cms\Classes\CmsObject;
use Cms\Classes\CmsCompoundObject;
use Cms\Widgets\TemplateList;
use System\Helpers\DateTime;
use Backend\Classes\Controller;
use RainLab\Pages\Widgets\PageList;
use RainLab\Pages\Widgets\MenuList;
use RainLab\Pages\Widgets\SnippetList;
use RainLab\Pages\Classes\Snippet;
use RainLab\Pages\Classes\Page as StaticPage;
use RainLab\Pages\Classes\Router;
use RainLab\Pages\Classes\Content;
use RainLab\Pages\Classes\MenuItem;
use RainLab\Pages\Plugin as PagesPlugin;
use RainLab\Pages\Classes\SnippetManager;
use ApplicationException;
use Exception;

/**
 * Pages and Menus index
 *
 * @package rainlab\pages
 * @author Alexey Bobkov, Samuel Georges
 */
class Index extends Controller
{
    use \Backend\Traits\InspectableContainer;

    protected $theme;

    public $requiredPermissions = ['rainlab.pages.*'];

    /**
     * Constructor.
     */
    public function __construct()
    {
        parent::__construct();

        try {
            if (!($this->theme = Theme::getEditTheme())) {
                throw new ApplicationException(Lang::get('cms::lang.theme.edit.not_found'));
            }

            if ($this->user) {
                if ($this->user->hasAccess('rainlab.pages.manage_pages')) {
                    new PageList($this, 'pageList');
                    $this->vars['activeWidgets'][] = 'pageList';
                }

                if ($this->user->hasAccess('rainlab.pages.manage_menus')) {
                    new MenuList($this, 'menuList');
                    $this->vars['activeWidgets'][] = 'menuList';
                }

                if ($this->user->hasAccess('rainlab.pages.manage_content')) {
                    new TemplateList($this, 'contentList', function() {
                        return $this->getContentTemplateList();
                    });
                    $this->vars['activeWidgets'][] = 'contentList';
                }

                if ($this->user->hasAccess('rainlab.pages.access_snippets')) {
                    new SnippetList($this, 'snippetList');
                    $this->vars['activeWidgets'][] = 'snippetList';
                }
            }
        }
        catch (Exception $ex) {
            $this->handleError($ex);
        }

        $context = [
            'pageList' => 'pages',
            'menuList' => 'menus',
            'contentList' => 'content',
            'snippetList' => 'snippets',
        ];

        BackendMenu::setContext('RainLab.Pages', 'pages', @$context[$this->vars['activeWidgets'][0]]);
    }

    //
    // Pages, menus and text blocks
    //

    public function index()
    {
        $this->addJs('/modules/backend/assets/js/october.treeview.js', 'core');
        $this->addJs('/plugins/rainlab/pages/assets/js/pages-page.js', 'RainLab.Pages');
        $this->addJs('/plugins/rainlab/pages/assets/js/pages-snippets.js', 'RainLab.Pages');
        $this->addCss('/plugins/rainlab/pages/assets/css/pages.css', 'RainLab.Pages');

        // Preload the code editor class as it could be needed
        // before it loads dynamically.
        $this->addJs('/modules/backend/formwidgets/codeeditor/assets/js/build-min.js', 'core');

        $this->bodyClass = 'compact-container';
        $this->pageTitle = 'rainlab.pages::lang.plugin.name';
        $this->pageTitleTemplate = Lang::get('rainlab.pages::lang.page.template_title');

        if (Request::ajax() && Request::input('formWidgetAlias')) {
            $this->bindFormWidgetToController();
        }
    }

    public function index_onOpen()
    {
        $this->validateRequestTheme();

        $type = Request::input('type');
        $object = $this->loadObject($type, Request::input('path'));

        return $this->pushObjectForm($type, $object);
    }

    public function onSave()
    {
        $this->validateRequestTheme();
        $type = Request::input('objectType');

        $object = $this->fillObjectFromPost($type);
        $object->save();

        /*
         * Extensibility
         */
        Event::fire('pages.object.save', [$this, $object, $type]);
        $this->fireEvent('object.save', [$object, $type]);

        $result = $this->getUpdateResponse($object, $type);

        $successMessages = [
            'page' => 'rainlab.pages::lang.page.saved',
            'menu' => 'rainlab.pages::lang.menu.saved',
            'content' => 'rainlab.pages::lang.content.saved',
        ];

        $successMessage = isset($successMessages[$type])
            ? $successMessages[$type]
            : $successMessages['page'];

        Flash::success(Lang::get($successMessage));

        return $result;
    }

    public function onCreateObject()
    {
        $this->validateRequestTheme();

        $type = Request::input('type');
        $object = $this->createObject($type);
        $parent = Request::input('parent');
        $parentPage = null;

        if ($type == 'page') {
            if (strlen($parent)) {
                $parentPage = StaticPage::load($this->theme, $parent);
            }

            $object->setDefaultLayout($parentPage);
        }

        $widget = $this->makeObjectFormWidget($type, $object);
        $this->vars['objectPath'] = '';
        $this->vars['canCommit'] = $this->canCommitObject($object);
        $this->vars['canReset'] = $this->canResetObject($object);

        $result = [
            'tabTitle' => $this->getTabTitle($type, $object),
            'tab'      => $this->makePartial('form_page', [
                'form'         => $widget,
                'objectType'   => $type,
                'objectTheme'  => $this->theme->getDirName(),
                'objectMtime'  => null,
                'objectParent' => $parent,
                'parentPage'   => $parentPage
            ])
        ];

        return $result;
    }

    public function onDelete()
    {
        $this->validateRequestTheme();

        $type = Request::input('objectType');

        $deletedObjects = $this->loadObject($type, trim(Request::input('objectPath')))->delete();

        $result = [
            'deletedObjects' => $deletedObjects,
            'theme' => $this->theme->getDirName()
        ];

        return $result;
    }

    public function onDeleteObjects()
    {
        $this->validateRequestTheme();

        $type = Request::input('type');
        $objects = Request::input('object');

        if (!$objects) {
            $objects = Request::input('template');
        }

        $error = null;
        $deleted = [];

        try {
            foreach ($objects as $path => $selected) {
                if (!$selected) {
                    continue;
                }
                $object = $this->loadObject($type, $path, true);
                if (!$object) {
                    continue;
                }

                $deletedObjects = $object->delete();
                if (is_array($deletedObjects)) {
                    $deleted = array_merge($deleted, $deletedObjects);
                }
                else {
                    $deleted[] = $path;
                }
            }
        }
        catch (Exception $ex) {
            $error = $ex->getMessage();
        }

        return [
            'deleted' => $deleted,
            'error'   => $error,
            'theme'   => Request::input('theme')
        ];
    }

    public function onOpenConcurrencyResolveForm()
    {
        return $this->makePartial('concurrency_resolve_form');
    }

    public function onGetMenuItemTypeInfo()
    {
        $type = Request::input('type');

        return [
            'menuItemTypeInfo' => MenuItem::getTypeInfo($type)
        ];
    }

    public function onUpdatePageLayout()
    {
        $this->validateRequestTheme();

        $type = Request::input('objectType');

        $object = $this->fillObjectFromPost($type);

        return $this->pushObjectForm($type, $object, Request::input('formWidgetAlias'));
    }

    public function onGetInspectorConfiguration()
    {
        $configuration = [];

        $snippetCode = Request::input('snippet');
        $componentClass = Request::input('component');

        if (strlen($snippetCode)) {
            $snippet = SnippetManager::instance()->findByCodeOrComponent($this->theme, $snippetCode, $componentClass);
            if (!$snippet) {
                throw new ApplicationException(trans('rainlab.pages::lang.snippet.not_found', ['code' => $snippetCode]));
            }

            $configuration = $snippet->getProperties();
        }

        return [
            'configuration' => [
                'properties'  => $configuration,
                'title'       => $snippet->getName(),
                'description' => $snippet->getDescription()
            ]
        ];
    }

    public function onGetSnippetNames()
    {
        $codes = array_unique(Request::input('codes'));
        $result = [];

        foreach ($codes as $snippetCode) {
            $parts = explode('|', $snippetCode);
            $componentClass = null;

            if (count($parts) > 1) {
                $snippetCode = $parts[0];
                $componentClass = $parts[1];
            }

            $snippet = SnippetManager::instance()->findByCodeOrComponent($this->theme, $snippetCode, $componentClass);

            if (!$snippet) {
                $result[$snippetCode] = trans('rainlab.pages::lang.snippet.not_found', ['code' => $snippetCode]);
            }
            else {
                $result[$snippetCode] =$snippet->getName();
            }
        }

        return [
            'names' => $result
        ];
    }

    public function onMenuItemReferenceSearch()
    {
        $alias = Request::input('alias');

        $widget = $this->makeFormWidget(
            'Rainlab\Pages\FormWidgets\MenuItemSearch',
            [],
            ['alias' => $alias]
        );

        return $widget->onSearch();
    }

    /**
     * Commits the DB changes of a object to the filesystem
     *
     * @return array $response
     */
    public function onCommit()
    {
        $this->validateRequestTheme();
        $type = Request::input('objectType');
        $object = $this->loadObject($type, trim(Request::input('objectPath')));

        if ($this->canCommitObject($object)) {
            // Populate the filesystem with the object and then remove it from the db
            $datasource = $this->getThemeDatasource();
            $datasource->pushToSource($object, 'filesystem');
            $datasource->removeFromSource($object, 'database');

            Flash::success(Lang::get('cms::lang.editor.commit_success', ['type' => $type]));
        }

        return array_merge($this->getUpdateResponse($object, $type), ['forceReload' => true]);
    }

    /**
     * Resets a object to the version on the filesystem
     *
     * @return array $response
     */
    public function onReset()
    {
        $this->validateRequestTheme();
        $type = Request::input('objectType');
        $object = $this->loadObject($type, trim(Request::input('objectPath')));

        if ($this->canResetObject($object)) {
            // Remove the object from the DB
            $datasource = $this->getThemeDatasource();
            $datasource->removeFromSource($object, 'database');

            Flash::success(Lang::get('cms::lang.editor.reset_success', ['type' => $type]));
        }

        return array_merge($this->getUpdateResponse($object, $type), ['forceReload' => true]);
    }

    //
    // Methods for internal use
    //

    /**
     * Get the response to return in an AJAX request that updates an object
     *
     * @param CmsObject $object The object that has been affected
     * @param string $type The type of object being affected
     * @return array $result;
     */
    protected function getUpdateResponse(CmsObject $object, string $type)
    {
        $result = [
            'objectPath'  => $type != 'content' ? $object->getBaseFileName() : $object->fileName,
            'objectMtime' => $object->mtime,
            'tabTitle'    => $this->getTabTitle($type, $object)
        ];

        if ($type == 'page') {
            $result['pageUrl'] = Url::to($object->getViewBag()->property('url'));
            PagesPlugin::clearCache();
        }

        $result['canCommit'] = $this->canCommitObject($object);
        $result['canReset'] = $this->canResetObject($object);

        return $result;
    }

    /**
     * Get the active theme's datasource
     *
     * @return \October\Rain\Halcyon\Datasource\DatasourceInterface
     */
    protected function getThemeDatasource()
    {
        return $this->theme->getDatasource();
    }

    /**
     * Check to see if the provided object can be committed
     * Only available in debug mode, the DB layer must be enabled, and the object must exist in the database
     *
     * @param CmsObject $object
     * @return boolean
     */
    protected function canCommitObject(CmsObject $object)
    {
        $result = false;

        if (Config::get('app.debug', false) &&
            Theme::databaseLayerEnabled() &&
            $this->getThemeDatasource()->sourceHasModel('database', $object)
        ) {
            $result = true;
        }

        return $result;
    }

    /**
     * Check to see if the provided object can be reset
     * Only available when the DB layer is enabled and the object exists in both the DB & Filesystem
     *
     * @param CmsObject $object
     * @return boolean
     */
    protected function canResetObject(CmsObject $object)
    {
        $result = false;

        if (Theme::databaseLayerEnabled()) {
            $datasource = $this->getThemeDatasource();
            $result = $datasource->sourceHasModel('database', $object) && $datasource->sourceHasModel('filesystem', $object);
        }

        return $result;
    }

    protected function validateRequestTheme()
    {
        if ($this->theme->getDirName() != Request::input('theme')) {
            throw new ApplicationException(trans('cms::lang.theme.edit.not_match'));
        }
    }

    protected function loadObject($type, $path, $ignoreNotFound = false)
    {
        $class = $this->resolveTypeClassName($type);

        if (!($object = call_user_func(array($class, 'load'), $this->theme, $path))) {
            if (!$ignoreNotFound) {
                throw new ApplicationException(trans('rainlab.pages::lang.object.not_found'));
            }

            return null;
        }

        return $object;
    }

    protected function createObject($type)
    {
        $class = $this->resolveTypeClassName($type);

        if (!($object = $class::inTheme($this->theme))) {
            throw new ApplicationException(trans('rainlab.pages::lang.object.not_found'));
        }

        return $object;
    }

    protected function resolveTypeClassName($type)
    {
        $types = [
            'page'    => 'RainLab\Pages\Classes\Page',
            'menu'    => 'RainLab\Pages\Classes\Menu',
            'content' => 'RainLab\Pages\Classes\Content'
        ];

        if (!array_key_exists($type, $types)) {
            throw new ApplicationException(Lang::get('rainlab.pages::lang.object.invalid_type') . ' - type - ' . $type);
        }

        $allowed = false;
        if ($type === 'content') {
            $allowed = $this->user->hasAccess('rainlab.pages.manage_content');
        } else {
            $allowed = $this->user->hasAccess("rainlab.pages.manage_{$type}s");
        }

        if (!$allowed) {
            throw new ApplicationException(Lang::get('rainlab.pages::lang.object.unauthorized_type', ['type' => $type]));
        }

        return $types[$type];
    }

    protected function makeObjectFormWidget($type, $object, $alias = null)
    {
        $formConfigs = [
            'page'    => '~/plugins/rainlab/pages/classes/page/fields.yaml',
            'menu'    => '~/plugins/rainlab/pages/classes/menu/fields.yaml',
            'content' => '~/plugins/rainlab/pages/classes/content/fields.yaml'
        ];

        if (!array_key_exists($type, $formConfigs)) {
            throw new ApplicationException(Lang::get('rainlab.pages::lang.object.not_found'));
        }

        $widgetConfig = $this->makeConfig($formConfigs[$type]);
        $widgetConfig->model = $object;
        $widgetConfig->alias = $alias ?: 'form' . studly_case($type) . md5($object->exists ? $object->getFileName() : uniqid());
        $widgetConfig->context = !$object->exists ? 'create' : 'update';

        $widget = $this->makeWidget('Backend\Widgets\Form', $widgetConfig);

        if ($type == 'page') {
            $widget->bindEvent('form.extendFieldsBefore', function() use ($widget, $object) {
                $this->checkContentField($widget, $object);
                $this->addPagePlaceholders($widget, $object);
                $this->addPageSyntaxFields($widget, $object);
            });
        }

        return $widget;
    }

    protected function checkContentField($formWidget, $page)
    {
        if (!($layout = $page->getLayoutObject())) {
            return;
        }

        $component = $layout->getComponent('staticPage');

        if (!$component) {
            return;
        }

        if (!$component->property('useContent', true)) {
            unset($formWidget->secondaryTabs['fields']['markup']);
        }
    }

    protected function addPageSyntaxFields($formWidget, $page)
    {
        $fields = $page->listLayoutSyntaxFields();

        foreach ($fields as $fieldCode => $fieldConfig) {
            if ($fieldConfig['type'] == 'fileupload') continue;

            if ($fieldConfig['type'] == 'repeater') {
                if (empty($fieldConfig['form']) || !is_string($fieldConfig['form'])) {
                    $fieldConfig['form']['fields'] = array_get($fieldConfig, 'fields', []);
                    unset($fieldConfig['fields']);
                }
            }

            /*
            * Custom fields placement
            */
            $placement = (!empty($fieldConfig['placement']) ? $fieldConfig['placement'] : NULL);

            switch ($placement) {
                case 'primary':
                    $formWidget->tabs['fields']['viewBag[' . $fieldCode . ']'] = $fieldConfig;
                    break;

                default:
                    $fieldConfig['cssClass'] = 'secondary-tab ' . array_get($fieldConfig, 'cssClass', '');
                    $formWidget->secondaryTabs['fields']['viewBag[' . $fieldCode . ']'] = $fieldConfig;
                    break;
            }

            /*
             * Translation support
             */
            $translatableTypes = ['text', 'textarea', 'richeditor', 'repeater'];
            if (in_array($fieldConfig['type'], $translatableTypes)) {
                $page->translatable[] = 'viewBag['.$fieldCode.']';
            }
        }
    }

    protected function addPagePlaceholders($formWidget, $page)
    {
        $placeholders = $page->listLayoutPlaceholders();

        foreach ($placeholders as $placeholderCode => $info) {
            if ($info['ignore']) {
                continue;
            }

            $placeholderTitle = $info['title'];
            $fieldConfig = [
                'tab'     => $placeholderTitle,
                'stretch' => '1',
                'size'    => 'huge'
            ];

            if ($info['type'] != 'text') {
                $fieldConfig['type'] = 'richeditor';
            }
            else {
                $fieldConfig['type'] = 'codeeditor';
                $fieldConfig['language'] = 'text';
                $fieldConfig['theme'] = 'chrome';
                $fieldConfig['showGutter'] = false;
                $fieldConfig['highlightActiveLine'] = false;
                $fieldConfig['cssClass'] = 'pagesTextEditor';
                $fieldConfig['showInvisibles'] = false;
                $fieldConfig['fontSize'] = 13;
                $fieldConfig['margin'] = '20';
            }

            $formWidget->secondaryTabs['fields']['placeholders['.$placeholderCode.']'] = $fieldConfig;

            /*
             * Translation support
             */
            $page->translatable[] = 'placeholders['.$placeholderCode.']';
        }
    }

    protected function getTabTitle($type, $object)
    {
        if ($type == 'page') {
            $viewBag = $object->getViewBag();
            $result = $viewBag ? $viewBag->property('title') : false;
            if (!$result) {
                $result = trans('rainlab.pages::lang.page.new');
            }

            return $result;
        }
        elseif ($type == 'menu') {
            $result = $object->name;
            if (!strlen($result)) {
                $result = trans('rainlab.pages::lang.menu.new');
            }

            return $result;
        }
        elseif ($type == 'content') {
            $result = in_array($type, ['asset', 'content'])
                ? $object->getFileName()
                : $object->getBaseFileName();

            if (!$result) {
                $result = trans('cms::lang.'.$type.'.new');
            }

            return $result;
        }

        return $object->getFileName();
    }

    protected function fillObjectFromPost($type)
    {
        $objectPath = trim(Request::input('objectPath'));
        $object = $objectPath ? $this->loadObject($type, $objectPath) : $this->createObject($type);
        $formWidget = $this->makeObjectFormWidget($type, $object, Request::input('formWidgetAlias'));

        $saveData = $formWidget->getSaveData();
        $postData = post();
        $objectData = [];

        if ($viewBag = array_get($saveData, 'viewBag')) {
            $objectData['settings'] = ['viewBag' => $viewBag];
        }

        $fields = ['markup', 'code', 'fileName', 'content', 'itemData', 'name'];

        if ($type != 'menu' && $type != 'content') {
            $object->parentFileName = Request::input('parentFileName');
        }

        foreach ($fields as $field) {
            if (array_key_exists($field, $saveData)) {
                $objectData[$field] = $saveData[$field];
            }
            elseif (array_key_exists($field, $postData)) {
                $objectData[$field] = $postData[$field];
            }
        }

        if ($type == 'page') {
            $placeholders = array_get($saveData, 'placeholders');

            if (is_array($placeholders) && Config::get('cms.convertLineEndings', false) === true) {
                $placeholders = array_map([$this, 'convertLineEndings'], $placeholders);
            }

            $objectData['placeholders'] = $placeholders;
        }

        if ($type == 'content') {
            $fileName = $objectData['fileName'];

            if (dirname($fileName) == 'static-pages') {
                throw new ApplicationException(trans('rainlab.pages::lang.content.cant_save_to_dir'));
            }

            $extension = pathinfo($fileName, PATHINFO_EXTENSION);

            if ($extension === 'htm' || $extension === 'html' || !strlen($extension)) {
                $objectData['markup'] = array_get($saveData, 'markup_html');
            }
        }

        if ($type == 'menu') {
            // If no item data is sent through POST, this means the menu is empty
            if (!isset($objectData['itemData'])) {
                $objectData['itemData'] = [];
            } else {
                $objectData['itemData'] = json_decode($objectData['itemData'], true);
                if (json_last_error() !== JSON_ERROR_NONE || !is_array($objectData['itemData'])) {
                    $objectData['itemData'] = [];
                }
            }
        }

        if (!empty($objectData['markup']) && Config::get('cms.convertLineEndings', false) === true) {
            $objectData['markup'] = $this->convertLineEndings($objectData['markup']);
        }

        if (!Request::input('objectForceSave') && $object->mtime) {
            if (Request::input('objectMtime') != $object->mtime) {
                throw new ApplicationException('mtime-mismatch');
            }
        }

        $object->fill($objectData);

        /*
         * Rehydrate the object viewBag array property where values are sourced.
         */
        if ($object instanceof CmsCompoundObject && is_array($viewBag)) {
            $object->viewBag = $viewBag + $object->viewBag;
        }

        return $object;
    }

    protected function pushObjectForm($type, $object, $alias = null)
    {
        $widget = $this->makeObjectFormWidget($type, $object, $alias);

        $this->vars['canCommit'] = $this->canCommitObject($object);
        $this->vars['canReset'] = $this->canResetObject($object);
        $this->vars['objectPath'] = Request::input('path');
        $this->vars['lastModified'] = DateTime::makeCarbon($object->mtime);

        if ($type == 'page') {
            $this->vars['pageUrl'] = Url::to($object->getViewBag()->property('url'));
        }

        return [
            'tabTitle' => $this->getTabTitle($type, $object),
            'tab'      => $this->makePartial('form_page', [
                'form'         => $widget,
                'objectType'   => $type,
                'objectTheme'  => $this->theme->getDirName(),
                'objectMtime'  => $object->mtime,
                'objectParent' => Request::input('parentFileName')
            ])
        ];
    }

    protected function bindFormWidgetToController()
    {
        $alias = Request::input('formWidgetAlias');
        $type = Request::input('objectType');
        $objectPath = trim(Request::input('objectPath'));
        $object = $objectPath ? $this->loadObject($type, $objectPath) : $this->createObject($type);

        $widget = $this->makeObjectFormWidget($type, $object, $alias);
        $widget->bindToController();
    }

    /**
     * Replaces Windows style (/r/n) line endings with unix style (/n)
     * line endings.
     * @param string $markup The markup to convert to unix style endings
     * @return string
     */
    protected function convertLineEndings($markup)
    {
        $markup = str_replace("\r\n", "\n", $markup);
        $markup = str_replace("\r", "\n", $markup);

        return $markup;
    }

    /**
     * Returns a list of content files
     * @return \October\Rain\Database\Collection
     */
    protected function getContentTemplateList()
    {
        $templates = Content::listInTheme($this->theme, true);

        /*
         * Extensibility
         */
        if (
            ($event = $this->fireEvent('content.templateList', [$templates], true)) ||
            ($event = Event::fire('pages.content.templateList', [$this, $templates], true))
        ) {
            return $event;
        }

        return $templates;
    }
}

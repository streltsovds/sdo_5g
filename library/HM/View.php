<?php

/**
 * Class HM_View
 */
class HM_View extends Zend_View
{
    /**
     * Navigation-контейнер Главное меню
     * @var HM_Navigation
     */
    private $_mainNavigation = null;

    /**
     * Navigation-контейнер Контекстное меню (если есть)
     * @var HM_Navigation
     */
    private $_contextNavigation = null;

    /**
     * Navigation-контейнер Меню на главной странице над виджетами (если пользователь создал htmlpage)
     * @var HM_Navigation
     */
    private $_indexNavigation = null;

    /**
     * Navigation-контейнер для добавления действий и режимов переключения на любые страницы
     * @var HM_Navigation
     */
    private $_customNavigation = null;

    /**
     * Массив с переменными для подстановки в URLы контекстного меню
     * Без него $_contextNavigation может оказаться бесполезным
     * @var Array
     */
    private $_contextNavigationSubstitutions = array();

    /**
     * Navigation-контейнер статичное меню в футере
     * @var HM_Navigation
     */
    private $_footerNavigation = null;

    /**
     * Navigation-контейнер статичное меню для текущей роли в футере
     * @var HM_Navigation
     */
    private $_footerCurrentRoleNavigation = null;

    /**
     * Navigation-контейнер список действий на странице (слева сверху над гридом и т.п.)
     * @var HM_Navigation
     */
    private $_actionsNavigation = null;

    /**
     * Navigation-контейнер список режимов отображения страницы (список/таблица и т.п.)
     * @var HM_Navigation
     */
    private $_modesNavigation = null;


    /**
     * Тема оформления
     * * logo
     * * skin
     * * title
     *
     * @var Array
     */
    private $_designSettings = array();


    /**
     *  Sidebar'ы доступные на этой странице
     *
     * @var Array
     */
    private $_sidebars = array();


    protected $_header = false;
    protected $_subHeader = false;
    protected $_subSubHeader = false;

    protected $_backUrl = false;
    protected $_switchContextUrls = [];
    protected $_blankPage = false;

    protected $_cache = array(
        'showExitBtn' => array()
    );

    public $_viewVarsExclude = ['grid'];

    public function initView()
    {
        $this
            ->_initMeta()
            ->_initStyles()
            ->_initScripts()
            ->_initVue()
            ->_initDesignSettings()
            ->_initAllNavigation();

        // не стал выносить главное меню в trait
        // для страниц вроде Multipage оно генерится, но не отображается
    }

//    public function encodePhpVars()
//    {
//        $vars = get_object_vars($this);
//        $this->phpVars = json_encode($vars);
//    }

    protected function _initMeta()
    {

        // инициализируем doctype, иначе нельзя установить charset
        $this->doctype('HTML5');

        $this
            ->headMeta()
            ->setCharset('UTF-8')
            ->appendName('viewport', 'width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no, minimal-ui')
            ->appendHttpEquiv('X-UA-Compatible', 'IE=edge')
            ->appendHttpEquiv('Content-Type',
                'text/html; charset=UTF-8')
            ->appendHttpEquiv('Content-Language', 'ru-RU')
        ;

        return $this;
    }

    protected function _initVue()
    {
        $this->hmVue()->init();

        return $this;
    }

    /*
     *  ВНИМАНИЕ!
     *  поскольку используется prependFile,
     *  подключаются в обратном порядке
     */
    protected function _initScripts()
    {
        // все файлы из _common.tpl
        // @todo: отключить лишнее ,оставить необходимое
        // @todo: актуализировать версии
        $this->headScript()->prependFile('/hm/js/hm.min.js');
//        $this->headScript()->prependFile('/js/lib/jquery/jquery.bgiframe.min.js');
//        $this->headScript()->prependFile('/js/lib/dd_belatedpng.js');
//        $this->headScript()->prependFile('/js/script.js');
        $this->headScript()->prependFile('/js/common.js');
//        $this->headScript()->prependFile('/js/polyfills/placeholder.js');
//        $this->headScript()->prependFile('/js/lib/raphael.js');
//        $this->headScript()->prependFile('/js/lib/jquery/jquery.ui.touch-punch.min.js');
        $this->headScript()->prependFile('/js/lib/datastorage-0.6.min.js');
//        $this->headScript()->prependFile('/js/lib/jquery/jquery.ui.selectmenu.min.js');
        $this->headScript()->prependFile('/js/lib/underscore-1.3.3.min.js');

        // временно отключено для форм
        $this->headScript()->prependFile('/js/lib/jquery/jquery-ui-1.8.21.custom.min.js');

//        $this->headScript()->prependFile('/js/lib/jquery/jquery.ba-resize.min.js');
        $this->headScript()->prependFile('/js/lib/jquery/jquery-1.7.2.min.js');
//        $this->headScript()->prependFile('/js/logger.js');
//        $this->headScript()->prependFile('/js/lib/json2.js');
        $this->headScript()->prependFile('/js/lib/modernizr-2.6.1.min.js');
        //$this->headScript()->prependScript("window.eLS_translations = ".HM_Json::encodeErrorSkip(array('alert' => array('title' => _('Информация'), 'ok' => _('OK')), 'confirm' => array('title' => _('Подтверждение действия'),'ok' => _('Да'), 'cancel' => _('Нет')))));


        return $this;
    }

    protected function _initStyles()
    {
        // @todo: актуализировать версии
//        $this->headLink()->prependStylesheet('/css/common.css');

        // вынес в head.tpl
//        $this->headLink()->prependStylesheet('/fonts/materialicons/material-icons.css');
        return $this;
    }

    protected function _initMainMenu()
    {
        $acl = Zend_Registry::get('serviceContainer')->getService('Acl');
        $menuConfig = new HM_Config(require APPLICATION_PATH . '/system/navigation.php', true, 'main');
        $container = new HM_Navigation($menuConfig->main, array());

        // цепляем Acl к Navigation
        $role = Zend_Registry::get('serviceContainer')->getService('User')->getCurrentUserRole();
        $this->navigation()->setAcl($acl)->setRole($role);

        $this->setMainNavigation($container);

        if (!$this->getHeader()) {
            $header = $this->getDesignSetting('windowTitle');
            $this->setHeader($header);
        }

        if (!$this->getSubHeader()) {
            $currentPage = $this->navigation()->findActive($container);
            if (count($currentPage)) {
                $subHeader = $currentPage['page']->getLabel();
                $this->setSubHeader($subHeader);
            }
        }

        return $this;
    }

    protected function _initCustomNavigation()
    {
        $acl = Zend_Registry::get('serviceContainer')->getService('Acl');
        $menuConfig = new HM_Config(require APPLICATION_PATH . '/system/navigation.php', true, 'custom');
        $container = new HM_Navigation($menuConfig->custom, array());

        // цепляем Acl к Navigation
        $role = Zend_Registry::get('serviceContainer')->getService('User')->getCurrentUserRole();
        $this->navigation()->setAcl($acl)->setRole($role);

        $this->setCustomNavigation($container);

//        $currentPage = $this->navigation()->findActive($container);
//        $subSubHeader = count($currentPage) ? $currentPage['page']->getLabel() : false;
//        if ($subSubHeader && !$this->getSubSubHeader()) {
//            $this->setSubSubHeader($subSubHeader);
//        }

        return $this;
    }

    public function _initActionsAndModes()
    {
        if ($containerMain = $this->getMainNavigation()) {
            $currentPage = $this->navigation()->findActive($containerMain);
            $substitutions = array();
        }

        // если есть контекстное меню, ищем actions в нём
        if ($containerContext = $this->getContextNavigation()) {
            $currentPage = $this->navigation()->findActive($containerContext);
            $substitutions = $this->getContextNavigationSubstitutions();
        }

        // если есть кастомные страницы, ищем actions в них
        if ($customContext = $this->getCustomNavigation()) {
            $customPage = $this->navigation()->findActive($customContext);
            $currentPage = $currentPage ?: $customPage;
            $substitutions = array();
        }

        /*foreach ($containerContext as $page) {
                foreach ($page as $subpage) {
                    if($subpage->getLabel() == 'Сервисы') {
                        die(var_dump($subpage));
                    }
                }
        }*/

        $containerActions = false;

        // продублировано в setCurrentPage
        if ($currentPage && isset($currentPage['page']) && isset($currentPage['page']->actions)) {
            $containerActions = new HM_Navigation($currentPage['page']->actions, $substitutions);
        }

        $this->setActionsNavigation($containerActions);

        if ($currentPage && isset($currentPage['page']) && isset($currentPage['page']->modes)) {
            $containerModes = new HM_Navigation($currentPage['page']->modes, $substitutions);
            $this->setModesNavigation($containerModes);
        }

        return $this;
    }

    protected function _initFooterMenu()
    {
        /** @var HM_Htmlpage_HtmlpageService $htmlPageService */
        $htmlPageService = Zend_Registry::get('serviceContainer')->getService('Htmlpage');

        $pages = $htmlPageService->getFooterNavigation();
        $container = new HM_Navigation($pages, array());
        $this->setFooterNavigation($container);

        return $this;
    }

    public function initIndexMenu()
    {
        /** @var HM_Htmlpage_HtmlpageService $htmlPageService */
        $htmlPageService = Zend_Registry::get('serviceContainer')->getService('Htmlpage');

        $currentRolePages = $htmlPageService->getIndexNavigation();

        if (!empty($currentRolePages)) {
            $container = new HM_Navigation($currentRolePages, array());
            $this->setIndexNavigation($container);
        }

        return $this;
    }

    protected function _initAllNavigation() {

        if(!$this->isBlankPage()) {
            $this
                ->_initMainMenu()
                ->_initCustomNavigation()
                ->_initActionsAndModes()
                ->_initFooterMenu();
        }


        return $this;

    }

    protected function _initDesignSettings()
    {
        $config = Zend_Registry::get('config');
        if (isset($config->designSettings)) {
            if (isset($config->designSettings->themeColors)) {
                $this->_designSettings['themeColors'] = $config->designSettings->themeColors->toArray();
            }
            if (isset($config->designSettings->colors)) {
                $this->_designSettings['colors'] = $config->designSettings->colors->toArray();
            }
            if (isset($config->designSettings->darkTheme)) {
                $this->_designSettings['darkTheme'] = intval($config->designSettings->darkTheme);
            }
        }

        /** @var HM_Option_OptionService $optionsService */
        $optionsService = Zend_Registry::get('serviceContainer')->getService('Option');

        $bgSet = ['loginBg1', 'loginBg2', 'loginBg3', 'loginBg4', 'loginBg5'];

        $designOptions = $optionsService->getOptions(HM_Option_OptionModel::SCOPE_DESIGN);
        foreach ($bgSet as $bgFieldName) {
            $bgFileName = $designOptions[$bgFieldName];
            if (!empty($bgFileName)) {
                $this->_designSettings['backgrounds']['random'][] = $bgFileName;
            }
        }

        if ($skin = $designOptions['skin']) {
            $this->_designSettings['skin'] = $skin;

            if (isset($config->designSettings->skins) && isset($config->designSettings->skins->{$skin})) {
                $this->_designSettings['skinColors'] = $config->designSettings->skins->{$skin}->toArray();
            }

        } else {
            // Нет темы, сломался конфиг, что угодно - это стандартные цвета
            $this->_designSettings['skinColors'] = [
                'primary' => '#1976D2',
                'secondary' => '#424242',
                'accent' => '#82B1FF',
                'error' => '#FF5252',
                'info' => '#2196F3',
                'success' => '#4CAF50',
                'warning' => '#FFC107'
            ];
        }

        if ($logo = $designOptions['logo']) {
            $this->_designSettings['logo'] = $logo;
        }

        if ($logoBack = $designOptions['logoBack']) {
            $this->_designSettings['logoBack'] = $logoBack;
        }

        if ($windowTitle = $designOptions['windowTitle']) {
            $windowTitleAppend = $windowTitle;
            if (APPLICATION_ENV == "development") {
                $windowTitleAppend = "[dev] " . $windowTitle;
            }

            $this->_designSettings['windowTitle'] = $windowTitle;

            $this
                ->headTitle()
                ->setSeparator(' :: ')
                ->append($windowTitleAppend);
        }

        if ($themeColors = $designOptions['themeColors']) {
            $this->_designSettings['themeColors'] = $themeColors;
        }


        return $this;
    }

    /**
     * @return HM_Navigation|null
     */
    public function getMainNavigation()
    {
        return $this->_mainNavigation;
    }

    public function setMainNavigation(HM_Navigation $container)
    {
        $this->_mainNavigation = $container;
        return $this;
    }

    /**
     * @return HM_Navigation|null
     */
    public function getCustomNavigation()
    {
        return $this->_customNavigation;
    }

    public function setCustomNavigation(HM_Navigation $container)
    {
        $this->_customNavigation = $container;
        return $this;
    }

    public function hasContextNavigation()
    {
        return !empty($this->_contextNavigation);
    }

    public function getContextNavigation()
    {
        return $this->_contextNavigation;
    }

    public function getIndexNavigation()
    {
        return $this->_indexNavigation;
    }

    public function getContextNavigationSubstitutions()
    {
        return $this->_contextNavigationSubstitutions;
    }

    public function setContextNavigation(HM_Navigation $container, $substitutions = array())
    {
        $this->_contextNavigation = $container;
        $this->_contextNavigationSubstitutions = $substitutions;
        return $this;
    }

    /**
     * @return HM_Navigation
     */
    public function getFooterNavigation()
    {
        return $this->_footerNavigation;
    }

    /**
     * @return HM_Navigation
     */
    public function getFooterCurrentRoleNavigation()
    {
        return $this->_footerCurrentRoleNavigation;
    }

    /**
     * @param HM_Navigation $footerNavigation
     */
    public function setFooterNavigation($footerNavigation)
    {
        $this->_footerNavigation = $footerNavigation;
        return $this;
    }
    /**
     * @param HM_Navigation $indexNavigation
     */
    public function setIndexNavigation($indexNavigation)
    {
        $this->_indexNavigation = $indexNavigation;
        return $this;
    }

    /**
     * @return HM_Navigation
     */
    public function getActionsNavigation()
    {
        /**
         * Если false, значит, вид был проинициализирован, но действий не найдено
         *   и повторно инициализировать не нужно.
         */
        if (is_null($this->_actionsNavigation)) {
            /**
             * Например, ajax-запрос для grid, где нужно получить обновлённые действия
             * с изменёнными параметрами.
             */
            $this->_initAllNavigation();
        }

        return $this->_actionsNavigation;
    }

    /**
     * @param HM_Navigation $actionsNavigation
     */
    public function setActionsNavigation($actionsNavigation)
    {
        $this->_actionsNavigation = $actionsNavigation;
        return $this;
    }

    /**
     * @return HM_Navigation
     */
    public function getModesNavigation()
    {
        return $this->_modesNavigation;
    }

    /**
     * @param HM_Navigation $modesNavigation
     */
    public function setModesNavigation($modesNavigation)
    {
        $this->_modesNavigation = $modesNavigation;
        return $this;
    }

    /**
     * @return array
     */
    public function getDesignSettings()
    {
        return $this->_designSettings;
    }

    public function getDesignSetting($key)
    {
        return isset($this->_designSettings[$key]) ? $this->_designSettings[$key] : false;
    }

    public function getSettingsColor($colorName) {
        $colors = $this->getDesignSetting('colors');
        if (!$colors) {
            return false;
        }
        if (isset($colors[$colorName])) {
            return $colors[$colorName];
        }
    }

    public function getRandomBackground() {
        $backgrounds = $this->getDesignSetting('backgrounds');
        if (!$backgrounds || !isset($backgrounds['random']) || !$backgrounds['random']) {
            return false;
        }

        $randomBackgrounds = $backgrounds['random'];

        $index = rand(0, count($randomBackgrounds) - 1);
        return $randomBackgrounds[$index];
    }

    /**
     * @param array $designSettings
     */
    public function setDesignSettings($designSettings)
    {
        $this->_designSettings = $designSettings;
        return $this;
    }

    /**
     * @return array
     */
    public function getSidebars()
    {
        $this->sortSidebars();
        $this->setOpened();
        return $this->_sidebars;
    }

    /**
     * @return array
     */
    public function sortSidebars()
    {
        // sidebar'ы, соответствующие HM_Controller_Action_Trait_Context,
        // должны иметь нулевой order (или просто не задан)

        uasort($this->_sidebars, function($item1, $item2){
            $options1 = $item1->getOptions();
            $options2 = $item2->getOptions();
            $order1 = isset($options1['order']) ? $options1['order'] : 0;
            $order2 = isset($options2['order']) ? $options2['order'] : 0;
            return ((int)$order1 < (int)$order2) ? -1 : 1;
        });
    }

    public function setOpened()
    {
        if (count($this->_sidebars)) {
            array_walk($this->_sidebars, function($sidebar){
                $sidebar->setOpened(false);
            });

            if (count($this->_sidebars) > 2) { // не надоедаем пользователям лентой активности
                $arrayKeys = array_keys($this->_sidebars);
                $key = array_pop($arrayKeys);
                $this->_sidebars[$key]->setOpened(true);
            }
        }
    }

    /**
     * @param array $sidebars
     */
    public function setSidebars($sidebars)
    {
        $this->_sidebars = $sidebars;
        return $this;
    }

    /*
     *  Этот метод использовать в дочерних классах,
     *  при необходимости включить добавить sidebar.
     *  Может принимать на вход название siderbar'а
     *  или сразу объект (если его надо как-то особенно инициализировать из контроллера)
     *
     *  @param mixed $nameOrObject
     */
    public function addSidebar($nameOrObject, $options = [])
    {
        $sidebar = null;
        /** @var HM_View_Helper_HmVue $hmVue */
        $hmVue = $this->hmVue();
        if (is_string($nameOrObject)) {
            $name = $nameOrObject;
            if (!isset($this->_sidebars[$name])) {

                // to camelcase
                $nameOrObject = lcfirst(str_replace('-', '', ucwords($nameOrObject, '-')));
                $sidebar = $this->_sidebars[$name] = $this->$nameOrObject();
            }
        } elseif (is_subclass_of($nameOrObject, 'HM_View_Sidebar_Abstract')) {
            $sidebar = $nameOrObject;
            $name = $sidebar->getName();
            if (!isset($this->_sidebars[$name])) {
                $this->_sidebars[$name] = $sidebar;
            }
        }

        if ($sidebar) {
            $sidebar->setOptions($options);
//            $hmVue->registerDataItem('is'.$name.'Shown', false, true); // ?
        }
        return $this;
    }

    /**
     * @param $search string|object
     * @param $replace string|object
     * @param $options array
     * @return HM_View
     */
    public function removeSidebar($nameOrObject)
    {
        if (is_string($nameOrObject)) {
            unset($this->_sidebars[$nameOrObject]);
        } elseif ($nameOrObject instanceof HM_View_Sidebar_Abstract) {
            unset($this->_sidebars[$nameOrObject->getName()]);
        }

        return $this;
    }

    /**
     * @param $search string|object
     * @param $replace string|object
     * @param $options array
     * @return HM_View
     */
    public function replaceSidebar($search, $replace, $options)
    {
        $this->removeSidebar($search);
        return $this->addSidebar($replace, $options);
    }

    public function getHeader()
    {
        return $this->_header;
    }

    public function setHeader($header, $backUrl = false)
    {
        if(!$this->isBlankPage()) {
            $this->_header = $header;
            /** @see Zend_View_Helper_HeadTitle */
            $this->headTitle($header, Zend_View_Helper_Placeholder_Container_Abstract::SET);
        }

        if ($backUrl) {
            $this->_backUrl = $backUrl;
        }

        return $this;
    }

    public function getSubHeader()
    {
        return $this->_subHeader;
    }

    public function setSubHeader($subHeader)
    {
        // не может быть 2 заголовка
        if (empty($this->getSubSubHeader())) {
            $this->_subHeader = $subHeader;
            $this->headTitle()->append($subHeader);
        }
        return $this;
    }

    /**
     * @return bool
     */
    public function getSubSubHeader()
    {
        return $this->_subSubHeader;
    }

    /**
     * @param bool $subSubHeader
     */
    public function setSubSubHeader($subSubHeader)
    {
        $this->_subSubHeader = $subSubHeader;
        return $this;
    }

    /**
     * @return string
     */
    public function getBackUrl()
    {
        return $this->_backUrl;
    }

    /**
     * @param string $backUrl
     */
    public function setBackUrl($backUrl)
    {
        $this->_backUrl = $backUrl;
        return $this;
    }

    /**
     * @return array
     */
    public function getSwitchContextUrls()
    {
        return ($this->_switchContextUrls && count($this->_switchContextUrls))
            ? $this->_switchContextUrls : false;
    }

    /**
     * @param array $switchContextUrls
     */
    public function setSwitchContextUrls($switchContextUrls)
    {
        $this->_switchContextUrls = $switchContextUrls;
        return $this;
    }

    public function setCurrentPage($pageId)
    {
        $contextNavigation = $this->getContextNavigation();
        $currentPage = $contextNavigation->findOneBy('id', $pageId);
        $substitutions = $this->getContextNavigationSubstitutions();

        if ($currentPage) {

            $currentPage->setActive(true);

            // продублировано в _initActionsMenu
            if (isset($currentPage->actions)) {

                $containerActions = new HM_Navigation($currentPage->actions, $substitutions);
                $this->setActionsNavigation($containerActions);
            }

            if (isset($currentPage->modes)) {

                $containerModes = new HM_Navigation($currentPage->modes, $substitutions);
                $this->setModesNavigation($containerModes);
            }

        }
    }

    public function unsetActionById($id)
    {
        $container = $this->getActionsNavigation();
        if ($page = $container->findOneBy('id', $id)) {
            $container->removePage($page);
        }
    }

    public function getRequest()
    {
        return Zend_Controller_Front::getInstance()->getRequest();
    }

    public function __call($name, $args)
    {
        // is the helper already loaded?
        $helper = $this->getHelper($name);

        $config = Zend_Registry::get('config');
        if ($config->cache->enabled) {
            if (method_exists($helper, 'getCachedContent') && empty($helper->disableCache)) {
                if ($content = $helper->getCachedContent()) {

                    if (method_exists($helper, 'getNotCachedContent')) {
                        $helper->getNotCachedContent();
                    }
                    return $content;
                }
            }
        }

        // call the helper method
        return call_user_func_array(
            array($helper, $name),
            $args
        );
    }

    public function getSocialLinks()
    {
        return Zend_Registry::get('serviceContainer')->getService('Option')->getOptions(HM_Option_OptionModel::SCOPE_SOCIAL);
    }

    public function showExitBtn()
    {
        if ($this->_cache['showExitBtn']) {
            $output = $this->_cache['showExitBtn'];
        } else {
            $loginStart = $this->getService('Option')->getOption('loginStart');
            $loginBlockEnabled = $this->getService('Infoblock')->fetchRow([
                'block = ?' => 'Authorization',
                'role = ?' => HM_Role_Abstract_RoleModel::ROLE_GUEST,
            ]);

            $currentUserId = $this->getService('User')->getCurrentUserId();

            if ((!$loginStart and !$loginBlockEnabled) or $currentUserId) {
                $output = true;
            } else {
                $output = false;
            }

            $this->_cache['showExitBtn'] = $output;
        }

        return $output;
    }

    protected function getService($serviceName)
    {
        return Zend_Registry::get('serviceContainer')->getService($serviceName);
    }

    // TODO почему возникают вложения view?
//    public function _viewVarsPreventRecursion($viewVars) {
//    }

    /**
     * Отсеивание window.__HM.php_view_vars кроме простых типов данных и массивов
     *
     * @param $viewVars
     * @return array
     */
    public function _viewVarsFilterObjects($viewVars) {
        $result = [];

        $viewVarsFilterObjects = [];

        foreach (array_keys($viewVars) as $key) {
            $var = $viewVars[$key];
            if (is_object($var) && get_class($var) != 'stdClass') {
                $viewVarsFilterObjects[] = $key;
                continue;
            }
            if (is_array($var)) {
                $result[$key] = $this->_viewVarsFilterObjects($var);
                continue;
            }
            $result[$key] = $var;
        }

        if ($viewVarsFilterObjects) {
            $result['_viewVarsFilterObjects'] = $viewVarsFilterObjects;
        }

        return $result;
    }

    /** @see `application/views/layout/default.tpl`, `window.__HM.php_view_vars = ` */
    public function getViewVars() {
        $viewVars = get_object_vars($this);

        // отключить/отладить преобразования ниже, если будут проблемы
        $viewVars = array_diff_key($viewVars, array_flip($this->_viewVarsExclude));
        $viewVars = $this->_viewVarsFilterObjects($viewVars);
        return $viewVars;
    }

    /**
     * Оборачивать этой функцией url на ассеты (стили, скрипты): если файл обновился на сервере,
     * добавляемый хэш-параметр после '?' заставит браузер пользователя перезагрузить файл.
     *
     * скопировано из 4g: project/danone/develop2: HM_View_Extended::cleanCacheOnAssetUpdateModUrl()
     *
     * @param string|null $publicFileRelativeUrl - Только с папки `public`. Пример: "/js/myfile.js". Должно начинаться с "/"
     * @return string|null - Пример: "/js/myfile.js?1563881134"
     */
    public function publicFileToUrlWithHash($publicFileRelativeUrl) {
        if (!$publicFileRelativeUrl) {
            return $publicFileRelativeUrl;
        }

        $hash = filemtime(PUBLIC_PATH . $publicFileRelativeUrl);
        return $publicFileRelativeUrl . '?' . $hash;
    }

    /**
     * @return bool
     */
    public function isBlankPage()
    {
        return $this->_blankPage;
    }

    /**
     * @param bool $blankPage
     * @return HM_View
     */
    public function setBlankPage(bool $blankPage)
    {
        $this->_blankPage = $blankPage;
        return $this;
    }


}
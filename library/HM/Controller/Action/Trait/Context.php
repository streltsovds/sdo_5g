<?php

/**
 * Sets the page environment. Use this instead of ::setExtended() from older versions.
 * Trait HM_Controller_Action_Trait_Context
 * @property HM_View $view
 */
trait HM_Controller_Action_Trait_Context
{
    /**
     * Entity for current page, that using to substitute into environment.
     */
    protected $_contextEntity;

    /*
     *  Класс сущности (может использоваться родительский класс)
     */
    protected $_contextEntityType;

    /**
     * Список блоков навигации для отображения в контекстном меню
     * @var HM_Navigation
     */
    protected $_contextMenus = array();

    /**
     * Navigation-контейнер Контекстное меню (если есть)
     * @var HM_Navigation
     */
    protected $_contextNavigation = null;

    public function initContext(HM_Model_Abstract $contextEntity, $contextEntityType = false)
    {
        $this->_contextEntity = $contextEntity;
        $this->_contextEntityType = $contextEntityType ? : $this->_autodetectEntityType();

        $this->_initContextView();
    }

    protected function _initContextView()
    {
        if (!$this->view->getHeader()) {
            $this->view->setHeader($this->_contextEntity->getName());
        }

        $this->_initDefaultContextMenu();

        return $this;
    }


    /**
     * default - это значит по названию сущности
     * @return $this
     */
    protected function _initDefaultContextMenu()
    {
        $this->_contextMenus[$this->_contextEntityType] = $this->_contextEntityType;
        $this->_setContextNavigation();

        return $this;
    }

    public function addContextMenus($menus)
    {
        foreach ((array) $menus as $menu) {
            $this->_contextMenus[$menu] = $menu;
        }
        $this->_setContextNavigation();

        return $this;
    }

    // надо дёргать каждый раз при изменении $this->_contextMenus
    protected function _setContextNavigation()
    {
        $navigation = require APPLICATION_PATH . '/system/navigation.php';
        $menuConfig = new HM_Config($navigation['context'], true, $this->_contextMenus);

        $substitutions = $this->getContextNavigationSubstitutions();
        $this->_contextNavigation = new HM_Navigation($menuConfig, $substitutions);

        $modifiers = $this->getContextNavigationModifiers();
        foreach ($modifiers as $modifier) {
            $modifier->process($this->_contextNavigation);
        }

        $this->view->setContextNavigation($this->_contextNavigation, $substitutions);

        $currentPage = $this->view->navigation()->findActive($this->_contextNavigation);

        // не показываем заголовок если страница и так видна в контекстном меню
        $subSubHeader = (count($currentPage) && ($currentPage['depth'] > 1)) ? $currentPage['page']->getLabel() : false;
        if ($subSubHeader && !$this->view->getSubSubHeader()) {
            $this->view->setSubSubHeader($subSubHeader);
        }

        return $this;
    }

    protected function _disableContextNavigation()
    {
        $this->view->setContextNavigation(null);

        return $this;
    }

    protected function _autodetectEntityType()
    {
        preg_match('#\w*_(\w*)Model#', get_class($this->_contextEntity), $result);
        return strtolower($result[1]);
    }

    public function getActiveContextMenu()
    {
        return $this->_contextNavigation->findOneBy('active', true);
    }

    public function setActiveContextMenu($id, $setDependentMenus = false)
    {
        if ($this->_contextNavigation) {
            if ($page = $this->_contextNavigation->findOneBy('id', $id)) {

                $page->setActive(true);

                // $this->view->setSubHeader($page->getLabel());

                // если нужно подтянуть действия на странице и переключатель
                // от базовой страницы (не всегда это надо)
                if ($setDependentMenus) {

                    $substitutions = $this->getContextNavigationSubstitutions();

                    if (isset($page->actions)) {
                        $containerActions = new HM_Navigation($page->actions, $substitutions);
                        $this->view->setActionsNavigation($containerActions);
                    };

                    if (isset($page->modes)) {
                        $containerModes = new HM_Navigation($page->modes, $substitutions);
                        $this->view->setModesNavigation($containerModes);
                    };
                }
            }
        }
    }
}
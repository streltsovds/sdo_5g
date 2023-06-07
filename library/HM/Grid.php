<?php

class HM_Grid extends HM_Grid_AbstractGrid
{
    use HM_Grid_Trait_VueGetMarkup;

    protected static $_defaultOptions = array(
        'view'        => null,
        'select'      => null,
        'userService' => null,
    );
    protected $view;
    protected $serviceContainer;

    public function __construct()
    {
        $argument = func_get_arg(0);
        parent::__construct($argument);

    }

    public function init($view = null, $gridElements = null)
    {
        $this->serviceContainer = Zend_Registry::get('serviceContainer');

        if ($view instanceof HM_View) {
            $this->view = $view;
        }

        if ($view instanceof Zend_Db_Select) {
            $this->_source = $view;
        }

        return parent::init($this->_source, $gridElements = null);
    }

    protected function _initColumns($gridElements = null)
    {

    }

    protected function _initFilters(HM_Grid_FiltersList $filters)
    {

    }

    protected function _initSwitcher(HM_Grid_Switcher $switcher)
    {

    }

    protected function _initActions(HM_Grid_ActionsList $actions)
    {

    }

    protected function _initGridMenu(HM_Grid_Menu $menu)
    {

    }

    protected function _initMassActions(HM_Grid_MassActionsList $massActions)
    {

    }

    protected function _getDefaultFilterValues()
    {
        /*
         * return array(
         *     'field_name' => 'filter_value'
         * );
         */
        return array();
    }

    /**
     * Внутри функции полезно использовать
     *
     * $actions->setInvisibleActions(array(
     *     'edit',
     *     'delete'
     * ));
     *
     * @param $row
     * @param HM_Grid_ActionsList $actions
     */
    public function checkActionsList($row, HM_Grid_ActionsList $actions)
    {

    }

    public function updatePositionName($name, $soid, $type, $isManager)
    {
        if (empty($name)) return '';

        if ($this->getService('Acl')->inheritsRole($this->getService('User')->getCurrentUserRole(), HM_Role_Abstract_RoleModel::ROLE_ENDUSER)) return $name;

        if ($type == HM_Orgstructure_OrgstructureModel::TYPE_DEPARTMENT) {
            $name = '<a href="'.$this->view->url(array('module' => 'orgstructure', 'controller' => 'index', 'action' => 'index', 'org_id' => $soid), null, true).'">'.$name.'</a>';
        }

        return $this->getView()->cardLink(
                $this->getView()->url(array(
                        'module' => 'orgstructure',
                        'controller' => 'list',
                        'action' => 'card',
                        'org_id' => '',
                        'baseUrl' => '')
                ) . $soid,
                _('Карточка должности'), //HM_Orgstructure_OrgstructureService::getIconTitle($type, $isManager),
                'icon-custom',
                'pcard',
                'pcard',
                'orgstructure-icon-small ' . HM_Orgstructure_OrgstructureService::getIconClass($type, $isManager)
            ) . $name;
    }
}
<?php

class HM_Grid_Switcher extends HM_Grid_AbstractClass
{
    protected $_items = array();
    /**
     * @var HM_Grid_AbstractGrid
     */
    protected $_grid;

    protected $_defaultValue = null;

    public function __construct(HM_Grid_AbstractGrid $grid)
    {
        $this->_grid = $grid;
    }

    public function add($title, $value, $filterParams = array())
    {
        $item = new HM_Grid_SwitcherItem($title, $value, $filterParams);

        $this->_items[] = $item;

        return $item;
    }

    public function getConfig()
    {
        if (empty($this->_items)) {
            return null;
        }

        $this->_checkSwitcherValue();

        $result = array();

        /** @var HM_Grid_SwitcherItem $item */
        foreach ($this->_items as $item) {
            $result[] = $item->getConfig();
        }

        return $result;

    }

    public function getDefaultValue()
    {
        if ($this->_defaultValue !== null) {
            return $this->_defaultValue;
        }

        if (empty($this->_items)) {
            return false;
        }

        /** @var HM_Grid_SwitcherItem $defaultItem */
        $defaultItem = $this->_items[0];

        return $defaultItem->getValue();
    }

    /**
     * Делает проверку, настроен ли свитчер в рамках текущего реквеста.
     * Если нет, устанавливает дефолтное значение в сессию
     */
    protected function _checkSwitcherValue()
    {
        if (empty($this->_items)) {
            return;
        }

        $request = $this->getRequest();

        $all = $request->getParam('all', NULL);

        // проверяем, есть ли значение свитчера в реквесте
        if ($all !== NULL) {
            return;
        }

        // в реквесте нет, смотрим сессию
        $gridData = &Bvb_Grid::getGridSessionData($this->_grid->getGridId());

        if (!isset($gridData['all'])) {
            $gridData['all'] = $this->getDefaultValue();
        }

    }

    public function setDefaultValue($defaultValue)
    {
        /** @var HM_Grid_SwitcherItem $item */
        foreach ($this->_items as $item) {
            if ($item->getValue() === $defaultValue) {
                $this->_defaultValue = $defaultValue;
                break;
            }
        }

    }

    public function getValue()
    {
        $this->_checkSwitcherValue();

        return Bvb_Grid::getGridSwitcherParamById($this->_grid->getGridId());
    }

    public function isVisible()
    {
        $currentRole = $this->getService('User')->getCurrentUserRole();

        return !empty($this->_items) && !$this->getService('Acl')->inheritsRole($currentRole,
                array(HM_Role_Abstract_RoleModel::ROLE_SUPERVISOR));
    }
}
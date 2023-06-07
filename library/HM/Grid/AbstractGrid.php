<?php

abstract class HM_Grid_AbstractGrid extends HM_Grid_ConfigurableClass
{
    /**
     * @var Bvb_Grid
     */
    protected $_grid = null;
    protected $_source = null;
    protected $_sourceOriginal = null;

    protected $_columns = null;
    protected $_classRowCondition = array();
    protected $_columnsList = null;

    private $_switcher = null;
    private $_actions = null;
    private $_massActions = null;
    private $_filters = null;
    private $_menu = null;

    protected static $_defaultOptions = array(

        /**
         * Идентификатор грида
         */
        'gridId' => 'grid',

        /**
         * Порядок сортировки таблицы по-умолчанию
         * Например: 'fio_ASC' или 'fio_DESC'
         */
        'defaultOrder' => '',

        /**
         * Контроллер, в котором создаётся грид. Не рекомендуется использовать.
         */
        'controller' => null,

        /**
         * Если true, то пользователь сможет фиксировать строки
         */
        'allowFixRows' => false,

    );

    // ===============================================================================
    //
    //                             Геттеры для опций
    //
    // ===============================================================================

    /**
     * @return Bvb_Grid
     */
    public function getGridId()
    {
        return $this->_options['gridId'];
    }

    /**
     * @return string
     */
    public function getDefaultOrder()
    {
        return $this->_options['defaultOrder'];
    }

    /**
     * @return Zend_Controller_Action
     */
    public function getController()
    {
        return $this->_options['controller'];
    }

    // ===============================================================================
    //
    //                            Абстрактные методы
    //
    // ===============================================================================

    abstract protected function _initSwitcher(HM_Grid_Switcher $switcher);
    abstract protected function _initActions(HM_Grid_ActionsList $actions);
    abstract protected function _initMassActions(HM_Grid_MassActionsList $massActions);
    abstract protected function _initGridMenu(HM_Grid_Menu $menu);
    abstract protected function _getDefaultFilterValues();
    abstract public function checkActionsList($row, HM_Grid_ActionsList $actions);

    /**
     * Лучше не использовать, старая инициализация столбцов
     */
    protected function _initColumns($gridElements = null)
    {

    }

    /**
     * Инициализация столбцов
     */
    protected function _initCols(HM_Grid_Columns $columns)
    {

    }

    protected function _initFilters(HM_Grid_FiltersList $filters)
    {

    }

    // ===============================================================================
    //
    //                      Не понятно, что с этим хламом делать
    //
    // ===============================================================================

    private function &_getSessionData()
    {
        return Bvb_Grid::getGridSessionData($this->getGridId());
    }

    private function _initDefaultFilters()
    {
        if ($this->isAjaxRequest()) {
            return;
        }

        $defaultFilters = $this->_getDefaultFilterValues();

        if (empty($defaultFilters)) {
            return;
        }

        $gridData = &$this->_getSessionData();

        if (!isset($gridData['filters'])) {
            $gridData['filters'] = array();
        }

        $gridData['filters'] = array_merge($defaultFilters, $gridData['filters']);

    }

    private function _initOrder()
    {
        $default = $this->getDefaultOrder();

        if (!$default) {
            return;
        }

        $gridId  = $this->getGridId();

        $request = $this->getRequest();

        $requestOrder = $request->getParam("order{$gridId}", false);

        if ($requestOrder) {
            return;
        }

        $gridData = &$this->_getSessionData();

        if (!empty($gridData['order'])) {
            return;
        }

        $gridData['order'] = $default;

    }

    // ===============================================================================
    //
    //                            Публичные функции
    //
    // ===============================================================================

    public function __construct($options)
    {
        parent::__construct($options);
        $this->_columnsList = new HM_Grid_Columns($this);
        $this->_filters     = new HM_Grid_FiltersList($this);
        $this->_actions     = new HM_Grid_ActionsList($this);
        $this->_massActions = new HM_Grid_MassActionsList($this);
        $this->_menu        = new HM_Grid_Menu();
        $this->_switcher    = new HM_Grid_Switcher($this);

        $this->_initSwitcher($this->_switcher); // свитчер важен ещё до инициализации грида

    }

    /**
     * @return Bvb_Grid_Source_Zend_Select
     */
    public function getSource()
    {
        return $this->_source;
    }

    public function getClassOfRow($row)
    {
        return '';
    }

    public function init($source = null, $gridElements = null)
    {
        $this->_sourceOriginal = $source;

        if (!empty($source)) {
            if (is_array($source)) {
                $source = new HM_Grid_Source_Array($source, null);
            } elseif ($source instanceof Zend_Db_Select) {
                $source = new Bvb_Grid_Source_Zend_Select($source);
            }
        }

        $this->_source = $source;

        $config = Zend_Registry::get('config');

        $tempDir = $config->path->upload->tmp;

        $columnsList = $this->_columnsList;

        $this->_initCols($columnsList);

        if ($columnsList->isEmpty()) {
            $gridElements ? $this->_initColumns($gridElements) : $this->_initColumns();
        } else {
            $this->_columns = $columnsList->getColumnsConfig();
        }

        $event = new sfEvent(null, HM_Extension_ExtensionService::EVENT_FILTER_GRID_COLUMNS);
        Zend_Registry::get('serviceContainer')->getService('EventDispatcher')->filter($event, $this->_columns);
        $summaryOptions = $event->getReturnValue();

        /** @var Bvb_Grid $grid */
        $this->_grid = $grid = Bvb_Grid::factory(
            'vue',
            array(
                'deploy' => array(
                    'excel' => array(
                        'download' => 1,
                        'dir' => $tempDir
                    ),
                    'word' => array(
                        'download' => 1,
                        'dir' => $tempDir
                    ),
                ),
                'summaryOptions' => $summaryOptions
            ),
            $this->getGridId()
        );

        $grid->setSource($source);

        $grid->setClassRowCallback(array($this, 'getClassOfRow'));

        $this->_initOrder();
        $this->_initDefaultFilters();

        $this->_initFilters($this->_filters);
        $this->_initFixRows();

        if (!$columnsList->isEmpty()) {

            $filters = $columnsList->getFilterConfig();
            $columnFilters = array();

            foreach ($filters as $field => $filter) {
                $columnFilters[$field] = $filter;
            }

            $this->_filters->add($columnFilters);

        }

        $this->_initGridMenu($this->_menu);
        $this->_initActions($this->_actions);

        $builder = new HM_Grid_Builder($grid);

        $builder->createGrid(array(
            'columns'  => $this->_columns,
            'filters'  => $this->_filters->getFilters(),
            'actions'  => $this->_actions,
            'switcher' => $this->_switcher->getConfig(),
            'classRowCondition' => $this->_classRowCondition
        ));

//        if ($this->_grid instanceof Bvb_Grid_Deploy_Table) {
            $this->_initMassActions($this->_massActions);
//        }
        $return = $this->deploy();
        return $return;
    }

    protected function _initFixRows()
    {
        if (!$this->getOption('allowFixRows')) {
            return;
        }

        $pk = $this->_findPrimaryKey();

        if (!$pk) {
            return;
        }

        $column = $pk['primaryKey'];
        $request = $this->getRequest();

        $this->_grid->addFixedRows(
            $request->getModuleName(),
            $request->getControllerName(),
            $request->getActionName(),
            $column
        );

        $this->_columns['fixType'] = array(
            'hidden' => true,
        );

    }

    protected function _findPrimaryKey()
    {
        /** @var Bvb_Grid_Source_Zend_Select $gridSource */
        $gridSource = $this->_grid->getSource();

        if (!($gridSource instanceof Bvb_Grid_Source_Zend_Select)) {
            return false;
        }

        $tables = $gridSource->getTableList();

        foreach ($tables as $alias => $table) {
            if ($table['joinType'] === 'from') {
                $tableName = $table['tableName'];
                $tableColumns = $gridSource->getDescribeTable($tableName);

                foreach ($tableColumns as $tableColumn) {
                    if ($tableColumn['IDENTITY']) {
                        return array(
                            'alias' => $alias,
                            'name' => $tableName,
                            'primaryKey' => $tableColumn['COLUMN_NAME']
                        );
                    }
                }
            }
        }

        return false;
    }

    /**
     * @var HM_Grid_ViewSwitcher
     */
    protected $_viewSwitcher = '';

    public function setViewSwitcher(HM_Grid_ViewSwitcher $switcher)
    {
        $this->_viewSwitcher = $switcher;
    }

    protected $_gridDeployed = false;

    public function deploy()
    {
        $return = $this->_grid->getMarkup();
        if ($this->isAjaxRequest()) $return = $this->_grid->deploy(true);

        $this->_gridDeployed = true;
        return $return;
    }

    public function __toString()
    {
        if (!$this->_gridDeployed) {
            $this->deploy();
        }

        return $this->_viewSwitcher.$this->_menu.$this->_grid;
    }

    public function getSwitcher()
    {
        return $this->_switcher;
    }

    public function getBvbGrid()
    {
        return $this->_grid;
    }

    public function getColumns()
    {
        return $this->_columns;
    }

    /**
     * @deprecated Используйте переопределение функции getClassOfRow
     * @param $condition
     * @param $class
     * @param string $else
     * @return $this
     */
    public function setClassRowCondition($condition, $class, $else = '')
    {
        $this->_classRowCondition[] = array('condition' => $condition, 'class' => $class, 'else' => $else);
        return $this;
    }

}

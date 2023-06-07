<?php

class Bvb_Grid_Deploy_Vue extends Bvb_Grid implements Bvb_Grid_Deploy_DeployInterface
{
    const FILTER_TYPE_SELECT = 'select';
    const FILTER_TYPE_TEXT = 'text';

    protected $totalRecordsBeforePagination;

    protected $defaultMassActionColumn;

    protected $_subMassActionSelects = array();

    protected $_subMassActionFcbk = array();

    protected $_subMassActionInput = array();

    public $loadUrl = null;

    /**
     * CSS classes to be used
     * @var array
     */
    protected $_cssClasses = array('odd' => 'alt odd', 'even' => 'even');

    public $deploy = array();

    const OUTPUT = 'vue';

    public $autoGetHeaderActionsOnDeploy = true;

    /*
    * @param array $data
    */
    public function __construct ($options, $gridId = null)
    {
        try {
            parent::__construct($options, $gridId);
        } catch (Bvb_Grid_Exception $e) {}
    }

    public function getDefaultMassActionColumn() {
        if (!$this->defaultMassActionColumn) {
            $primary = $this->getSource()->getPrimaryKey($this->_data['table']);

            if ($primary instanceof Zend_Db_Expr) {
                $primary = $primary->__toString();
            }
            else {
                // Если точка есть в праймари кее
                foreach ($primary as &$val) {
                    $explode = explode('.', $val);
                    if (count($explode) > 1) {
                        $val = $explode[1];
                    }
                }

                $primary = implode('-', $primary);
            }

            $this->defaultMassActionColumn = $primary;
        }
        return $this->defaultMassActionColumn;
    }

    public function buildDataJson ($classRowConditions)
    {
        $grids = (array) parent::_buildGrid();
        $grid = array();
        $i = 0;
        foreach ($grids as $value) {

            $actions = [];
            foreach ($this->_actions as $action) {
                $actionsTemp = array();
                foreach ($action as $key => $item) {
                    $actionsTemp[$key] = ($key == 'url') ? $this->replaceParams($value, $item) : $item;
                }
                $actions[] = $actionsTemp;
            }

            if (!empty($this->_actionsCallback)) {

                $callParams = [];

                if (is_array($this->_actionsCallback['params'])) {
                    foreach ($this->_actionsCallback['params'] as $callbackParamMask) {
                        $callParams[] = $this->replaceParams($value, $callbackParamMask, true);
                    }
                }
                $callParams[] = $actions;

                $actions = call_user_func_array(
                    $this->_actionsCallback['function'],
                    $callParams
                );
            }

            $grid1 = new stdClass();
            foreach ($value as $final) {
                $grid1->{$final['field']} = htmlspecialchars($final['value']);
                $grid1->actions = $actions;
            }

            foreach ($classRowConditions as $classRowCondition) {
                if(empty($grid1->highlighted)) {
                    $grid1->highlighted = $this->highlightedClass($classRowCondition, $value);
                }

            }

            $grid[] = $grid1;
            $i ++;
        }

        return $grid;

    }

    protected function highlightedClass($classRowCondition, $value)
    {
        $function  = null;
        $result    = '';
        $condition = $classRowCondition['condition'] ?? $result;
        $class     = $classRowCondition['class']     ?? $result;
        $else      = $classRowCondition['else']      ?? $result;

        foreach ($value as $final)
            if (false !== strpos($condition, '{{'. $final['field'] . '}}'))
                $condition = str_replace('{{'. $final['field'] . '}}', $final['raw_value'], $condition);

        if (!empty($condition)) {
            $function = function () use ($condition) {
                return eval('if (' . $condition . ') {return true;} else {return false;}');
            };
            $result = $function() ? $class : $else;
        }

        return $result;
    }

    protected function replaceParams($params, $string, $raw = false)
    {
        $result = $string;
        foreach ($params as $array) {
            $value = $raw ? $array['raw_value'] : $array['value'];
            if (false !== strpos($string, '{{'.$array['field'].'}}')) {
                $result = str_replace('{{'.$array['field'].'}}', $value, $result);
            }
        }
        return is_null($result) ? $string : $result;
    }

    public function buildFiltersJson($filters)
    {
        foreach ($filters as $filterKey => $filterValue) {
            if (isset($this->_filtersValues[$filterKey])) {
                if (isset($filters[$filterKey]['values'])) {
                    $filters[$filterKey]['selected'] = $this->_filtersValues[$filterKey];
                } elseif (isset($filters[$filterKey]['render'])) {
                    $filters[$filterKey]['value'] = $this->_filtersValues[$filterKey];
                } else {
                    $filters[$filterKey] = $this->_filtersValues[$filterKey];
                }
            }
        }

        $transformFilters = function($filter) {
            $filterType = null;
            $filterValue = null;

            // если у нас есть такая штука то это селект
            if (isset($filter['values'])) {
                $filterType = self::FILTER_TYPE_SELECT;

                // конвертируем опции селекта в нужную структуру
                $filterValue = array_map(function($value, $key) {
                    return array(
                        'key' =>  $key,
                        'value' => $value
                    );
                }, $filter['values'], array_keys($filter['values']));
            }

            // если у нас есть такая штука то типом будет являться значение 'render'
            if (isset($filter['render']) /*&& isset($filter['value'])*/) {
                $filterType = $filter['render'];
                $filterValue = isset($filter['value']) ? $filter['value'] : null;
            }

            if (isset($filter['callback'])) {
                $filterType  = $filterType ?: self::FILTER_TYPE_TEXT;
                $filterValue = $filterValue ?: null;
            }

            // Если мы ещё не определили тип до этого то это просто ввод текста
            if (is_null($filterType)) {
                $filterType  = self::FILTER_TYPE_TEXT;
                $filterValue = $filter;
            }

            $return = array(
                'type' => $filterType,
                'value' => $filterValue
            );

            if ($filterType === self::FILTER_TYPE_SELECT) {
                $return['options'] = $return['value'];

                if (isset($filter['selected'])) {
                    foreach ($return['options'] as $key => $option) {
                        if ($option['key']  == $filter['selected']) {
                            $return['value'] = $option['key'];
                            break;
                        }
                    }
                } else {
                    $return['value'] = null;
                }
            }


            return $return;
        };
        $map = array_map($transformFilters, $filters);
        return $map;
    }

    public function buildMassActionsJson()
    {
        $grid = array();
        $i = 0;
        foreach ($this->_massActions as $value) {

            $class1 = new stdClass();
            $class2 = new stdClass();

            $caption = $value['caption'];
            $path    = $value['url'];
            unset($value['caption']);
            unset($value['url']);

            if (isset($this->_subMassActionInput[$path]) && $this->_subMassActionInput[$path]) {
                $class3 = new stdClass();
                $class3->{$this->_subMassActionInput[$path]['name']} =
                    $this->_subMassActionInput[$path]['options'];

                $class2->sub_mass_actions['input'] = $class3;
            }

            if (isset($this->_subMassActionSelects[$path]) && $this->_subMassActionSelects[$path]) {
                $class3 = new stdClass();
                $class3->{$this->_subMassActionSelects[$path]['name']} =
                    $this->_subMassActionSelects[$path]['options'];

                $class2->sub_mass_actions['multiple'] = isset($value['options']['multiple']) ?
                    $value['options']['multiple'] :
                    $this->_subMassActionSelectsAllowMultiple;
                $class2->sub_mass_actions['select'] = $class3;
            }

            if (isset($this->_subMassActionFcbk[$path]) && $this->_subMassActionFcbk[$path]) {
                $class3 = new stdClass();
                $class3->{$this->_subMassActionFcbk[$path]['name']} =
                    $this->_subMassActionFcbk[$path]['options'];

                $class2->sub_mass_actions['fcbk'] = $class3;
            }

            foreach ($value as $k => $v) {
                $class2->{$k} = $v;
            }
            $class2->path = $path;


            $defaultMassActionsColumn = $this->getSource()->getPrimaryKeyField() ?: $this->getDefaultMassActionColumn();
            // Добавляем нужную опцию
            if (!isset($class2->options)) {
                $class2->options = array('postMassIdsColumn' => $defaultMassActionsColumn);
            } else if (!isset($class2->options['postMassIdsColumn'])) {
                $class2->options['postMassIdsColumn'] = $defaultMassActionsColumn;
            }
            $class1->{$caption} = $class2;

            $grid[] = $class1;
            $i ++;
        }

        return $grid;
    }

    public function buildTableSettingsJson()
    {
        $class1 = new stdClass();

        $class1->tableColumns  = $this->_options['summaryOptions'];
        $class1->tableSwitcher = $this->getTableSwitcher();
        $class1->tableFilters  = $this->getTableFilters();
        $class1->order         = $this->getOrder();
        $class1->links         = $this->getExport();
        $class1->pagination    = $this->getResultsPerPage();
        $class1->totalRecords  = $this->totalRecordsBeforePagination;

        return $class1;
    }

    /**
     * @param bool $return
     * @return false|string
     * @throws Zend_Exception
     * @throws Exception
     */
    public function deploy ($return = false)
    {

        if ($this->getSource() === null) {
            throw new Bvb_Grid_Exception('Please Specify your source');
        }

        $grid = array();

        $request = Zend_Controller_Front::getInstance()->getRequest();
        $perPage = $request->getParam('perPage', $this->_pagination);
        $page    = $request->getParam('page', 1);
        $start   = $perPage * $page - $perPage;
        $this->setParam('start' . $this->getGridId(), $start);

        if ($perPage) {
            $this->setNumberRecordsPerPage($perPage);
            $this->setPaginationInterval(array($perPage));
        }
        try {
            parent::deploy();
        } catch (Bvb_Grid_Exception $e) {}

//        header('Content-Type', 'application/json');

        $grid['data']             = self::buildDataJson(count($this->_classRowCondition) ? $this->_classRowCondition : []);
        $grid['filters']          = self::buildFiltersJson($this->_filters);
        $grid['massActions']      = self::buildMassActionsJson();
        $grid['tableSettings']    = self::buildTableSettingsJson();
        $grid['currentPage']      = $page;
        $grid['rowsPerPageItems'] = self::getRowsPerPageItems();
        if ($this->_data['table']) {
            $grid['MassActionsAll'] = $this->getSource()->getMassActionsIds($this->_data['table']);
        }
        $grid['gridId'] = $this->getGridId();

        // Если кастомное поле - берём его
        $defaultMassActionsColumn = $this->getSource()->getPrimaryKeyField() ?: $this->getDefaultMassActionColumn();
        $grid['defaultMassActionsColumn'] = $defaultMassActionsColumn;


        //getMassActionsIds

        // Хардкод запрета сортировки, так как ещё не реализщована сортировка
        // если в url есть параметр gridmod и он равняется ajax
        // то сортировка работает
        $grid['isSortingEnabled'] = true;

        if ($this->autoGetHeaderActionsOnDeploy) {
            // TODO нужна ли проверка роли пользователя??
            $view = Zend_Registry::get('view');
            $grid['headerActions'] = $view->actionsData();
        }

        $json = HM_Json::encodeErrorThrow($grid);

        // TODO убрать развилку (см. `HM_Controller_Action_Trait_Ajax::postDispatchAjax()`)
        //      и проверить использования ->deploy()
        if ($return) {
            return $json;
        }
        echo $json;
        die();
    }

    protected function getRowsPerPageItems() {
        return array($this->_pagination);
        // Тут например можно возвращать массив чисел который
        // соответствует числу отображаемых строк и пользоватли смогут сами выбрать
        // сколько строк им в табличке показывать
        // $totalRows = $this->totalRecordsBeforePagination;
        // $initialRowsPerPage = $this->_pagination;
        // $step = round($initialRowsPerPage / 2);
        // return array($step, $initialRowsPerPage, min(array($initialRowsPerPage + $step, $totalRows)));
    }

    protected  function getOrder()
    {
        $order = new stdClass();
        $orderGrid = 'order' . ($this->getGridId() ?: 'grid');
        if (isset($this->_ctrlParams[$orderGrid]) && $this->_ctrlParams[$orderGrid]) {
            $parts = explode('_', $this->_ctrlParams[$orderGrid]);
            $direction = array_pop($parts);
            $column = implode('_', $parts);
            $order->column = $column;
            $order->direction = $direction;
        } else {
            $order->column = null;
            $order->direction = null;
        }

        return $order;
    }

    protected  function getTableSwitcher()
    {
        $default = new Zend_Session_Namespace('default');
        $request = Zend_Controller_Front::getInstance()->getRequest();
        $gridId =  $this->getGridId();
        $page = sprintf('%s-%s-%s', $request->getModuleName(), $request->getControllerName(), $request->getActionName());
        $class = new stdClass();

        if (isset($this->_gridSwitcher) && isset($this->_gridSwitcher['modes']) && count($this->_gridSwitcher['modes'])) {

            /** порядок выбора не совпадает с @see HM_Controller_Action::getSwitcherSetOrder(),
             * но работает вроде правильно, потому что там мы выставляем пришедший запрос в сессию,
             * а тут берём сначала из сессии, а если не прокатило, то из реквеста
             */
            $currentMode = isset($default->grid[$page][$gridId][HM_DataGrid::SWITCHER_PARAM_DEFAULT])
                ? $default->grid[$page][$gridId][HM_DataGrid::SWITCHER_PARAM_DEFAULT]
                : ($request->getParam(HM_DataGrid::SWITCHER_PARAM_DEFAULT ?: null));

            // изначально в коде только title, но нам нужен и label (краткий) и title (полный) текст
            $class->label = $this->_gridSwitcher['label'] ? : $this->_gridSwitcher['title'];
            $class->title = $this->_gridSwitcher['title'];
            $class->modes = $this->_gridSwitcher['modes'];
            $class->param = isset($this->_gridSwitcher['param']) ? $this->_gridSwitcher['param'] : HM_DataGrid::SWITCHER_PARAM_DEFAULT;
            $class->currentMode = isset($currentMode) && in_array($currentMode, $this->_gridSwitcher['modes']) ? $currentMode : $this->_gridSwitcher['modes'][0];
        }

        return $class;
    }

    protected  function getTableFilters()
    {
        $default = new Zend_Session_Namespace('default');
        $request = Zend_Controller_Front::getInstance()->getRequest();
        $gridId =  $this->getGridId();
        $page = sprintf('%s-%s-%s', $request->getModuleName(), $request->getControllerName(), $request->getActionName());
        $filters = $currentFilters = [];
        if (isset($this->_filters) && count($this->_filters)) $filters = array_flip(array_keys($this->_filters));
        $class = new stdClass();
        foreach ($filters as $filter => $value) {
            unset($filters[$filter]);

            if (isset($this->_filtersValues[$filter])) {
                $value = $default->grid[$page][$gridId]['filters'][$filter] = $this->_filtersValues[$filter];
                $currentFilters[] = $filter;
            } else {
                if (isset($default->grid[$page][$gridId]['filters'][$filter])) {
                    $value = $default->grid[$page][$gridId]['filters'][$filter];
                    unset($default->grid[$page][$gridId]['filters'][$filter]);
                } else {
                    unset($default->grid[$page][$gridId]['filters'][$filter]);
                    continue;
                }
            }

            $class->$filter = $value;
        }

        return $class;
    }

    /**
     * Sets a row class based on a condition
     * @param $condition
     * @param $class
     * @param string $else
     * @return Bvb_Grid_Deploy_Vue
     */
    public function setClassRowCondition ($condition, $class, $else = '')
    {
        if (empty($this->_classRowCondition)) {
            $this->_classRowCondition = array();
        }
        $this->_classRowCondition[] = array('condition' => $condition, 'class' => $class, 'else' => $else);
        return $this;
    }

    public function setAction(HM_Grid_Action $action)
    {
        $this->_actions[] = [
            'confirm' => false,
            'icon'    => $action->getTitle(),
            'url'     => $action->getUrl()
        ];
    }
}

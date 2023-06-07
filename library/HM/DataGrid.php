<?php

/**
 * Абстрактный класс, определяющий базовый функционал для создания сущностей,
 * объединяющих в себе определение запроса к БД и конструирование грида
 * на основании результатов этого запроса.
 *
 */
abstract class HM_DataGrid
{
    use HM_Grid_Trait_VueGetMarkup;

    const SWITCHER_PARAM_DEFAULT = 'all';

    protected $serviceContainer;
    protected $userService;
    protected $select;
    protected $view;
    protected $switcher;
    protected $output;
    protected $actionsCallback;
    protected $massActionsCallback;
    protected $primaryKey = [];
    /**
     * @var string|null название поля составного ключа грида для проброса потом в grid->source
     * @see Bvb_Grid_Source_Zend_Select::$_primaryKeyField
     */
    protected $primaryKeyField = null;
    protected $from        = [];
    protected $baseTable   = [];
    protected $options     = [];
    protected $columns     = [];
    protected $actions     = [];
    protected $massActions = [];
    protected $switcherOptions    = [];
    protected $classRowConditions = [];

    /**
     * HM_DataGrid constructor.
     * @param HM_View $view
     * @param array $switcher
     * @param array $options
     * @param string $output
     *
     * @throws Zend_Exception
     */
    public function __construct(HM_View $view, array $switcher = [], array $options = [], $output = 'vue')
    {
        $this->serviceContainer = Zend_Registry::get('serviceContainer');
        $this->userService      = new HM_User_UserService();
        $this->select           = $this->userService->getSelect();
        $this->view             = $view;
        $this->switcher         = $switcher;
        $this->options          = $options;
        $this->output           = $output;

        $this->initColumns();
        $this->initActions();
        $this->initMassActions();
        $this->initClassRowConditions();
        $this->initSwitcher();
        $this->initActionsCallback();
        $this->initMassActionsCallback();

        foreach ($this->columns as $column)
            $this->from[$column->getName()] = $this->getColumn($column->getName())->getExpression();
    }

    /**
     * Метод для конструирования селекта.
     * В дочерних классах в этом методе можно джойнить, группировать, сортировать всё, что нужно,
     * используя поле $select, в котором хранится экземпляр Zend_Db_Select.
     *
     * !! ОБЯЗАТЕЛЬНО вызывать родительскую реализацию метода в дочернем!!
     *
     * @return void
     */
    protected function getList()
    {
        $this->select->from($this->baseTable, $this->from);

        $this->setSwitcherRestrictions();
        $this->setRoleRestrictions();
    }

    /**
     * Здесь определяются дополнительные условия при определении селекта,
     * в зависимости от положения свитча в гриде.
     *
     * @return void
     */
    abstract protected function setSwitcherRestrictions();

    /**
     * Здесь определяются дополнительные условия при определении селекта,
     * в зависимости от роли пользователя.
     *
     * @return void
     */
    abstract protected function setRoleRestrictions();

    /**
     * Метод для добавления полей в грид.
     * Скрытые поля добавляются массивом через метод класса "hiddenColumns(array $columns = [])",
     * поля в виде колонок таблицы добавляются по отдельности через метод класса addColumn($name, $array).
     *
     * @return void
     */
    abstract protected function initColumns();

    /**
     * @return void
     */
    abstract protected function initActions();

    /**
     * @return void
     */
    abstract protected function initClassRowConditions();

    /**
     * @return void
     */
    abstract protected function initMassActions();

    /**
     * @return void
     */
    abstract protected function initSwitcher();

    protected function initActionsCallback() {}

    protected function initMassActionsCallback() {}

    /**
     * Опциональная возможность создания составного ключа для организации масс-экшнов в гридах,
     * построенных на таблицах, не содержащих праймари-кей.
     * Инициировано в задаче #31294
     *
     * @param array  $parts     - Поля, значения которых образуют составной ключ для грида
     * @param string $keyName   - Назвавние ключа в гриде, по умолчанию 'primaryKey'
     * @param string $delimiter - Разделитель значений, образующих составной ключ, по умолчанию '-'
     *
     * @return void
     */
    protected function addPrimaryKey(array $parts, $keyName = 'primaryKey', $delimiter = '-')
    {
        $expression = (count($parts) == 1) ? $parts[0] : $this->getConcat($parts, $delimiter);

        $this->addColumn($keyName, [
            'hidden' => true,
            'expression' => new HM_Db_Expr($expression)
        ]);

        $this
            ->setPrimaryKeyField($keyName)
            ->setPrimaryKey($parts);
    }

    private function getConcat($parts, $delimiter)
    {
        $expression  = str_repeat('CONCAT(', count($parts));
        $expression .= implode(',\'' . $delimiter . '\'),', $parts);
        $expression .= ')';
        return $expression;
    }

    /**
     * Здесь добавляются скрытые поля грида, т.к. они не влияют на внешний вид грида,
     * то единственная существенная информация о поле, указываемая здесь,
     * это выражение для формирования селекта.
     *
     * Соответственно, в метод передаётся массив с ключами, в виде названий полей и значениями
     * в виде экземпляров HM_Db_Expr.
     *
     * @param array $columns
     *
     * @return void
     */
    protected function hiddenColumns(array $columns = [])
    {
        foreach ($columns as $name => $column) {
            $this->addColumn($name, [
                'hidden' => true,
                'expression' => $column
            ]);
        }
    }

    /**
     * Метод для добавления поля в виде колонки в гриде.
     *
     * @param $name  - название поля
     * @param $array - массив типа:
     * [
     *     'title'      => _('ФИО'), // название колонки в гриде
     *     'position'   => 1, // позиция колонки в гриде слева, начиная с 1
     *     'expression' => new HM_Db_Expr('t1.fio'), // выражение для формирования селекта
     *     'handler'    => 'card-link.js' // js-обработчик
     * ]
     *
     * @return void
     */
    protected function addColumn($name, $array)
    {
        $column = new HM_DataGrid_Column($name);
        foreach ($array as $key => $value) {
            $methodName = 'set' . ucfirst($key);
            if (property_exists('HM_DataGrid_Column' , $key)) $column->$methodName($value);
        }
        $this->columns[$name] = $column;
    }

    protected function addActions(array $actions)
    {
        if (is_array($actions) && count($actions)) {
            foreach ($actions as $action) {
                if ($action) $this->addAction($action);
            }
        }
    }

    protected function addAction(HM_DataGrid_Action $action)
    {
        $this->actions[] = $action;
    }

    protected function addMassActions(array $massActions)
    {
        if (is_array($massActions) && count($massActions)) {
            foreach ($massActions as $massAction) {
                if ($massAction) $this->addMassAction($massAction);
            }
        }
    }

    protected function addMassAction(HM_DataGrid_MassAction $massAction)
    {
        $this->massActions[] = $massAction;
    }

    protected function addActionsCallback(array $actionsCallback)
    {
        $this->actionsCallback = $actionsCallback;
    }

    protected function addMassActionsCallback(array $massActionsCallback)
    {
        $this->massActionsCallback = $massActionsCallback;
    }

    protected function addSwitcher(array $switcher)
    {
        $this->switcherOptions = $switcher;
    }

    protected function addClassRowCondition($column, $class, $else = '')
    {
        $this->classRowConditions[] = [
            'column' => $column,
            'class'  => $class,
            'else'   => $else
        ];
    }

    /**
     * @name string
     * @return mixed
     */
    protected function getColumn($name)
    {
        return $this->columns[$name];
    }

    protected function unsetAction(&$actions, $unsetAction, $reset = true)
    {
        // ВНИМАНИЕ!!!! Данная функция неверно раболтает при сортировке грида!!!!!
        $unsetUrl = $this->view->url($unsetAction, null, $reset);
        foreach ($actions as $actionKey => $actionVal) {
            $url = $actionVal['url'];
            if (false !== strpos($unsetUrl, $url) or false !== strpos($url, $unsetUrl)) {
                unset($actions[$actionKey]);
            }
        }

        $actions = array_values($actions);
    }

    /**
     * Построение грида на основе селекта, определённого в методе "getList()" дочерних классов.
     *
     * @throws Zend_Exception
     *
     * @return mixed
     */
    public function buildGrid()
    {
        $this->getList();
        $columnsOptions = $filters = [];

        foreach ($this->columns as $column) {
            $filters[$column->getName()] = $column->getFilter();
            $columnsOptions[$column->getName()]['title'    ] = $column->getTitle();
            $columnsOptions[$column->getName()]['handler'  ] = $column->getHandler();
            $columnsOptions[$column->getName()]['decorator'] = $column->getDecorator();
            $columnsOptions[$column->getName()]['callback' ] = $column->getCallback();
            $columnsOptions[$column->getName()]['position' ] = $column->getPosition();
            $columnsOptions[$column->getName()]['color'    ] = $column->getColor();
            $columnsOptions[$column->getName()]['hidden'   ] = $column->getHidden();
        }

        $request = Zend_Controller_Front::getInstance()->getRequest();
        $params = $request->getParams();
        $gridId = isset($this->options['courseId']) && !in_array('_exportTogrid', array_keys($params)) ?
            'grid' . $this->options['courseId'] ?: '' : 'grid';
        $event = new sfEvent(null, HM_Extension_ExtensionService::EVENT_FILTER_GRID_COLUMNS);
        $this->serviceContainer->getService('EventDispatcher')->filter($event, $columnsOptions);
        $summaryOptions = $event->getReturnValue();

        $deployParams = ['download' => 1, 'dir' => Zend_Registry::get('config')->path->upload->tmp];
        $grid = Bvb_Grid::factory($this->output, [
            'deploy' => ['excel' => $deployParams, 'word' => $deployParams],
            'summaryOptions' => $summaryOptions
        ], $gridId);

        $grid->setAjax($gridId);
        $grid->setImagesUrl('/images/bvb/');
        $grid->setExport(['print', 'excel', 'word']);
        $grid->setEscapeOutput(true);
        $grid->setAlwaysShowOrderArrows(false);

        if (!empty($this->actionsCallback[0])) {
            $grid->setActionsCallback($this->actionsCallback[0]);
        }

        //Получаем колво строк с помощью старого метода
        $perPage = $this->serviceContainer->getService('Option')->getOption('grid_rows_per_page');
        $perPage = $perPage > 0 ? $perPage : Bvb_Grid::ROWS_PER_PAGE;

        $grid->setNumberRecordsPerPage($perPage);
        $grid->setcharEncoding(Zend_Registry::get('config')->charset);

        if (is_array($this->select)) {
            $grid->setSource(new Bvb_Grid_Source_Array($this->select, array_keys($columnsOptions)));
        } elseif (!is_null($this->select) and $this->select instanceof Zend_Db_Select) {
            $grid->setSource(new Bvb_Grid_Source_Zend_Select($this->select));
        }

       /**
        * Пробрасываем данные о составном или просто кастомном ключе и названии поля
        * в @see  Bvb_Grid_Source_Zend_Select
        **/
        if ($this->getPrimaryKey()) {
            $grid->setPrimaryKey($this->getPrimaryKey());
        }

        if ($this->getPrimaryKeyField()) {
            $grid->setPrimaryKeyField($this->getPrimaryKeyField());
        }

        if (is_array($columnsOptions)) {
            foreach($columnsOptions as $column => $options) {
                $grid->updateColumn($column, $options);
            }
        }

        if (is_array($filters) && count($filters)) {
            $gridFilters = new Bvb_Grid_Filters();
            foreach($filters as $field => $options) {
                $gridFilters->addFilter($field, $options);
            }
            $grid->addFilters($gridFilters);
        }

        if (is_array($this->actions)) {
            foreach($this->actions as $action) {
                $grid->addAction(
                    $action->getUrl(),
                    $action->getParams(),
                    $action->getName(),
                    $action->getConfirm()
                );
            }
        }

        if (is_array($this->massActions) && count($this->massActions)) {
            foreach($this->massActions as $massAction) {
                $grid->addMassAction(
                    $massAction->getUrl(),
                    $massAction->getName(),
                    $massAction->getConfirm(),
                    $massAction->getOptions()
                );

                $sub = $massAction->getSub();
                if (is_array($sub) && count($sub)) {
                    $function   = $sub['function'];
                    $multiple   = isset($sub['params']['multiple']) ? $sub['params']['multiple'] : false;
                    $subOptions = isset($sub['params']['options' ]) ? $sub['params']['options' ] : false;
                    $grid->$function(
                        $sub['params']['url' ],
                        $sub['params']['name'],
                        $subOptions,
                        $multiple
                    );
                }
            }
        }

        if (is_array($this->switcherOptions) && count($this->switcherOptions)) {
            $grid->setGridSwitcher($this->switcherOptions);
        }

        if (is_array($this->classRowConditions)) {
            foreach ($this->classRowConditions as $classRowCondition) {
                $grid->setClassRowCondition(
                    $classRowCondition['column'],
                    $classRowCondition['class'],
                    $classRowCondition['else']
                );
            }
        }

        $translator = new Zend_Translate('array', APPLICATION_PATH.'/system/errors.php');
        $grid->setTranslator($translator);

        return $grid->deploy(true);
    }

    /**
     * @return HM_View
     */
    public function getView()
    {
        return $this->view;
    }

    public function setTitle($title, $iconName = null, $size = '24px', $params = [])
    {
        return $iconName ? $this->getView()->svgIcon($iconName, _($title), $size, $params) : _($title);
    }

    /**
     * @return mixed
     */
    public function getServiceContainer()
    {
        return $this->serviceContainer;
    }

    /**
     * @return mixed
     */
    public function getSelect()
    {
        return $this->select;
    }

    /**
     * @return array
     */
    public function getSwitcher(): array
    {
        return $this->switcher;
    }

    /**
     * @return array
     */
    public function getPrimaryKey()
    {
        return $this->primaryKey;
    }

    /**
     * @param $primaryKey
     * @return HM_DataGrid
     */
    public function setPrimaryKey($primaryKey): HM_DataGrid
    {
        $this->primaryKey = $primaryKey;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getPrimaryKeyField(): ?string
    {
        return $this->primaryKeyField;
    }

    /**
     * @param string|null $primaryKeyField
     * @return HM_DataGrid
     */
    public function setPrimaryKeyField(?string $primaryKeyField): HM_DataGrid
    {
        $this->primaryKeyField = $primaryKeyField;
        return $this;
    }

    /**
     * @return array
     */
    public function getOptions(): array
    {
        return $this->options;
    }

    /**
     * @return array
     */
    public function getColumns(): array
    {
        return $this->columns;
    }

    /**
     * @param array $columns
     */
    public function setColumns(array $columns): void
    {
        $this->columns = $columns;
    }

    /**
     * @param string $title
     * @param HM_DataGrid_Column $value
     */
    public function updateColumn(string $title, HM_DataGrid_Column $value): void
    {
        $columns = $this->getColumns();
        $columns[$title] = $value;
        $this->setColumns($columns);
    }

    public function setActionsCallback($callback)
    {
        $this->actionsCallback = $callback;
    }

    public function setMassActionsCallback($callback)
    {
        $this->massActionsCallback = $callback;
    }
}
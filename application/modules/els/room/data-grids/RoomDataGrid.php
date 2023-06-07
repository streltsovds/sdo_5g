<?php

/**
 *
 */
class HM_Room_DataGrid_RoomDataGrid extends HM_DataGrid
{
    protected $baseTable = ['t1' => 'rooms'];
    protected $subSelect = null;

    /**
     * Метод для конструирования селекта.
     * В этом методе можно джойнить, группировать, сортировать всё, что нужно,
     * используя поле $select, в котором хранится экземпляр Zend_Db_Select.
     *
     * !! ОБЯЗАТЕЛЬНО вызывать родительскую реализацию метода в дочернем!!
     *
     * @return void
     */
    protected function getList()
    {
        $this->select
//            ->joinInner([], '', [])
//            ->joinLeft([], '', [])
//            ->where('')
//            ->order('')
            ->group([
//                't1.rid',
//                't1.name',
//                't1.type',
//                't1.description',
//                't1.volume'
            ]);

        parent::getList();
    }

    /**
     * Здесь определяются дополнительные условия при определении селекта,
     * в зависимости от положения свитча в гриде.
     *
     * @return void
     */
    protected function setSwitcherRestrictions()
    {
    }

    /**
     * Здесь определяются дополнительные условия при определении селекта,
     * в зависимости от роли пользователя.
     *
     * @return void
     */
    protected function setRoleRestrictions()
    {
    }

    /**
     * Метод для добавления полей в грид.
     * Скрытые поля добавляются массивом через метод класса "hiddenColumns(array $columns = [])".
     * Поля в виде колонок таблицы добавляются по отдельности через метод класса addColumn(string $name, array $array).
     * Так же, в случае отсутствия первичного ключа в базовой таблице, здесь можно задать составной ключ при помощи
     * метода класса addPrimaryKey(array $parts, $keyName = 'primaryKey', $delimiter = '-').
     * Этот ключ может использоваться, например, при работе масс-экшнов.
     *
     * @return void
     */
    protected function initColumns()
    {
        $this->hiddenColumns([
            'rid'        => new HM_Db_Expr('t1.rid'),
        ]);

        $this->addColumn('name', [
            'title'      => _('Название'),
            'position'   => 1,
            'expression' => new HM_Db_Expr('t1.name'),
            'filter'     => HM_DataGrid_Column_Filter::create($this)
        ]);

        $this->addColumn('type', [
            'title'      => _('Тип'),
            'position'   => 2,
            'expression' => new HM_Db_Expr('t1.type'),
            'callback'   => HM_Room_DataGrid_Callback_UpdateRoomType::create($this, ['{{type}}']),
            'filter'     => HM_DataGrid_Column_Filter_Select::create($this, ['values' => HM_Room_RoomModel::getTypes()])
        ]);

        $this->addColumn('description', [
            'title'      => _('Описание'),
            'position'   => 3,
            'expression' => new HM_Db_Expr('t1.description'),
            'filter'     => HM_DataGrid_Column_Filter::create($this)
        ]);

        $this->addColumn('volume', [
            'title'      => _('Количество мест'),
            'position'   => 4,
            'expression' => new HM_Db_Expr('t1.volume'),
            'filter'     => HM_DataGrid_Column_Filter::create($this)
        ]);
    }

    /**
     * Метод для добавления экшнов в грид.
     * В массив $actions набиваются экземпляры наследников HM_DataGrid_Action, для создания которых используется
     * статический метод create(HM_DataGrid $dataGrid, $name, $options = []).
     * В library/HM/DataGrid/Action можно найти и заюзать некоторые типовые классы экшнов.
     * Кастомный экшн можно создать в папке "actions", лежащей на уровне размещения данного файла.
     * Если Ваш экшн носит универсальный характер и может быть подключен в других дата-гридах,
     * имеет смысл поместить его в "library".
     *
     * @return void
     */
    protected function initActions()
    {
        $actions = [
            HM_DataGrid_Action_Edit::create($this, $this->getView()->svgIcon('edit', 'Редактировать'), [
                'module'     => 'room',
                'controller' => 'index',
                'params'     => ['rid']
            ]),

            HM_DataGrid_Action_Delete::create($this, $this->getView()->svgIcon('delete', 'Удалить'), [
                'module'     => 'room',
                'controller' => 'index',
                'params'     => ['rid']
            ]),
        ];

        $this->addActions($actions);
    }

    /**
     * Метод для добавления масс-экшнов в грид.
     * В массив $massActions набиваются экземпляры наследников HM_DataGrid_MassAction, для создания которых используется
     * статический метод create(HM_DataGrid $dataGrid, $name, $options = []).
     * В library/HM/DataGrid/MassAction можно найти и заюзать некоторые типовые классы масс-экшнов.
     * Кастомный экшн можно создать в папке "mass-actions", лежащей на уровне размещения данного файла.
     * Если Ваш масс-экшн носит универсальный характер и может быть подключен в других дата-гридах,
     * имеет смысл поместить его в "library".
     *
     * @return void
     */
    protected function initMassActions()
    {
        $massActions = [
            HM_DataGrid_MassAction::create($this, _('Выберите действие'), ['url' => ['action' => 'index']]),
            HM_Room_DataGrid_MassAction_Delete::create($this, _('Удалить'))
        ];

        $this->addMassActions($massActions);
    }

    /**
     * Здесь добавляем свитчер путём передачи опций в метод addSwitcher($switcherOptions),
     * для переключения режимов отображения данных в гриде.
     * Что-бы не показывать свитчер, оставьте тело данного метода пустым.
     *
     */
    protected function initSwitcher()
    {
//        $switcherOptions = [
//            'title' => 'ХХХ', // этот текст будет выводиться возле свитча над гридом
//            'param' => self::SWITCHER_PARAM_DEFAULT,
//            'modes' => [
//                //это пример, заменить актуальными положениями свитча
//                Assign_StudentController::FILTER_LISTENERS_COURSE,
//                Assign_StudentController::FILTER_ALL,
//            ],
//        ];
//
//        $this->addSwitcher($switcherOptions);
    }

    /**
     * Здесь можно определить css-класс строки грида, удовлетворяющей определённому условию.
     * Например, указание аргументов ("{{field}} > 0", 'highlighted') приведёт к тому, что строки,
     * в которых поле "field" окажется больше нуля, будут выделены стилями, определёнными в css-классе .highlighted.
     * Каждое последующее условие следует добавлять отдельным вызовом метода "addClassRowCondition".
     *
     */
    protected function initClassRowConditions()
    {
//        $this->addClassRowCondition(set_condition_here, set_css_class_here);
    }

    /**
     * Здесь можно привязать коллбэки, преобразующие вывод экшнов в гриде.
     * Эти коллбэки должны быть определены где-то в контроллере или в трейте, подключённом к контроллеру.
     *
     */
    protected function initActionsCallback()
    {
//        $this->addActionsCallback([
//            'function' => [$this, 'customActionsCallbackName'],
//            'params'   => ['{{field_1}}', '{{field_2}}']
//        ]);
    }

    /**
     * Здесь можно привязать коллбэки, преобразующие вывод масс-экшнов в гриде.
     * Эти коллбэки должны быть определены где-то в контроллере или в трейте, подключённом к контроллеру.
     *
     */
    protected function initMassActionsCallback()
    {
//        $this->addMassActionsCallback([
//            'function' => [$this, 'customMassActionsCallbackName'],
//            'params'   => ['{{field_1}}', '{{field_2}}']
//        ]);
    }
}

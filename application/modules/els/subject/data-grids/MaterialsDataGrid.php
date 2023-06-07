<?php

/**
 * Датагрид для отображения материалов в курсе
 *
 */
class HM_Subject_DataGrid_MaterialsDataGrid extends HM_DataGrid
{
    protected $baseTable = ['m' => 'materials'];

	const SWITCHER_SELF = 0;
	const SWITCHER_ALL = 1;

    protected function getList()
    {
        $this->select
            ->joinLeft(array('s'  => 'schedule'), 'm.id = s.material_id AND (m.type = s.typeID OR m.type = s.tool) AND s.isfree = ' . HM_Lesson_LessonModel::MODE_PLAN, array())
            ->joinLeft(array('sr' => 'subjects_resources'), 'm.id = sr.resource_id AND m.type = \'' . HM_Event_EventModel::TYPE_RESOURCE.'\'', array());

        $this->select->group(array(
            'm.id',
            'm.title',
            'm.type',
        ));

        parent::getList();
    }

    protected function setSwitcherRestrictions()
    {
        $this->select->where('m.subject_id = ?', $this->options['subject_id']);
        if ($this->switcher[0] == self::SWITCHER_ALL) {
            $this->select->orWhere('m.subject_id IS NULL OR m.subject_id = ?', 0);
        }
    }

    protected function setRoleRestrictions()
    {
    }

    protected function initColumns()
    {
        $this->addPrimaryKey([
            'id',
            'type'
        ], 'idType');

        $this->hiddenColumns([
            'id' => new HM_Db_Expr('id'),
        ]);

        $this->addColumn('title', [
            'title'      => _('Название'),
            'position'   => 1,
            'expression' => new HM_Db_Expr('m.title'),
            'decorator'  => HM_Subject_DataGrid_Decorator_Materials_CardLink::create($this, ['{{type}}']),
            'filter'     => HM_DataGrid_Column_Filter::create($this),
            'callback'   => HM_Subject_DataGrid_Callback_Materials_CardLink::create($this, ['{{title}}', '{{type}}', '{{id}}']),
        ]);

        $this->addColumn('type', [
            'title'      => _('Тип материала'),
            'position'   => 2,
            'expression' => new HM_Db_Expr('type'),
            'filter'     => HM_DataGrid_Column_Filter_Select::create($this, ['values' => HM_Material_MaterialModel::getMaterialTypes()]),
            'callback'   => HM_Subject_DataGrid_Callback_Materials_Type::create($this, ['{{type}}']),
        ]);

        $this->addColumn('lessons', [
            'title'      => _('Занятия на его основе'),
            'position'   => 4,
            'expression' => new HM_Db_Expr('GROUP_CONCAT(s.SHEID)'),
            'callback'   => HM_DataGrid_Column_Callback_LessonsCache::create($this, ['{{lessons}}']),
        ]);

        $this->addColumn('extramaterials', [
            'title'      => _('Доп. материалы на его основе'),
            'position'   => 5,
            'expression' => new HM_Db_Expr('GROUP_CONCAT(sr.resource_id)'),
            'callback'   => HM_Subject_DataGrid_Callback_Materials_Extras::create($this, ['{{extramaterials}}']),
        ]);
    }

    protected function initActions()
    {
        $actions = [
            HM_DataGrid_Action::create($this,
                $this->setTitle('Редактировать карточку', 'edit'),
                [
                    'module' => 'subject',
                    'controller' => 'material',
                    'action' => 'edit-card',
                    'params' => [$this->getPrimaryKeyField()]
                ]
            ),
            HM_DataGrid_Action::create($this,
                $this->setTitle('Редактировать содержимое', 'reports'),
                [
                    'module' => 'subject',
                    'controller' => 'material',
                    'action' => 'edit',
                    'params' => [$this->getPrimaryKeyField()]
                ]
            ),
            HM_DataGrid_Action_Delete::create($this,
                $this->setTitle('Удалить', 'delete'),
                [
                    'module' => 'subject',
                    'controller' => 'material',
                    'action' => 'delete',
                    'params' => [$this->getPrimaryKeyField()]
                ]
            ),
        ];
        $this->addActions($actions);
    }

    protected function initMassActions()
    {
        $massActions = [
//            HM_Subject_DataGrid_MassAction_Materials_Assign::create($this, _('Использовать в курсе'),
//                ['subject_id' => $this->options['subject_id']]
//            ),
//            HM_Subject_DataGrid_MassAction_Materials_Unassign::create($this, _('Не использовать в курсе'),
//                ['subject_id' => $this->options['subject_id']]
//            ),
            HM_Subject_DataGrid_MassAction_Materials_CreateLessons::create($this, _('Сгенерировать занятия'),
                ['subject_id' => $this->options['subject_id']]
            ),
            HM_Subject_DataGrid_MassAction_Materials_CreateExtras::create($this, _('Сгенерировать дополнительные материалы'),
                ['subject_id' => $this->options['subject_id']]
            ),
            HM_Subject_DataGrid_MassAction_Materials_DeleteBy::create($this, _('Удалить'),
                ['subject_id' => $this->options['subject_id']]
            ),
        ];
        $this->addMassActions($massActions);
    }

    protected function initSwitcher()
    {
//        $switcherOptions = [
//            'title' => _('Показать все ресурсы БЗ'),
//            'param' => self::SWITCHER_PARAM_DEFAULT,
//            'modes' => [
//                self::SWITCHER_SELF,
//                self::SWITCHER_ALL,
//            ],
//        ];

        $switcherOptions = [];
        $this->addSwitcher($switcherOptions);
    }

    protected function initClassRowConditions()
    {
    }

    protected function initActionsCallback()
    {
        $this->setActionsCallback([
            [
                'function' => array($this, 'updateDeleteAction'),
                'params' => array('{{id}}', '{{type}}')
            ]
        ]);
    }

    public function updateDeleteAction($id, $type, $actions)
    {
        $lessons = $this->serviceContainer->getService('Lesson')->fetchAll([
            'material_id = ?' => $id,
            'typeID = ?' => $type
        ]);
        $s = count($lessons);

        if (count($lessons)) {
            $confitm = _('На основе данного материала создано одно или несколько занятий. Они будут автоматически преобразованы в занятия без материала (очные); оценки слушателей сохранятся. Продолжить?');
            foreach ($actions as &$action) {
                if (false !== strpos($action['url'], 'delete')) {
                    $action['confirm'] = $confitm;
                }
            }
            return $actions;
        } else {
            return $actions;
        }
    }


}
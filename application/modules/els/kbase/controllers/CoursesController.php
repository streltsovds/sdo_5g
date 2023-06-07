<?php
/*
 * 5G
 *
 */
class Kbase_CoursesController extends HM_Controller_Action
{
    use HM_Controller_Action_Trait_Grid;

    protected function _redirectToIndex()
    {
        $this->_redirector->gotoSimple('index', 'courses', 'kbase');
    }

    public function indexAction()
    {
        $order = $this->_request->getParam("ordergrid");
        if ($order == "") {
            $this->_request->setParam("ordergrid", 'lastUpdateDate_DESC');
        }

        /** @var HM_Course_CourseService $courseService */
        $courseService = $this->getService('Course');
        $select = $courseService->getSelect();
        $from = [
            'course_id' => 'c.CID',
            'Title' => 'c.Title',
            'format' => 'c.format',
            'courseFormat' => 'c.format',
            'lastUpdateDate' => 'c.lastUpdateDate',
            'provider' => 'p.title',
            'provider_id' => 'p.id',
            'Status' => 'c.Status',
            'tags' => 'c.CID'
        ];

        $select
            ->from(['c' => $courseService->getMapper()->getAdapter()->getTableName()], $from)
            ->joinLeft(['p' => $this->getService('Provider')->getMapper()->getAdapter()->getTableName()], 'c.provider = p.id', [])
            ->joinLeft(
                ['sc' => $this->getService('Lesson')->getMapper()->getAdapter()->getTableName()],
                sprintf('sc.material_id = c.CID AND sc.typeID = \'%s\'', HM_Event_EventModel::TYPE_COURSE),
                ['subjects' => 'GROUP_CONCAT(DISTINCT sub.name)']
            )
            ->joinLeft(array('sub' => $this->getService('Subject')->getMapper()->getAdapter()->getTableName()), 'sub.subid = sc.cid ', [])
            ->where('subject_id IS NULL OR subject_id = ?', 0)
            ->group($from);

        $grid = $this->getGrid(
            $select,
            [
                'course_id' => array('hidden' => true),
                'Title' => array(
                    'title' => _('Название'),
                    'style' => 'width: 500px;',
                    'decorator' => "<a href=\"".$this->view->url(array('module' => 'kbase', 'controller' => 'course', 'action' => 'index', 'course_id' => ''), null, true)."{{course_id}}\">{{Title}}</a>"
                ),
                'courseFormat' => array(
                    'title' => _('Формат'),
                    'callback' => array(
                        'function' => array($this, '_updateFormat'),
                        'params' => array('{{courseFormat}}')
                    )
                ),
                'lastUpdateDate' => array(
                    'title' => _('Дата последнего изменения'),
                    'format' => array(
                        'date',
                        array('date_format' => HM_Locale_Format::getDateFormat())
                    ),
                ),
                'provider_id' => array('hidden' => true),
                'provider' => array(
                    'title' => _('Поставщик'),
                    'hidden' => $this->getService('Acl')->checkRoles(HM_Role_Abstract_RoleModel::ROLE_DEAN),
                ),
                'subjects' => array(
                    'title' => _('Используется в учебных курсах'),
                    'callback' => array(
                        'function' => array($this, '_updateSubjects'),
                        'params' => array('{{subjects}}')
                    ),
                    'color' => HM_DataGrid_Column::colorize('subjects')
                ),
                'Status' => array(
                    'title' => _('Статус'),
                    'style' => 'width: 50px;',
                    'callback' => array(
                        'function' => array($this, '_updateStatus'),
                        'params' => array('{{Status}}')
                    )
                ),
                'tags' => array(
                    'title' => _('Метки'),
                    'callback' => array(
                        'function' => array($this, 'displayTags'),
                        'params' => array('{{tags}}', HM_Tag_Ref_RefModel::TYPE_COURSE)
                    ),
                    'color' => HM_DataGrid_Column::colorize('tags')
                )
            ],
            [
                'Title' => null,
                'provider' => null,
                'longtime' => null,
                'lastUpdateDate' => array('render' => 'Date'), // не работает
                'Status' => array('values' => HM_Course_CourseModel::getStatuses()),
                'courseFormat' => array('values' => HM_Course_CourseModel::getFormats()),
                'tags' => array('callback' => array('function' => array($this, 'filterTags')))
            ],
            'grid'
        );

        $grid->setPrimaryKeyField('course_id');

        $grid->updateColumn('public',
            array('callback' =>
                array('function' =>
                    array($this, '_updatePublic'),
                    'params' => array('{{public}}')
                )
            )
        );

        $grid->updateColumn('updated', array(
                'callback' => array(
                    'function' => array(
                        new HM_Course_CourseModel(array()),
                        'dateTime'),
                    'params' => array(
                        '{{updated}}')))
        );

        if (!$this->getService('Acl')->inheritsRole(
            $this->getService('User')->getCurrentUserRole(),
            HM_Role_Abstract_RoleModel::ROLE_TEACHER)) {

            $grid->addAction(
                array('module' => 'kbase', 'controller' => 'course', 'action' => 'edit-card', 'gridmod' => null),
                array('course_id'),
                $this->view->svgIcon('edit', 'Редактировать')
            );

            $grid->addAction(
                array('module' => 'kbase', 'controller' => 'course', 'action' => 'delete'),
                array('course_id'),
                $this->view->svgIcon('delete', 'Удалить')
            );

            $grid->addAction(
                array('module' => 'kbase', 'controller' => 'course', 'action' => 'import'),
                array('course_id'),
                $this->view->svgIcon('upload', _('Импортировать'))
            );

            $grid->addAction(
                array('module' => 'kbase', 'controller' => 'course', 'action' => 'import', 'edition' => 1),
                array('course_id'),
                $this->view->svgIcon('editContent', _('Редактировать содержимое'))
            );

            $grid->addMassAction(
                array('module' => 'kbase', 'controller' => 'course', 'action' => 'delete-by'),
                _('Удалить'),
                _('Данное действие может быть необратимым. Вы действительно хотите продолжить?')
            );
        }

        $grid->setActionsCallback(
            array('function' => array($this,'_updateActions'),
                'params'   => array('{{format}}')
            )
        );

        $this->view->gridAjaxRequest = $this->isGridAjaxRequest();
        $this->view->grid = $grid;
    }

    protected function _getMessages()
    {
        return array(
            self::ACTION_INSERT => _('Ресурс успешно создан'),
            self::ACTION_UPDATE => _('Ресурс успешно обновлён'),
            self::ACTION_DELETE => _('Ресурс успешно удалён'),
            self::ACTION_DELETE_BY => _('Ресурсы успешно удалены')
        );
    }

    public function _updateType($type)
    {
        $types = HM_Course_CourseModel::getTypes();
        return $types[$type];
    }

    public function _updatePublic($status)
    {
        $statuses = HM_Course_CourseModel::getStatuses();
        return $statuses[$status];

    }

    public function _updateFormat($format)
    {
        return HM_Course_CourseModel::getFormat($format);
    }

    public function _updateStatus($status)
    {
        $statuses = HM_Course_CourseModel::getStatuses();

        return $statuses[$status];
    }

    public function _updateSubjects($subjects)
    {
        $subjects = array_filter(array_unique(explode(',', $subjects)));
        $result = [];

        foreach($subjects as $subject) {
            $result[] = "<p>{$subject}</p>";
        }

        if ($result) {
            if (count($result) > 1) {
                array_unshift($result, '<p class="total">' . Zend_Registry::get('serviceContainer')->getService('Subject')->pluralFormCount(count($result)) . '</p>');
            }
            return implode('',$result);
        } else {
            return _('Нет');
        }
    }

    public function _updateActions($type, $actions)
    {
        if ($type == HM_Course_CourseModel::FORMAT_FREE) {
//            $this->unsetAction($actions, ['module' => 'kbase', 'controller' => 'course', 'action' => 'import']);
        } else {
            $this->unsetAction($actions, ['module' => 'course', 'controller' => 'constructor', 'action' => 'index']);
        }
        return $actions;
    }

}

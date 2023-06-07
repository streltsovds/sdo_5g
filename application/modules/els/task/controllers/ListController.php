<?php

class Task_ListController extends HM_Controller_Action
{
    use HM_Controller_Action_Trait_Grid;

    protected $id;
    protected $subject;

    public function init()
    {
        parent::init();

        if (!$this->isAjaxRequest()) {
            $subjectId = $this->id = (int) $this->_getParam('subject_id', 0);
            if ($subjectId) { // Делаем страницу расширенной
                $this->subject = $this->getOne($this->getService('Subject')->find($this->id));

//                $this->initContext($this->subject);
                $this->view->addSidebar('subject', [
                    'model' => $this->subject,
                ]);

            }
        }
    }

    public function indexAction()
    {
        $subjectId = (int) $this->_getParam('subject_id', 0);

        $gridId = ($subjectId) ? "grid{$subjectId}" : 'grid';

        $default = new Zend_Session_Namespace('default');
        if ($subjectId && !isset($default->grid['task-list-index'][$gridId])) {
            $default->grid['task-list-index'][$gridId]['filters']['subject'] = $subjectId; // по умолчанию показываем только слушателей этого курса
        }

        $order = $this->_request->getParam("order{$gridId}");
        if ($order == ""){
            $this->_request->setParam("order{$gridId}", 'title_ASC');
        }

        $filters = array(
            'title' => null,
            'location' => array('values' => HM_Resource_ResourceModel::getLocaleStatuses()),
            'tags' => array('callback' => array('function' => array($this, 'filterTags')))
        );

        $rolesWithFilter = array(
            HM_Role_Abstract_RoleModel::ROLE_DEVELOPER,
            HM_Role_Abstract_RoleModel::ROLE_MANAGER,
            HM_Role_Abstract_RoleModel::ROLE_DEAN,
            HM_Role_Abstract_RoleModel::ROLE_DEAN_LOCAL,
            HM_Role_Abstract_RoleModel::ROLE_ATMANAGER_LOCAL,
            HM_Role_Abstract_RoleModel::ROLE_HR_LOCAL,
            HM_Role_Abstract_RoleModel::ROLE_LABOR_SAFETY_LOCAL,
        );

        if(in_array($this->getService('User')->getCurrentUserRole(), $rolesWithFilter)){
            $filters['public'] = array('values' => HM_Task_TaskModel::getStatuses());
            /*if($this->_getParam('publicgrid', '') == '' && $this->_getParam('gridmod', '') != 'ajax'){
                $this->_setParam('publicgrid', 1);
            }*/
        }else{
            $this->_setParam('publicgrid', 1);
        }



        if ($subjectId) {

            if($order == ''){
                $this->_setParam('ordergrid', 'subject_ASC');
            }

            $select = $this->getService('Task')->getSelect();
            $select->from(
                array('t' => 'tasks'),
                array(
                    'variants' => new Zend_Db_Expr("COUNT(v.variant_id)"),
                    't.task_id',
                    't.title',
                    't.location',
                    'subject' => new Zend_Db_Expr($subjectId),
                    'locationtemp' =>'t.location',
                    'tags' => 't.task_id',
                    'public' => 't.status',
                )
            );


            $subSelect = $this->getService('Task')->getSelect();
            $subSelect->from(array('s' => 'subjects_tasks'), array('subject_id', 'task_id'))->where('subject_id = ?', $subjectId);

            $select->joinLeft(
                array('s' => $subSelect),
                't.task_id = s.task_id',
                array(
                    'statustemp'  => 't.status',
                    'subjecttemp' =>  new Zend_Db_Expr($subjectId),
                )
            )
            ->where('(t.location = ' . (int) HM_Test_Abstract_AbstractModel::LOCALE_TYPE_GLOBAL . ' AND t.status = ' . (int) HM_Task_TaskModel::STATUS_STUDYONLY . ') OR t.subject_id = ' . (int) $subjectId);
        } else {
            if($order == ''){
                $this->_setParam('ordergrid', 'title_ASC');
            }

            $select = $this->getService('Task')->getSelect();
            $select->from(
                array('t' => 'tasks'),
                array(
                    'variants' => new Zend_Db_Expr("COUNT(v.variant_id)"),
                    't.task_id',
                    't.title',
                    't.location',
                    'public' => 't.status',
                    'statustemp' => 't.status',
                    'subject' => new Zend_Db_Expr("'0'"),
                    'subjecttemp' => new Zend_Db_Expr("'0'"),
                    'tags' => 't.task_id'
                )
            )
            //Пока закомментим
            ->where('t.location = ?', HM_Test_Abstract_AbstractModel::LOCALE_TYPE_GLOBAL);
        }

        $select->joinLeft(array('v' => 'tasks_variants'),'t.task_id = v.task_id',array());
        $select->group(array('t.task_id', 't.title', 't.location', 't.status'));

        $grid = $this->getGrid(
            $select,
            array(
                'task_id' => array('hidden' => true),
                'statustemp' => array('hidden' => true),
                'subjecttemp' => array('hidden' => true),
                'locationtemp' => array('hidden' => true),
                'title' => array(
                    'title' => _('Название'),
                    'callback' =>
                        array('function' =>
                            array($this, 'updateName'),
                            'params' => array('{{title}}', '{{status}}', '{{subject}}', '{{task_id}}')
                        )
                ),
                'location' => array(
                    'title' => _('Место хранения'),
                    'hidden' => true,
                    'callback' =>
                        array('function' =>
                            array($this, 'updateStatus'),
                            'params' => array('{{location}}')
                        )
                ),
                'status' => array(
                    'title' => _('Тип'),
                    'hidden' => true,
                ),
                'variants' => array('title' => _('Количество вариантов')),
                'subject' => (
                $subjectId ?
                    array(
                        'title' => _('Доступ для слушателей'),
                        'callback' => array(
                            'function' => array($this, 'updateSubjectColumnTasks'),
                            'params' => array(HM_Event_EventModel::TYPE_TASK, '{{task_id}}', '{{subject}}', $subjectId)
                        )) :
                    array('hidden' => true)
                ),
                'public' => array(
                    'title' => _('Статус ресурса БЗ'),
                    'callback' =>
                        array('function' =>
                            array($this, 'updatePublic'),
                            'params' => array('{{public}}')
                        )
                ),
                'tags' => array(
                    'title' => _('Метки'),
                    'callback' => array(
                        'function' => array($this, 'displayTags'),
                        'params' => array('{{tags}}', HM_Tag_Ref_RefModel::TYPE_TASK)
                    ),
                    'color' => HM_DataGrid_Column::colorize('tags')
                )
            ),
            $filters,
            $gridId
        );

        if ($subjectId) {

            $options = array(
                    'local' => array('name' => 'local', 'title' => _('используемые в данном учебном курсе'), 'params' => array('subject' => $subjectId)),
                    'global' => array('name' => 'global', 'title' => _('все, включая задания из Базы знаний'), 'params' => array('subject' => null), 'order' => 'subject', 'order_dir' => 'DESC'),
            );

            $event = new sfEvent(null, HM_Extension_ExtensionService::EVENT_FILTER_GRID_SWITCHER);
            Zend_Registry::get('serviceContainer')->getService('EventDispatcher')->filter($event, $options);
            $options = $event->getReturnValue();

            $grid->setGridSwitcher($options);
        }

        if (!$this->getService('Acl')->inheritsRole(
            $this->getService('User')->getCurrentUserRole(),
            HM_Role_Abstract_RoleModel::ROLE_TEACHER)) {

            $grid->addAction(
                array('module' => 'task', 'controller' => 'index', 'action' => 'edit'),
                array('task_id'),
                $this->view->svgIcon('edit', 'Редактировать')
            );

            $grid->addAction(
                array('module' => 'task', 'controller' => 'index', 'action' => 'delete'),
                array('task_id'),
                $this->view->svgIcon('delete', 'Удалить')
            );

            $grid->addAction(
                array('module' => 'task', 'controller' => 'index', 'action' => 'preview'),
                array('task_id'),
                $this->view->svgIcon('preview', _('Предварительный просмотр'))
            );

            $grid->addMassAction(
                array('module' => 'task', 'controller' => 'index', 'action' => 'delete-by'),
                _('Удалить'),
                _('Данное действие может быть необратимым. Вы действительно хотите продолжить?')
            );
        }

        $grid->setActionsCallback(
            array('function' => array($this,'updateActions'),
                  'params'   => array('{{locationtemp}}', '{{subjecttemp}}')
            )
        );

        if ($subjectId) $grid->setClassRowCondition("'{{subject}}' != ''", "success");

        $this->view->subjectId = $subjectId;
        $this->view->gridAjaxRequest = $this->isGridAjaxRequest();

        $this->view->grid = $grid;
    }

    public function updateStatus($status)
    {
        $statuses = HM_Test_Abstract_AbstractModel::getLocaleStatuses();
        return $statuses[$status];
    }

    public function updateSubject($subject)
    {

        if($subject !=''){
            return _('Да');
        }else{
            return _('Нет');
        }

    }

    public function updateActions($status, $subjectId, $actions)
    {
        $subject_id = $this->_getParam('subject_id', 0);
        if($this->getService('Task')->isEditable($subjectId, $subject_id, $status)){
            return $actions;
        }else{
            $actions = explode('</a>', $actions);  // fucking hardcode
            unset($actions[1]);
            unset($actions[2]);
            $actions = join('</a>', $actions);
            return $actions;
        }
    }


    public function updateName($name, $status, $subjectId, $taskId)
    {
        return '<a href="'.$this->view->url([
            'module' => 'task',
            'controller' => 'variant',
            'action' => 'list',
            'task_id' => $taskId,
            'subject_id' => $subjectId ? : null
        ], null, true, false) . '">' . $name . '</a>';
    }

    public function updatePublic($status)
    {
        $statuses = HM_Task_TaskModel::getStatuses();
        return $statuses[$status];
    }

    public function viewAction()
    {
        $taskId = (int) $this->_getParam('task_id', 0);
        $subjectId = $this->_getParam('subject_id', 0);

        if ($taskId) {

            $abstract = $this->getOne($this->getService('Task')->find($taskId));
            if ($abstract) {
                    $_SESSION['default']['lesson']['execute']['returnUrl'] = $this->view->serverUrl($this->view->url(array('module' => 'task', 'controller' => 'list', 'action' => 'index', 'subject_id' => $subjectId), null, true));
                    $this->_redirector->gotoUrl($this->view->serverUrl(sprintf('/'.HM_Lesson_Test_TestModel::TEST_EXECUTE_URL, $test->tid, 0)));
            }
        }

        $this->_flashMessenger->addMessage(sprintf(_('Задание #%d не найдено'), $taskId));
        $this->_redirector->gotoSimple('index', 'list', 'task', array('subject_id' => $subjectId));
    }

    public function newDefaultAction()
    {
        $this->_helper->getHelper('layout')->disableLayout();
        Zend_Controller_Front::getInstance()->unregisterPlugin('HM_Controller_Plugin_Unmanaged');
        $this->getHelper('viewRenderer')->setNoRender();

        $result = false;
        $defaults = $this->getService('Task')->getDefaults();
        $defaults['title'] = $this->_getParam('title');
        $subjectId = $defaults['subject_id'] = $this->_getParam('subject_id');
        if (strlen($defaults['title']) && $subjectId) {
            if ($task = $this->getService('Task')->insert($defaults)) {

                if ($this->getService('SubjectTask')->insert(array('subject_id' => $subjectId, 'task_id' => $task->task_id))) {
                    $this->getService('Subject')->update(array(
                        'last_updated' => $this->getService('Subject')->getDateTime(),
                        'subid' => $subjectId
                    ));
                    $result = $task->task_id;
                    // Создаем вариант
                    $qData = array(
                        'task_id' => $task->task_id,
                        'name'=> $task->title,
                        'description'=>"",
                    );
                    $question = $this->getService('TaskVariant')->insert($qData);
                }
            }
        }
        exit(HM_Json::encodeErrorSkip($result));
    }

    public function migrateAction()
    {
        $this->getService('Task')->migrate();
    }
}

<?php
class Assign_TeacherController extends HM_Controller_Action_Assign
{
    use HM_Controller_Action_Trait_Grid;
    use HM_Controller_Action_Trait_Context;

    const FILTER_TEACHERS_COURSE = 0;
    const FILTER_ALL = 1;
    const FILTER_TEACHERS = 2;

    protected $_subjectId;
    protected $_subject;

    public function init()
    {
        if (!$this->isAjaxRequest()) {
            $this->_subjectId = $subjectId = $this->_getParam('subid', $this->_getParam('subject_id', 0));
            $this->_subject   = $this->getOne($this->getService('Subject')->find($subjectId));
            if ($this->_subject) {
                $this->initContext($this->_subject);
                $this->view->addSidebar('subject', [
                    'model' => $this->_subject,
                ]);
                $this->view->setBackUrl($this->view->url([
                    'module' => 'subject',
                    'controller' => 'list',
                    'action' => 'index',
                    'base' => $this->_subject->base,
                ], null, true));
            }
        }

        parent::init();
    }

    public function indexAction()
    {
        $courseId = (int) $this->_getParam('subject_id', 0);
        $switcher = $this->getSwitcherSetOrder($courseId, 'fio_ASC', 'notempty DESC');

        $deployType = 'vue';
        try {
            $this->dataGrid = $courseId ?
                new HM_Assign_DataGrid_AssignTeacherByCourseDataGrid(
                    $this->view,
                    [$switcher],
                    ['courseId' => $courseId],
                    $deployType
                ) :
                new HM_Assign_DataGrid_AssignTeacherDataGrid(
                    $this->view,
                    [$switcher],
                    [],
                    $deployType
                );
        } catch (Zend_Exception $e) {}

        $this->view->subjectId = $courseId;
        $this->view->editable = $this->getService('Acl')->inheritsRole($this->getService('User')->getCurrentUserRole(), HM_Role_Abstract_RoleModel::ROLE_DEAN) ? true : false;
        $this->view->isAjaxRequest = $this->isAjaxRequest();
        $this->view->gridAjaxRequest = $this->isGridAjaxRequest();
        Zend_Registry::get('session_namespace_default')->userCard['returnUrl'] = $_SERVER['REQUEST_URI'];
    }

    public function calendarAction()
    {
        $userIds = $users = $events = $datesWithEvents = [];
        if ($postMassIds = $this->_getParam('postMassIds_grid', '')) {
            $userIds = explode(',', $postMassIds);
            $collection = $this->getService('User')->fetchAll(['MID IN (?)' => $userIds]);
            foreach ($collection as $item) {
                $users[$item->MID] = $item->getName();
            }
        } elseif ($userId = $this->getParam('MID')) {
            $userIds = [$userId];
            /** @var HM_User_UserModel $user */
            if ($user = $this->getService('User')->findOne($userId)) {
                $users[$userId] = $user->getName();
            }
        }

        $this->view->setHeader($user ? $user->getName() : _('Общий календарь'));

        $this->view->setBackUrl($this->view->url([
            'module' => 'assign',
            'controller' => 'teacher',
            'action' => 'index',
            'MID' => null,
        ]));

        if (false === strpos(Zend_Registry::get('config')->resources->db->adapter, 'mysql')) {
            $scheduleEventCond = 'CASE WHEN isnumeric(sch.typeID)=1 THEN abs(CAST(sch.typeID as INT)) ELSE 0 END=ev.event_id';
        } else {
            $scheduleEventCond = 'CASE WHEN (CONCAT(\'\',(sch.typeID * 1)) = sch.typeID) THEN abs(cast(sch.typeID as signed)) ELSE 0 END = ev.event_id';
        }

        if (count($userIds)) {
            $scheduleEventsSelect = $this->getService('Lesson')->getSelect()
                ->from(
                    ['sch' => 'schedule'],
                    [
                        'id' => 'sch.SHEID',
                        'name' => 'sch.title',
                        'begin_date' => 'sch.begin',
                        'type' => new Zend_Db_Expr("'lesson'"),
                        'subtype' => new Zend_Db_Expr("
                            (CASE
                                WHEN (ev.tool is not null) THEN ev.tool
                                ELSE sch.typeID
                            END)
                        "),
                        'subject_id' => 'sch.CID',
                        'user_id' => 'sch.teacher',
                    ]
                )
                ->joinLeft(['ev' => 'events'], $scheduleEventCond, [])
                ->where('sch.timetype <> 2')
                ->where('sch.teacher IN (?)', $userIds);

            $scheduleEvents = $scheduleEventsSelect->query()->fetchAll();

            $subjectEventSelect = $this->getService('Subject')->getSelect()
                ->from(
                    ['s' => 'subjects'],
                    [
                        'id' => 's.subid',
                        'name' => 's.name',
                        'begin_date' => 's.begin',
                        'type' => new Zend_Db_Expr("'subject'"),
                        'subtype' => new Zend_Db_Expr("''"),
                        'subject_id' => new Zend_Db_Expr("0"),
                        'user_id' => 't.MID',
                    ]
                )
                ->joinInner(['t' => 'Teachers'], 't.CID = s.subid', [])
                ->where('s.begin is not null')
                ->where('s.period <> 1')
                ->where('t.MID IN (?)', $userIds)
                ->where('s.begin <> 0');
            $subjectEvent = $subjectEventSelect->query()->fetchAll();

            $events = array_merge($scheduleEvents, $subjectEvent);
        }

        $lessonsIds = [0];

        foreach ($events as $event) {
            if(HM_EventDate_EventDateModel::EVENT_TYPE_LESSON == $event['type']) {
                $lessonsIds[] = $event['id'];
            }
        }

        foreach ($events as &$event) {

            $eventDate = new HM_Date($event['begin_date']);
            $eventDate = $eventDate->toString('YYYY-MM-dd');
            $event['begin_date'] = $eventDate;

            if (!empty($event['user_id']) && isset($users[$event['user_id']])) {
                $event['description'] = sprintf(_('Тьютор: %s'), $users[$event['user_id']]);
            }

            switch ($event['type']) {
                case HM_EventDate_EventDateModel::EVENT_TYPE_LESSON:
                    $viewUrl = $this->getService('Lesson')->getExecuteUrl($event['id'], $event['subject_id']);
                    break;
                case HM_EventDate_EventDateModel::EVENT_TYPE_SUBJECT:
                    $viewUrl = $this->getService('Subject')->getViewUrl($event['id']);
                    break;
            }

            $event['view_url'] = is_array($viewUrl) ? $this->view->url($viewUrl) : $viewUrl;

            $datesWithEvents[$eventDate][] = $event;
        }

        $this->view->data = HM_Json::encodeErrorSkip($datesWithEvents);
    }

    public function calendarOldAction()
    {
        $this->view->source   = array('module'=>'subject', 'controller'=>'list', 'action'=>'calendar', 'user_id' => $this->getRequest()->getParam('MID', null));
        $this->view->editable = $this->getService('Acl')->inheritsRole($this->getService('User')->getCurrentUserRole(), HM_Role_Abstract_RoleModel::ROLE_DEAN) ? true : false;
        $this->view->userId   = $this->getRequest()->getParam('MID', null);
    }

    public function assignAction()
    {
        $subjectId = (int) $this->_getParam('subject_id', 0);
        $courseIds = $this->checkPostIds($this->_getParam('courseId', array(0)));

        $gridId = ($subjectId) ? "grid{$subjectId}" : 'grid';
        $postMassIds = $this->checkPostIds($this->_getParam('postMassIds_' . $gridId, ''));

        if (((count($courseIds) == 1) && empty($courseIds[0])) || !count($postMassIds)) {
            $this->_flashMessenger->addMessage ( _ ( 'Пожалуйста выберите пользователей и укажите курс' ) );
        }

        foreach ($courseIds as $courseId) {
            if (!$this->getService('Dean')->isSubjectResponsibility($this->getService('User')->getCurrentUserId(), $courseId)) {
                $errors = true;
                $this->_flashMessenger->addMessage(_('Нет прав на назначение на этот курс'));
                continue;
            }

            if (count($postMassIds)) {
                $errors=false;
                foreach($postMassIds as $id) {
                    $id = (int) $id;

                    if (method_exists($this, '_preAssign')) {
                        $return = $this->_preAssign($id, $courseId);

                        if ($return === self::RETCODE_DOACTION_END_ITERATION){ // Константы кодов ошибок с описаниями находятся в начале класс
                            $errors = true;
                            continue;
                        }
                        elseif ($return === self::RETCODE_DOACTION_END_LOOP){
                            $errors = true;
                            break;
                        }
                    }

                    $fetch = $this->getService('Teacher')->fetchAll(array('MID = ?' => $id, 'CID = ?' => $courseId));
                    if (count($fetch) == 0) {
                        $this->getService('Teacher')->insert(
                            array(
                                'MID' => $id,
                                'CID' => $courseId
                            )
                        );
                    }

                    if (method_exists($this, '_postAssign')) {
                        $this->_postAssign($id, $courseId);
                    }
                }
            }
        }

        if ($errors == false) {
            $this->_flashMessenger->addMessage ( _ ( 'Пользователи успешно назначены'));
        } else{
            $this->_flashMessenger->addMessage(_('В ходе назначения пользователей возникли ошибки'));
        }


        if (method_exists($this, '_finishAssign')) {
            $this->_finishAssign();
        }

        $messenger = $this->getService('Messenger');
        $messenger->sendAllFromChannels();
        $default = new Zend_Session_Namespace('default');
        $this->_redirector->gotoSimple(
            'index',
            null,
            null,
            [
                'subject_id' => ($subjectId ? $subjectId : null),
//                'all'        => !$this->_getParam(
//                    'all',
//                    isset($default->grid['assign-student-index'][$gridId]['all']) ?
//                        $default->grid['assign-student-index'][$gridId]['all'] :
//                        null
//                )
            ]
        );
    }

    public function unassignAction()
    {
        $subjectId = (int) $this->_getParam('subject_id',0);
        $courseIds = $this->checkPostIds($this->_getParam('unCourseId', $this->_getParam('courseId',array(0))));

        $gridId = ($subjectId) ? "grid{$subjectId}" : 'grid';
        $postMassIds = $this->checkPostIds($this->_getParam('postMassIds_' . $gridId, ''));

        if ((((count($courseIds) == 1)) && empty($courseIds[0])) || !count($postMassIds)) {
            $this->_flashMessenger->addMessage ( _ ( 'Пожалуйста выберите пользователей и укажите курс' ) );
        }

        foreach ($courseIds as $courseId) {
            if (!$this->getService('Dean')->isSubjectResponsibility($this->getService('User')->getCurrentUserId(), $courseId)) {
                $this->_flashMessenger->addMessage(_('Нет прав на удаление назначений в курсе'));
                continue;
            }

            if (count($postMassIds)) {
                foreach($postMassIds as $id) {
                    if (method_exists($this, '_preUnassign')) {
                        $this->_preUnassign($id, $courseId);
                    }
                    $this->getService('Teacher')->deleteBy(
                        sprintf("%s = %d AND %s = %d", 'MID', $id, 'CID', $courseId)
                    );
                    if (method_exists($this, '_postUnassign')) {
                        $this->_postUnassign($id, $courseId);
                    }
                }
            }
        }
        $this->_flashMessenger->addMessage(_('Назначения успешно удалены'));

        if (method_exists($this, '_finishUnassign')) {
            $this->_finishUnassign();
        }
        $this->_redirector->gotoSimple('index', null, null, array('subject_id' => $subjectId));
    }

    protected function _preAssign($personId, $courseId){}
    protected function _assign($personId, $courseId) {}
    protected function _postAssign($personId, $courseId){}
    protected function _postUnassign($personId, $courseId){}
    protected function _finishAssign(){}
    protected function _finishUnassign(){}

    protected function _unassign($personId, $courseId){}
    protected function _preUnassign($personId, $courseId){}
}
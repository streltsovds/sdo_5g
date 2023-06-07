<?php
/*
 * 5G
 *
 */
class Subject_MaterialController extends HM_Controller_Action_Subject
{
    protected $_materialId;
    protected $_materialType;

    protected $_lesson;

    protected $_isEnduser;

    public function init()
    {
        $this->_materialId = $this->_getParam('id');
        $this->_materialType = $this->_getParam('type');

        $this->_isEnduser = $this->getService('Acl')->inheritsRole($this->getService('User')->getCurrentUserRole(), HM_Role_Abstract_RoleModel::ROLE_ENDUSER);

        $lessonId = $this->_getParam('lesson_id');
        if ($lessonId) {

            $this->_lesson = $lesson = $this->getService('Lesson')->findOne($lessonId);
            $this->_materialId = $lesson->material_id ?: $lesson->getModuleId();
            $this->_materialType = $lesson->getType();

        } elseif ($idType = $this->_getParam('idType')) {
            list($this->_materialId, $this->_materialType) = explode('-', $idType);
        }
        // fallback
        if (empty($this->_materialType) && ($resourceId = $this->_getParam('resource_id'))) {
            $this->_materialId = $resourceId;
            $this->_materialType = HM_Event_EventModel::TYPE_RESOURCE;
        }

        return parent::init();
    }

    /*
     *  proxy к подробному просмотру материала
     *  может варьироваться от роли
     */
    public function indexAction()
    {
        switch ($this->_materialType) {
            case HM_Event_EventModel::TYPE_RESOURCE:

                if ($resource = $this->getService('Resource')->findOne($this->_materialId)) {
                    $this->_forward('index', 'resource', 'kbase', ['resource_id' => $resource->resource_id, 'gridmod' => null]);
                }

                break;
            case HM_Event_EventModel::TYPE_COURSE:

                if ($course = $this->getService('Course')->findOne($this->_materialId)) {
                    $url = $this->view->url([
                        'module' => 'kbase',
                        'controller' => 'course',
                        'action' => 'index',
                        'gridmod' => null,
                        'course_id' => $course->CID,
                        'id' => null,
                        'type' => null,
                    ]);
                    $this->_redirect($url);
                }

                break;
            case HM_Event_EventModel::TYPE_TASK:

                $task = $this->getService('Task')->findOne($this->_materialId);
/*
                if($this->getService('Acl')->inheritsRole($this->getService('User')->getCurrentUserRole(), array(HM_Role_Abstract_RoleModel::ROLE_DEAN))) {
                    $this->_materialId = $this->_getParam('id');
                    $url = $this->view->url([
                        'module'     => 'task',
                        'controller' => 'index',
                        'action'     => 'check',
                        'task_id' => $this->_materialId,
                        'redirect_url' => null, // @todo
                        'gridmod' => null,
                        'id' => null,
                        'type' => null,
                    ]);
                } else {
*/
                    $url = $this->view->url([
                        'module'     => 'task',
                        'controller' => 'index',
                        'action'     => 'preview',
                        'task_id' => $this->_materialId,
                        'redirect_url' => null, // @todo
                        'gridmod' => null,
                        'id' => null,
                        'type' => null,
                    ]);
//                }
                $this->_redirect($url);
                break;

            case HM_Event_EventModel::TYPE_TEST:
            case HM_Event_EventModel::TYPE_POLL:

                $redirectUrl = parse_url($_SERVER['HTTP_REFERER']);
                $path = isset($redirectUrl['path']) ? $redirectUrl['path'] : '';
                $query = isset($redirectUrl['query']) ? $redirectUrl['query'] : '';
                $redirectUrl = $path.'?'.$query;

                $quest = $this->getService('Quest')->findOne($this->_materialId);
                $url = $this->view->url([
                    'module'     => 'quest',
                    'controller' => 'lesson',
                    'action'     => 'info',
                    'quest_id' => $quest->quest_id,
                    'redirect_url' => urlencode($redirectUrl),
                    'gridmod' => null,
                    'id' => null,
                    'type' => null,
                ]);
                $this->_redirect($url);
                break;
        }
    }

    public function quickViewAction()
    {
        $this->_helper->layout()->disableLayout();

        if ($resource = $this->getService('Resource')->findOne($this->_materialId)) {
            $this->_forward('index', 'resource', 'kbase', ['resource_id' => $resource->resource_id, 'gridmod' => null]);
        }
    }

    /*
     *  proxy к специализированным формам редактирования (карточки)
     */
    public function editCardAction()
    {
        $kbaseMessage = _('Данный материал создан в Базе знаний и может использоваться в нескольких курсах. Редактирование разрешено только через Базу знаний.');

        switch ($this->_materialType) {
            case HM_Event_EventModel::TYPE_RESOURCE:
                if ($resource = $this->getService('Resource')->findOne($this->_materialId)) {
                    if ($resource->subject_id && ($resource->location == HM_Resource_ResourceModel::LOCALE_TYPE_LOCAL)) {
                        $this->_forward('edit-card', 'resource', 'kbase', ['resource_id' => $resource->resource_id, 'gridmod' => null]);
                    } else {
                        $this->_flashMessenger->addMessage(['type' => HM_Notification_NotificationModel::TYPE_ERROR, 'message' => $kbaseMessage]);
                        $this->_redirectToIndex('index');
                    }
                }
                break;
            case HM_Event_EventModel::TYPE_COURSE:
                if (
                    ($course = $this->getService('Course')->findOne($this->_materialId)) &&
                    $course->subject_id
                ) {
                    $url = $this->view->url([
                        'module' => 'kbase',
                        'controller' => 'course',
                        'action' => 'edit-card',
                        'gridmod' => null,
                        'course_id' => $course->CID,
                        'id' => null,
                        'type' => null,
                    ]);
                    $this->_redirect($url);
                }
                break;
            case HM_Event_EventModel::TYPE_TASK:
                if (
                    ($task = $this->getService('Task')->findOne($this->_materialId)) &&
                    $task->subject_id
                ) {
                    $url = $this->view->url([
                        'module' => 'task',
                        'controller' => 'index',
                        'action' => 'edit',
                        'gridmod' => null,
                        'task_id' => $task->task_id,
                        'id' => null,
                        'type' => null,
                    ]);
                    $this->_redirect($url);
                }
                break;
            case HM_Event_EventModel::TYPE_TEST:
            case HM_Event_EventModel::TYPE_POLL:
                if (
                    ($quest = $this->getService('Quest')->findOne($this->_materialId)) &&
                    $quest->subject_id
                ) {
                    $url = $this->view->url([
                        'module' => 'quest',
                        'controller' => 'list',
                        'action' => 'edit',
                        'gridmod' => null,
                        'quest_id' => $quest->quest_id,
                        'id' => null,
                        'type' => null,
                    ]);
                    $this->_redirect($url);
                }
                break;
        }
    }

    /*
     *  proxy к специализированным конструкторам
     */
    public function editAction()
    {
        $kbaseMessage = _('Данный материал создан в Базе знаний и может использоваться в нескольких курсах. Редактирование разрешено только через Базу знаний.');
        $resource = $resource = $this->getService('Resource')->findOne($this->_materialId);

        switch ($this->_materialType) {

            case HM_Event_EventModel::TYPE_RESOURCE:
                if ($resource) {
                    if ($resource->subject_id && ($resource->location == HM_Resource_ResourceModel::LOCALE_TYPE_LOCAL)) {

                        $action = sprintf('edit-resource-type-%s', HM_Resource_ResourceModel::getTypeString($resource->type));
                        $this->_forward($action, 'resource', 'kbase', ['resource_id' => $resource->resource_id]);
                    } else {
                        $this->_flashMessenger->addMessage(['type' => HM_Notification_NotificationModel::TYPE_ERROR, 'message' => $kbaseMessage]);
                        $this->_redirectToIndex('index');
                    }
                }
                break;

            case HM_Event_EventModel::TYPE_COURSE:
                if (
                    ($course = $this->getService('Course')->findOne($this->_materialId)) &&
                    $course->subject_id
                ) {
                    if ($course->format == HM_Course_CourseModel::FORMAT_FREE) {
                        $url = $this->view->url(array('module' => 'course', 'controller' => 'constructor', 'action' => 'index', 'gridmod' => null, 'course_id' => $course->CID));
                    } else {
                        $url = $this->view->url(array('module' => 'kbase', 'controller' => 'course', 'action' => 'import', 'gridmod' => null, 'course_id' => $course->CID));
                    }
                    $this->_redirect($url);
                } else {
                    $this->_flashMessenger->addMessage(['type' => HM_Notification_NotificationModel::TYPE_ERROR, 'message' => $kbaseMessage]);
                    $this->_redirectToIndex('index');
                }
                break;

            case HM_Event_EventModel::TYPE_TASK:
                if (
                    ($task = $this->getService('Task')->findOne($this->_materialId)) &&
                    $task->subject_id
                ) {
                    $url = $this->view->url(array('module' => 'task', 'controller' => 'variant', 'action' => 'list', 'gridmod' => null, 'task_id' => $task->task_id));
                    $this->_redirect($url);
                } else {
                    $this->_flashMessenger->addMessage(['type' => HM_Notification_NotificationModel::TYPE_ERROR, 'message' => $kbaseMessage]);
                    $this->_redirectToIndex('index');
                }
                break;

            case HM_Event_EventModel::TYPE_TEST:
            case HM_Event_EventModel::TYPE_POLL:
                if (
                    ($quest = $this->getService('Quest')->findOne($this->_materialId)) &&
                    $quest->subject_id
                ) {
                    $url = $this->view->url([
                        'module' => 'quest',
                        'controller' => 'question',
                        'action' => 'list',
                        'gridmod' => null,
                        'quest_id' => $quest->quest_id,
                        'idType' => null,
                    ]);
                    $this->_redirect($url);
                } else {
                    $this->_flashMessenger->addMessage(['type' => HM_Notification_NotificationModel::TYPE_ERROR, 'message' => $kbaseMessage]);
                    $this->_redirectToIndex('index');
                }
                break;

            case HM_Event_EventModel::TYPE_ECLASS:
                $url = $this->view->url([
                    'module' => 'eclass',
                    'controller' => 'index',
                    'action' => 'index',
                    'lesson_id' => $this->_lesson->SHEID,
                    'idType' => null,
                ]);
                $this->_redirect($url);
                break;

            case HM_Event_EventModel::TYPE_EMPTY:
                $this->_flashMessenger->addMessage([
                    'type' => HM_Notification_NotificationModel::TYPE_ERROR,
                    'message' => _('Для занятия данного типа действие "Редактировать материал" не допустимо.')
                ]);
                $this->_redirectToIndex('index');
                break;

            case HM_Event_EventModel::TYPE_FORUM:
                $forum = $this->getService('Forum')->getForumBySubject($this->_subject, null, $this->_lesson);
                if ($forum) {
                    $this->_redirector->gotoSimple('edit', 'themes', 'forum', [
                        'forum_id'   => $forum->forum_id,
                        'section_id' => $forum->section->section_id
                    ]);
                } else {
                    $this->_flashMessenger->addMessage([
                        'type' => HM_Notification_NotificationModel::TYPE_ERROR,
                        'message' => _('Тема форума для данного занятия не найдена')
                    ]);
                    $this->_redirectToIndex('index');
                }
                break;
        }
    }

    public function deleteAction()
    {
        switch ($this->_materialType) {
            case HM_Event_EventModel::TYPE_RESOURCE:
                $this->deleteResource($this->_subjectId, $this->_materialId);
                break;
            case HM_Event_EventModel::TYPE_COURSE:
                $this->deleteCourse($this->_subjectId, $this->_materialId);
                break;
            case HM_Event_EventModel::TYPE_TASK:
                $this->deleteTask($this->_subjectId, $this->_materialId);
                break;
            case HM_Event_EventModel::TYPE_POLL:
            case HM_Event_EventModel::TYPE_TEST:
                $this->deleteQuest($this->_subjectId, $this->_materialId, $this->_materialType);
                break;
        }

        $this->_flashMessenger->addMessage($this->_getMessage(self::ACTION_DELETE));
        $this->_redirectToIndex('index');

    }

    public function deleteByAction()
    {
        $postMassIds = $this->_getParam('postMassIds_grid', '');
        $subjectId = $this->_getParam('subject_id');

        if (strlen($postMassIds)) {

            if (count($ids = explode(',', $postMassIds))) {
                $isAllDeleted = true;

                foreach ($ids as $id) {
                    list($materialId, $materialType) = explode('-', $id);

                    switch ($materialType) {
                        case HM_Event_EventModel::TYPE_RESOURCE:
                            $deleted = $this->deleteResource($subjectId, $materialId);
                            break;
                        case HM_Event_EventModel::TYPE_COURSE:
                            $deleted = $this->deleteCourse($subjectId, $materialId);
                            break;
                        case HM_Event_EventModel::TYPE_TASK:
                            $deleted = $this->deleteTask($subjectId, $materialId);
                            break;
                        case HM_Event_EventModel::TYPE_POLL:
                        case HM_Event_EventModel::TYPE_TEST:
                            $deleted = $this->deleteQuest($subjectId, $materialId, $materialType);
                            break;
                    }

                    $isAllDeleted = $isAllDeleted && $deleted;
                }

                $flashMessages = $this->getDeleteFlashMessages($postMassIds, $this->notDeleted, $isAllDeleted);
                foreach ($flashMessages as $flashMessage) {
                    $this->_flashMessenger->addMessage($flashMessage);
                }

                $this->notDeleted = null;

                $this->_flashMessenger->addMessage($this->_getMessage(count($ids) > 1 ? self::ACTION_DELETE_BY : self::ACTION_DELETE));
            }
        }

        $this->_redirector->gotoUrl($this->view->url([
            'module' => 'subject',
            'controller' => 'materials',
            'action' => 'index',
            'baseUrl' => '',
            'subject_id' => $this->_subjectId,
        ]));
    }

    protected function _getMessages()
    {
        return [
            self::ACTION_INSERT    => _('Материал успешно создан'),
            self::ACTION_UPDATE    => _('Материал успешно обновлён'),
            self::ACTION_DELETE    => _('Материал успешно удалён'),
            self::ACTION_DELETE_BY => _('Материалы успешно удалены'),
        ];
    }

    protected function _getMessage($type)
    {
        $messages = $this->_getMessages();
        return empty($messages[$type]) ? '' : $messages[$type];
    }


    /**
     * Удаление ресурса с предварительной проверкой на такую возможность
     * Перенесено из @see Resource_ListController::delete()
     *
     * @param $resourceId
     * @return bool
     * @throws Zend_Exception
     * @see Resource_ListController::delete()
     *
     */
    private function deleteResource($subjectId, $resourceId)
    {
        /** @var HM_Resource_ResourceService $resourceService */
        $resourceService = $this->getService('Resource');

        $return = $resourceService->complexRemove($resourceId, $subjectId);
        $resource = $resourceService->getOne($resourceService->findDependence('Revision', $resourceId));

        if(!$return) {
            $this->notDeleted[$resourceId] = $resource;
        }

        return $return;
    }


    /**
     * Перенесено из @see Subject_CoursesController
     *
     * @param $subjectId
     * @param $materialId
     * @return bool
     * @throws HM_Exception
     */
    private function deleteCourse($subjectId, $materialId)
    {
        $course = $this->getOne($this->getService('Course')->find($materialId));
        if ($course) {
            if ($course->chain == $subjectId) {
                if ($this->getService('Teacher')->isUserExists($subjectId, $this->getService('User')->getCurrentUserId()) ||
                    $this->getService('Dean')->isSubjectResponsibility($this->getService('User')->getCurrentUserId(), $subjectId)
                ) {
                    $this->getService('Course')->delete($course->CID);
                    $this->getService('Course')->clearLesson(null, $materialId);

                    return true;
                } else {
                    throw new HM_Exception(_('Вы не являетесь преподавателем на данном учебном курсе.'));
                }
            } else {
                throw new HM_Exception(_('Учебный модуль не используется в данном учебном курсе.'));
            }
        }
    }

    /**
     * Перенесено из @see Subject_QuestController
     * todo: проверка на существование занятий с этим тестом, может не стоит удалять тест из активных занятий?
     * Или clearLesson по аналогии с Course / Resource
     *
     * @param $subjectId
     * @param $materialId
     */
    private function deleteQuest($subjectId, $materialId, $eventType)
    {
        /** @var HM_Quest_QuestService $questService */
        $questService = $this->getService('Quest');
        $questService->delete($materialId);
        $questService->clearLesson(null, $materialId, $eventType);
        $this->getService('SubjectQuest')->deleteBy(array('quest_id = ?' => $materialId));
    }

    private function deleteTask($subjectId, $materialId)
    {
        /** @var HM_Task_TaskService $taskService */
        $taskService = $this->getService('Task');
        $taskService->delete($materialId);
        $taskService->clearLesson(null, $materialId);
        $this->getService('SubjectTask')->deleteBy(array('task_id = ?' => $materialId));
    }

    private function getDeleteFlashMessages($allItems, $notDeletedItems, $isAllDeleted)
    {
        $result = [];

        if ($isAllDeleted) {
            $result[] = $this->_getMessage((count($allItems) > 1) ? self::ACTION_DELETE_BY : self::ACTION_DELETE);
        } else {
            foreach ($notDeletedItems as $item) {
                $result[] = ['message' => sprintf('Вы не можете удалить материал, созданный в Базе знаний. Материал "%s" не удалён!', $item->title), 'type' => HM_Notification_NotificationModel::TYPE_ERROR];
            }

            $result[] = $this->_getMessage((count($allItems) - count($notDeletedItems) > 1) ? self::ACTION_DELETE_BY : self::ACTION_DELETE);
        }

        return $result;
    }

    protected function _redirectToIndex($action = 'index')
    {
        $returnUrl = $this->_request->getParam('returnUrl');
        if($returnUrl) {
            $this->_redirector->gotoUrl($returnUrl);
        } else {
            $this->_redirector->gotoSimple($action, 'materials', 'subject', array('subject_id' => $this->_subjectId));
        }
    }
}

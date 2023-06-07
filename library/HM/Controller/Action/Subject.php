<?php
class HM_Controller_Action_Subject extends HM_Controller_Action implements HM_Controller_Action_Interface_Context
{
    use HM_Controller_Action_Trait_Context;

    protected $_subjectId;
    protected $_subject;
    protected $_isEnduser;
    protected $_isTutor;

    protected $_isAccessible = false;

    public function init()
    {
        $this->view->subjectId = $this->_subjectId = $subjectId = $this->_getParam('subid', $this->_getParam('subject_id', 0));

        /** @var HM_Activity_ActivityService $activityService */
        $activityService = $this->getService('Activity');
        $activityService->initializeActivityCabinet('', 'subject', $this->_subjectId);

        /** @var HM_Subject_SubjectService $subjectService */
        $subjectService = $this->getService('Subject');

        // @todo: plainify?
        $this->view->subject = $this->_subject = $this->getOne($subjectService->findDependence(['ResourceAssign', 'SubjectUser'], $subjectId));

        /** @var HM_Acl $acl */
        $acl = $this->getService('Acl');
        $this->_isEnduser = $acl->checkRoles(HM_Role_Abstract_RoleModel::ROLE_ENDUSER);
        $this->_isTutor = $acl->checkRoles(HM_Role_Abstract_RoleModel::ROLE_TEACHER);
        $redirectUrl = false;

        if ($this->_subject && !$this->isAjaxRequest()) {

            $this->_isAccessible = $subjectService->subjectAccessibleForUser($subjectId);

            if ($this->_isAccessible) {

                $this->initContext($this->_subject);

                $this->view->addSidebar('subject', [
                    'model' => $this->_subject,
                ]);

            } else {
                $this->view->setHeader($this->_subject->name);
            }

            if ($this->_isEnduser) {

                if ($this->_isAccessible) {

                    $backUrl =  [
                        'module' => 'subject',
                        'controller' => 'my',
                        'action' => 'index',
                    ];

                    if (!$this->_getParam('task_id')) {
                        $switcherData = $subjectService->getContextSwitcherData($this->_subject);
                        $this->view->setSwitchContextUrls($switcherData);
                    }

                } else {
                    if (sprintf('%s:%s', $this->_request->getControllerName(), $this->_request->getActionName()) != 'index:happy-end') {
                        if ($this->getService('SubjectMark')->isConfirmationNeeded($this->_subject->subid, $this->getService('User')->getCurrentUserId())) {
                            $redirectUrl = $this->view->url([
                                'module' => 'subject',
                                'controller' => 'index',
                                'action' => 'happy-end',
                                'subject_id' => $this->_subjectId,
                            ]);
                        }
                    }
                }

            } else {
                $backUrl = [
                    'module' => 'subject',
                    'controller' => 'list',
                    'action' => 'index',
                    'base' => $this->_subject->base,
                ];

                if ($this->_isTutor) {
                    $switcherData = $subjectService->getContextSwitcherData($this->_subject);
                    $this->view->setSwitchContextUrls($switcherData);
                }
            }

            if ($backUrl)
                $this->view->setBackUrl($this->view->url($backUrl, null, true));
        }

        parent::init();

        // После init, ибо в нём появляется _redirector
        if ($redirectUrl) {
            $this->_redirect($redirectUrl);
        }

        // что это там внизу..??
        // Какое-то страшное наследие, пока убираю в комментарий, потом удалим, если проблем не будет
        // Проверки, похожие на period_restriction_type есть в $_isAccessible выше

/*
        $isPollExecute = false;
        if (sprintf('%s:%s', $this->_request->getModuleName(), $this->_request->getControllerName()) == 'lesson:execute') {
            $lessonId = (int) $this->_getParam('lesson_id', 0);
            if ($lessonId) {
                $lesson = $this->getOne($this->getService('Lesson')->find($lessonId));
                if ($lesson) {
                    if (in_array($lesson->typeID, array(
                        HM_Event_EventModel::TYPE_POLL,
                        HM_Event_EventModel::TYPE_DEAN_POLL_FOR_LEADER,
                        HM_Event_EventModel::TYPE_DEAN_POLL_FOR_STUDENT,
                        HM_Event_EventModel::TYPE_DEAN_POLL_FOR_TEACHER
                    ))) {
                        $isPollExecute = true;
                    }
                }
            }
        }

        if (!$this->isAjaxRequest() && !$isPollExecute) {

            // автосоздание секций для обратной совместимости с 4.2
            // Убрал, т.к. не даёт полностью избавиться от разделов в курсе
            // if ($this->_subject) {
            //     $this->getService('Section')->getDefaultSection($this->_subject->subid);
            // }

            if ($this->_subject && $acl->checkRoles(HM_Role_Abstract_RoleModel::ROLE_ENDUSER)) {

                if ($this->_subject->period == HM_Subject_SubjectModel::PERIOD_DATES // не совсем верно, есть еще вариант с ограниченной длительностью и он никак не обрабатывается; рассчитываем на то, что скоро появится перевод в прош.обучение по крону и эта проверка не понадобится
                    && strtotime($this->_subject->end) < time()
                    && $this->_subject->period_restriction_type == HM_Subject_SubjectModel::PERIOD_RESTRICTION_STRICT
                ){
                    $this->_flashMessenger->addMessage(array('type' => HM_Notification_NotificationModel::TYPE_NOTICE, 'message' => _('Время обучения на курсе закончилось')));
                    $this->_redirector->gotoSimple('index', 'list', 'subject');
                }

                if (sprintf('%s:%s', $this->_request->getControllerName(), $this->_request->getActionName()) != 'index:happy-end') {
                    if ($this->getService('SubjectMark')->isConfirmationNeeded($this->_subject->subid, $this->getService('User')->getCurrentUserId())) {
                        $this->_redirector->gotoSimple('happy-end', 'index', 'subject', array('subject_id' => $this->_subject->subid));
                    } else {
                    }
                }
            }
        }*/
    }

    public function getContextNavigationSubstitutions()
    {
        return array(
            HM_Controller_Action_Activity::PARAM_CONTEXT_TYPE => HM_Controller_Action_Activity::CONEXT_TYPE_SUBJECT,
            HM_Controller_Action_Activity::PARAM_CONTEXT_ID => $this->_subjectId,

            // это на всякий случай - если в запросе не будет subject_id,
            // то ссылки в контекстном меню сломаются
            'subject_id' => $this->_subjectId,
        );
    }


    static public function initFulltimeAccordeon($view, $subject)
    {
        // для внешних курсов нужен несколько иной аккордеон
        $resources = $modifiers = array();
        if ($subject->type == HM_Subject_SubjectModel::TYPE_FULLTIME) {
            $resources[] = 'cm:subject:page1_1';
            $resources[] = 'cm:subject:page2_1';
            $resources[] = 'cm:subject:page3_1';
        } else {
            $resources[] = 'cm:subject:page1_2';
        }

        foreach ($resources as $resource) {
            $modifiers[] = new HM_Navigation_Modifier_Remove_Page('resource', $resource);
        }

        $view->addContextNavigationModifiers($modifiers);
    }
}
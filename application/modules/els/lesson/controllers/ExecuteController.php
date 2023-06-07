<?php
class Lesson_ExecuteController extends HM_Controller_Action_Subject
{
    public function indexAction()
    {
        //subject/index/index/subject_id/92/course_id/221

        $returnUrl = parse_url($_SERVER['HTTP_REFERER']);
        $returnUrl = $returnUrl['path'].'?'.$returnUrl['query'];

        $lessonId = $this->view->lessonId = (int) $this->_getParam('lesson_id');
        $subjectId = $this->view->subjectId = (int) $this->_getParam('subject_id', 0);

        $lesson = $this->getService('Lesson')->getOne($this->getService('Lesson')->find($lessonId));

        if ($lessonId) {

            try{
                /** @var HM_Lesson_LessonModel $lesson */
                if ($lesson) {
                    $proctoringService = $this->getService('Proctoring');

                    if ($lesson->getService()->isExecutable($lessonId)) {

                        if ( $this->getService('Acl')->inheritsRole($this->getService('User')->getCurrentUserRole(),HM_Role_Abstract_RoleModel::ROLE_STUDENT) ) {

                            // проверка даты начала/окончания курса и занятия
                            $currentDate = new HM_Date();

                            // фиксированная дата курса
                            if ($this->_subject->period == HM_Subject_SubjectModel::PERIOD_DATES && $this->_subject->period_restriction_type != HM_Subject_SubjectModel::PERIOD_RESTRICTION_DECENT) {
                                $subjectBegin = new HM_Date($this->_subject->begin);
                                $subjectEnd   = new HM_Date($this->_subject->end);
                                if ($subjectBegin->getTimestamp() > $currentDate->getTimestamp() || $subjectEnd->getTimestamp() < $currentDate->getTimestamp()) {
                                    $this->_flashMessenger->addMessage(array('type' => HM_Notification_NotificationModel::TYPE_ERROR, 'message' => ($subjectBegin->getTimestamp() > $currentDate->getTimestamp())? _('Дата начала курса не наступила') : _('Курс завершен')));
                                    $this->_redirector->gotoUrl($returnUrl);
                                }
                            }
                        }

                        /* Логирование захода пользователя в занятие */
                        $this->getService('Session')->toLog(array('lesson_id' => $lesson->SHEID, 'course_id' => $subjectId, 'lesson_type' => $lesson->typeID));

                        $this->getService('LessonAssign')->onLessonStart($lesson);

                        if ($lesson->isExternalExecuting()) {
                            Zend_Registry::get('session_namespace_default')->lesson['execute']['returnUrl'] = $_SERVER['HTTP_REFERER'];
                            $this->_redirector->gotoUrl($lesson->getExecuteUrl());
                            //header('Location: '.$lesson->getExecuteUrl());
                            //exit();
                        } else {
                            $url = '';

                            if (strlen($url)) {
                                $this->view->url = $url;
                                return true; // render index.tpl
                            }

                            $this->_flashMessenger->addMessage(array('message' => _('Данное занятие невозможно запустить'), 'type' => HM_Notification_NotificationModel::TYPE_ERROR));
                        }
                    }
                } else {
                    $this->_flashMessenger->addMessage(array('message' => _('Занятие не найдено'), 'type' => HM_Notification_NotificationModel::TYPE_ERROR));
                }

            } catch(HM_Exception $exception) {
                $this->_flashMessenger->addMessage(array('message' => $exception->getMessage(), 'type' => HM_Notification_NotificationModel::TYPE_ERROR));
            }

        } else {
            $this->_flashMessenger->addMessage(array('message' => _('Не указан идентификатор занятия'), 'type' => HM_Notification_NotificationModel::TYPE_ERROR));
        }

        //$this->_redirector->gotoSimple('index', 'index', 'default');
        $this->_redirector->gotoUrl($returnUrl);

    }



}
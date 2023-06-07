<?php
class Meeting_ExecuteController extends HM_Controller_Action_Project
{
    public function indexAction()
    {
        //project/index/index/project_id/92/course_id/221

        $returnUrl = $_SERVER['HTTP_REFERER'];
        $meetingId = $this->view->meetingId = (int) $this->_getParam('meeting_id');
        if ($meetingId) {

            try{
                $meeting = $this->getService('Meeting')->getOne($this->getService('Meeting')->find($meetingId));
                if ($meeting) {
                    if ($meeting->getService()->isExecutable($meetingId)) {

                        if ( $this->getService('Acl')->inheritsRole($this->getService('User')->getCurrentUserRole(),HM_Role_Abstract_RoleModel::ROLE_PARTICIPANT) ) {

                            // проверка даты начала/окончания проекта и занятия
                            $currentDate = new HM_Date();

                            // фиксированная дата проекта
                            if ($this->_project->period == HM_Project_ProjectModel::PERIOD_DATES) {
                                $projectBegin = new HM_Date($this->_project->begin);
                                $projectEnd   = new HM_Date($this->_project->end);
                                if ($projectBegin->getTimestamp() > $currentDate->getTimestamp() || $projectEnd->getTimestamp() < $currentDate->getTimestamp()) {
                                    $this->_flashMessenger->addMessage(array('type' => HM_Notification_NotificationModel::TYPE_ERROR, 'message' => ($projectBegin->getTimestamp() > $currentDate->getTimestamp())? _('Дата начала проекта не наступила') : _('Курс завершен')));
                                    $this->_redirector->gotoUrl($returnUrl);
                                }
                            }
                            // фиксированная дата занятия
                            if (!$meeting->recommend && ($meeting->timetype == HM_Meeting_MeetingModel::TIMETYPE_DATES || $meeting->timetype == HM_Meeting_MeetingModel::TIMETYPE_TIMES)) {
                                $meetingBegin = new HM_Date($meeting->begin);
                                $meetingEnd   = new HM_Date($meeting->end);
                                if ($meetingBegin->getTimestamp() > $currentDate->getTimestamp() || $meetingEnd->getTimestamp() < $currentDate->getTimestamp()) {
                                    $this->_flashMessenger->addMessage(array('type' => HM_Notification_NotificationModel::TYPE_ERROR, 'message' => ($meetingEnd->getTimestamp() > $currentDate->getTimestamp())? _('Дата начала занятия не наступила') : _('Занятие завершено')));
                                    $this->_redirector->gotoUrl($returnUrl);
                                }
                            }
                            // относительная дата занятия
                            if (!$meeting->recommend && $meeting->timetype == HM_Meeting_MeetingModel::TIMETYPE_RELATIVE) {
                                $meetingAssign = $this->getService('MeetingAssign')->getOne($this->getService('MeetingAssign')->fetchAll(array('meeting_id = ?'  => $meetingId, 'MID = ?'    => $this->getService('User')->getCurrentUserId())));
                                $meetingBegin = new HM_Date($meetingAssign->beginRelative);
                                $meetingEnd   = new HM_Date($meetingAssign->endRelative);
                                if ($meetingBegin->getTimestamp() > $currentDate->getTimestamp() || $meetingEnd->getTimestamp() < $currentDate->getTimestamp()) {
                                    $this->_flashMessenger->addMessage(array('type' => HM_Notification_NotificationModel::TYPE_ERROR, 'message' => ($meetingEnd->getTimestamp() > $currentDate->getTimestamp())? _('Дата начала занятия не наступила') : _('Занятие завершено')));
                                    $this->_redirector->gotoUrl($returnUrl);
                                }
                            }
                        }
                        
                        $this->getService('MeetingAssign')->onMeetingStart($meeting);

                        if ($meeting->isExternalExecuting()) {
                            Zend_Registry::get('session_namespace_default')->meeting['execute']['returnUrl'] = $_SERVER['HTTP_REFERER'];
                            $this->_redirector->gotoUrl($meeting->getExecuteUrl());
                            //header('Location: '.$meeting->getExecuteUrl());
                            //exit();
                        } else {
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
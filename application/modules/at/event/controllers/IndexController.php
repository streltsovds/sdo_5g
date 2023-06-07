<?php
class Event_IndexController extends HM_Controller_Action 
{
    public function indexAction()
    {
        $sessionEventId = $this->_getParam('session_event_id');
        $collection = $this->getService('AtSessionEvent')->find($sessionEventId);
        if (count($collection)) {
            
            $event = $this->getService('AtSessionEvent')->getOne($collection);
            if (is_a($event, 'HM_At_Session_Event_Method_CompetenceModel')) { // сложные контроллеры, одним run не обойтись
                
                if ($event->isMultipage()) {
                    $this->_redirector->gotoSimple('start', 'competence-multipage', 'event', array('session_event_id' => $sessionEventId));                
                } else {
                    $this->_redirector->gotoSimple('index', 'competence', 'event', array('session_event_id' => $sessionEventId));
                }
                
            } elseif (is_a($event, 'HM_At_Session_Event_Method_KpiModel')) {
                
                $this->_redirector->gotoSimple('index', 'kpi', 'event', array('session_event_id' => $sessionEventId));
                
            } elseif (is_a($event, 'HM_At_Session_Event_Method_RatingModel')) {
                
                $this->_redirector->gotoSimple('start', 'rating', 'event', array('session_event_id' => $sessionEventId));
                
            } elseif (is_a($event, 'HM_At_Session_Event_Method_TestModel')) {

                $url = $this->view->url(array('module' => 'quest', 'controller' => 'event', 'action' => 'start', 'baseUrl' => '', 'session_event_id' => $sessionEventId, 'quest_id' => $event->quest_id));
                $this->_redirector->gotoUrl($url, array('prependBase' => false));

            } elseif (is_subclass_of($event, 'HM_At_Session_Event_Method_Quest_Abstract')) {
                
                $url = $this->view->url(array('module' => 'quest', 'controller' => 'event', 'action' => 'start', 'baseUrl' => '', 'session_event_id' => $sessionEventId, 'quest_id' => $event->quest_id, 'advance' => 1));
                $this->_redirector->gotoUrl($url, array('prependBase' => false));
                
            } else {
                $this->_redirector->gotoSimple('run', 'index', 'event', array('session_event_id' => $sessionEventId));
                //$this->_forward('run'); // @todo: почему не работает forward?                
            }
            return;
        } else {
            $this->_flashMessenger->addMessage(array(
                'type'    => HM_Notification_NotificationModel::TYPE_ERROR,
                'message' => _('Анкета не найдена')
            ));
            $this->_redirector->gotoSimple('my', 'event', 'session', array('session_event_id' => $sessionEventId));
        }
    }
    
    // DEPRECATED!
    public function runAction()
    {
        $sessionEventId = $this->_getParam('event_id');
        $currentUserId = $this->getService('User')->getCurrentUserId();
        $isChief = false;

        if ($event = $this->getService('AtSessionEvent')->getOne(
            $this->getService('AtSessionEvent')->findDependence(array('User', 'Chief', 'Evaluation', 'EvaluationResult', 'EvaluationMemo'), $sessionEventId))
        ) {

            $isChief = ($event->chief->MID == $currentUserId) ||
                $this->getService('Acl')->inheritsRole($this->getService('User')->getCurrentUserRole(),HM_Role_Abstract_RoleModel::ROLE_CHIEF);

            if (!($event->begin_date && $event->end_date)) {
                $this->_flashMessenger->addMessage(array(
                    'type'    => HM_Notification_NotificationModel::TYPE_NOTICE,
                    'message' => _('Необходимо указать период проведения мероприятия')
                ));
                $this->_redirector->gotoSimple('edit',null,null,array('event_id' => $sessionEventId));
            }

            $errors = array();
            if ($event->evaluation) {
                $event->evaluation = $event->evaluation->current();
            } else {
                $errors[] = _('Не задан вид оценки мероприятия');
            }
            if ($event->user) {
                $event->user = $event->user->current();
            } else {
                $errors[] = _('Не задан участник мероприятия');
            }
            if ($event->chief) {
                $event->chief = $event->chief->current();
            } else {
                $errors[] = _('Не задан руководитель, проводящий мероприятие');
            }

            $peoples = array();
            $orgItems = $this->getService('Orgstructure')->fetchAll(array('mid=?' => $currentUserId));
            if (count($orgItems)) {
              foreach($orgItems as $orgItem) {
                  $peoples = array_merge($peoples,$this->getService('Orgstructure')->getChildren($orgItem->soid,false)->getList('soid','mid'));
              }
            }
            if (!(($event->chief->MID == $currentUserId) ||
                  ($event->user->MID == $currentUserId)  ||
                   in_array($event->user->MID, $peoples)  || // #9716 - разрешить видеть отчет всех подчиненных по иерархии
                  ($this->getService('Acl')->inheritsRole($this->getService('User')->getCurrentUserRole(), array(HM_Role_Abstract_RoleModel::ROLE_TEACHER,HM_Role_Abstract_RoleModel::ROLE_FODHQ,HM_Role_Abstract_RoleModel::ROLE_PUPERVISOR))))) { // @todo: || самый главный менеджер
                $errors[] = _('У вас нет прав на просмотр данного мероприятия');
            }

            $sessionUser = $this->getService('AtSessionUser')->getOne($this->getService('AtSessionUser')->findDependence('Session', $event->session_user_id));
            $event->scale = $this->getService('AtScale')->findDependence('ScaleValue', $event->evaluation->scale_id)->current();

            if (!$event->scale) {
                $errors[] = _('Не задана шкала оценки мероприятия');
            }
            if (($methodSpecificValidation = $event->isValid()) !== true) {
                $errors[] = $methodSpecificValidation;
            }

            // important!!
            $event->init();

            if ($this->_request->isPost()) {

                $values = $this->_request->getPost();
                if ($event->status !== HM_At_Session_Event_EventModel::STATUS_COMPLETED) {

                    // может быть сохранение результатов тоже специфично для каждой методики?
                    if (count($event->evaluationResults)) {
                        $this->getService('AtEvaluationResults')->deleteBy(array('session_event_id = ?'  => $sessionEventId));
                    }
                    foreach ($values['criteria'] as $criterionId => $value) {
                        $this->getService('AtEvaluationResults')->insert(array(
                            'criterion_id'  => $criterionId,
                            'session_event_id'  => $sessionEventId,
                            'value_id'  => $event->getMethodValue($value),
                            'value_weight'  => (($weight = $event->getWeight($value)) !== false) ? $weight : null,
                        ));
                    }

                    foreach ($values['extra_criteria'] as $key => $value) {
                        if (strpos($key, 'rid') !== 0) continue;
                        $this->getService('AtEvaluationResults')->insert(array(
                            'custom_criterion_parent_id'  => $value['criterion_id'],
                            'custom_criterion_name'  => $value['title'],
                            'session_event_id'  => $sessionEventId,
                            'value_id'  => $event->getMethodValue($value),
                            'value_weight'  => (($weight = $event->getWeight($value)) !== false) ? $weight : null,
                        ));
                    }

                    if (count($event->evaluationMemos)) {
                        $this->getService('AtEvaluationMemos')->deleteBy(array('session_event_id = ?'  => $sessionEventId));
                    }
                    foreach ($values['memo'] as $memoInternalId => $memoValue) {
                        $this->getService('AtEvaluationMemos')->insert(array(
                            'memo_internal_id'  => $memoInternalId,
                            'value'  => Zend_Filter::filterStatic($memoValue, 'StripTags'),
                            'session_event_id'  => $sessionEventId,
                        ));
                    }

                    $event->savesubmethod($values);

                    if ($this->_getParam('finalize', false)) {
                        $this->getService('AtSessionEvent')->updateWhere(array(
                            'status' => HM_At_Session_Event_EventModel::STATUS_COMPLETED
                        ), array('at_session_event_id = ?' => $sessionEventId));
                    }
                    $this->_flashMessenger->addMessage(array('message' => _('Результат сохранен успешно'), 'type' => HM_Notification_NotificationModel::TYPE_SUCCESS));
                    $this->_redirect('/evaluation/list/index' . ($isChief ? '/all/1' : ''));
                    return true;

                } else {

                    $this->_flashMessenger->addMessage(array('message' => _('Невозможно сохранить результат'), 'type' => HM_Notification_NotificationModel::TYPE_ERROR));
                    $this->_redirect('/evaluation/list/index' . ($isChief ? '/all/1' : ''));
                    return true;

                }
            }

            if (count($errors)) {
                $this->_flashMessenger->addMessage(array('message' => implode('. ', $errors), 'type' => HM_Notification_NotificationModel::TYPE_ERROR));
                $this->_redirect('/evaluation/list/index' . ($isChief ? '/all/1' : ''));
                return true;
            }

            if ($event->begin_date) {
                $event->begin_date = $this->getDateForGrid($event->begin_date, true);
            }

            $this->view->session = $this->getService('AtSession')->getOne($sessionUser->session);
            $this->view->readonly = (($event->status == HM_At_Session_Event_EventModel::STATUS_COMPLETED) || !$isChief);
            $this->view->isChief = $isChief;
            $this->view->event = $event;
            $this->view->backUrl = (strstr($_SERVER['HTTP_REFERER'],'my-fc') !== false)? substr($this->view->url(array('module' => 'user', 'controller' => 'index', 'action' => 'my-fc', 'user_id' => $event->user_id),null,true),3) : $this->view->url(array('event_id' => null, 'module' => 'evaluation', 'controller' => 'list', 'action' => 'index', 'all' => $this->isChief ? 1 : null));

            $this->_helper->viewRenderer->setNoRender();
            $this->view->addScriptPath(APPLICATION_PATH . "/views/methods/{$event->type}/");
            echo $this->view->render(HM_At_Session_Event_EventModel::FORM_SCREEN . '.tpl');

        } else {
            $this->_flashMessenger->addMessage(array('message' => _('Не найдено мероприятие'), 'type' => HM_Notification_NotificationModel::TYPE_ERROR));
            $this->_redirect('/evaluation/list/index' . ($isChief ? '/all/1' : ''));
            return true;
        }
    }
        
    public function printAction()
    {
        $sessionEventId = $this->_getParam('session_event_id');

        if ($event = $this->getService('AtSessionEvent')->getOne(
            $this->getService('AtSessionEvent')->findDependence(array('SessionEventUser', 'SessionEventRespondent', 'Evaluation', 'EvaluationResult', 'EvaluationMemo'), $sessionEventId))
        ) {
            $this->_helper->viewRenderer->setNoRender();
            $this->view->addScriptPath(APPLICATION_PATH . "/views/methods/{$event->method}/");
            echo $this->view->render(HM_At_Session_Event_EventModel::FORM_PRINT . '.tpl');
        }
    }
}
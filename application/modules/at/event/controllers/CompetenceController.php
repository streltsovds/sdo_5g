<?php
/**
 * Заполнение анкеты 360, НЕ в режиме Multipage (всё на одной странице)
 * Используется та же модель, что и в MultipageController
 */
class Event_CompetenceController extends HM_Controller_Action_SessionEvent
{
    public function indexAction()
    {
        $currentUserId = $this->getService('User')->getCurrentUserId();
        $errors = array();

        $this->view->model = $this->_event->getAttempt()->getModel();
        $this->view->results = $this->_event->getAttempt()->getResults();
        $this->view->memoResults = $this->_event->getAttempt()->getMemoResults();

        $position = $this->getService('Orgstructure')->findDependence(array('User', 'Parent', 'Profile'), $this->_event->sessionUser->current()->position_id)->current();
        $this->view->singleScreen = true;
        
        if (count($this->_event->user)) $info['user'] = $this->_event->user->current(); // нельзя брать из $position!
        if (count($this->_event->session)) $info['session'] = $this->_event->session->current(); // нельзя брать из $position!
        if (count($position->parent)) $info['department'] = $position->parent->current();
        if (count($position->profile)) $info['profile'] = $position->profile->current();
        $this->view->info = $info;

        $this->view->navPanel = array(
			'stop' => $this->view->url(array('action' => 'stop')),
			'finalize' => $this->view->url(array('action' => 'finalize')),
        );

        $this->_helper->viewRenderer->setNoRender();
        $this->view->addScriptPath(APPLICATION_PATH . "/views/methods/{$this->_event->method}/");
        echo $this->view->render(HM_At_Session_Event_EventModel::FORM_SCREEN . '.tpl');

    }

    public function saveAction()
    {
        if ($this->_request->isPost()) {

            $results = $this->_getParam('results', array());
            $memos = $this->_getParam('memos', array());
            $finalize = $this->_getParam('finalize', 0);
            if ($this->_event->status !== HM_At_Session_Event_EventModel::STATUS_COMPLETED) {

                $result = false;
                $model = $this->_event->getAttempt()->getModel();
                $this->_event->getAttempt()->setResults(HM_At_Criterion_Cluster_ClusterModel::NONCLUSTERED, $results);
                $session = count($this->_event->session) ? $this->_event->session->current() : false;
                
                if (!Zend_Registry::get('serviceContainer')->getService('Option')->getOption('competenceUseIndicators', $session->getOptionsModifier())) {
                    $result = $this->getService('AtEvaluationResults')->saveResults($this->_event, array_keys($model['index'][HM_At_Criterion_Cluster_ClusterModel::NONCLUSTERED]), $results);
                } else {
                    $result = $this->getService('AtEvaluationIndicator')->saveResults($this->_event, $model['index'][HM_At_Criterion_Cluster_ClusterModel::NONCLUSTERED], $results);
                    $results = array(HM_At_Criterion_Cluster_ClusterModel::NONCLUSTERED => $results);
                    $this->getService('AtEvaluationIndicator')->saveTotalResults($this->_event, $model['scale']->scale_id, $model['index'], $results);
                }
                $memoResult = $this->getService('AtEvaluationMemoResult')->saveMemoResults($this->_event, $memos);

                if ($result && $memoResult) {
                    $return = array(
                        'result' => true,
                        'alert' => false,
                        'confirm' => $finalize ? _('Выполняя данную операцию, Вы подтвержаете что анкета заполнена корректно и дальнейшему изменению не подлежит. Продолжить?') : false,
                    );
                } else {
                    $return = array(
                        'result' => false,
                        'alert' => $finalize ?
                            _('Остались незаполненные поля. Выполняя данную операцию, Вы подтвержаете что анкета заполнена корректно и дальнейшему изменению не подлежит. Необходимо заполнить оставшиеся поля, либо прервать заполнение анкеты.') :
                            false,
                        'confirm' => $finalize ?
                            false :
                            _('Остались незаполненные поля. Вы можете вернуться к заполнению анкеты позже. Продолжить?'),
                    );
                }
            } else {
                $return = array(
                    'result' => false,
                    'alert' => _('Невозможно сохранить результат'),
                    'confirm' => false,
                );
            }
        }
        exit (HM_Json::encodeErrorSkip($return));
    }

    public function stopAction()
    {
        $this->_flashMessenger->addMessage(array('message' => _('Заполнение анкеты прервано'), 'type' => HM_Notification_NotificationModel::TYPE_NOTICE));
        $this->_redirect('/session/event/my/session_id/' . $this->_event->session_id);
    }

    public function finalizeAction()
    {
        // если всего один эксперт оценивает компетенции - сразу выставляем итоговые оценки
        if (!count($this->getService('AtSessionEvent')->getSameMethodEvents($this->_event))) {
            $this->getService('AtSessionUserCriterionValue')->setCriteriaValues($this->_event->session_user_id);
        }        

        $this->getService('AtSessionEvent')->updateStatus($this->_event->session_event_id, HM_At_Session_Event_EventModel::STATUS_COMPLETED);
        
        $this->_flashMessenger->addMessage(array(
            'message' => _('Заполнение анкеты завершено'),
            'type' => HM_Notification_NotificationModel::TYPE_SUCCESS,
        ));

        $action = $this->getService('Acl')->inheritsRole($this->getService('User')->getCurrentUserRole(), HM_Role_Abstract_RoleModel::ROLE_ENDUSER) ? 'my' : 'list';
        $this->_redirector->gotoSimple($action, 'event', 'session', array('session_id' => $this->_event->session_id));
    }
}
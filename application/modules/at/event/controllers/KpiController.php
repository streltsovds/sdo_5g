<?php
/**
 * Заполнение анкеты KPI
 */
class Event_KpiController extends HM_Controller_Action_SessionEvent
{
    public function indexAction()
    {
        $currentUserId = $this->getService('User')->getCurrentUserId();
        $errors = array();

        $this->view->model = $this->_event->getAttempt()->getModel();
        $this->view->results = $this->_event->getAttempt()->getResults();
        $this->view->memoResults = $this->_event->getAttempt()->getMemoResults();

        $position = $this->getService('Orgstructure')->findDependence(array('User', 'Parent', 'Profile'), $this->_event->sessionUser->current()->position_id)->current();
        $info = array('position' => $position);
        if (count($this->_event->user)) $info['user'] = $this->_event->user->current();
        if (count($this->_event->session)) $info['session'] = $this->_event->session->current();
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
        $return = array(
            'result' => false,
            'alert' => false,
            'confirm' => false,
            'lightdialog' => false,
        );
        $model = $this->_event->getAttempt()->getModel();

        if ($this->_request->isPost()) {

            $kpis = $this->_getParam('kpis', array());
            $results = $this->_getParam('results', array());
            $comments = $this->_getParam('comments', array());
            $memos = $this->_getParam('memos', array());
            $finalize = $this->_getParam('finalize', 0);
            if ($this->_event->status !== HM_At_Session_Event_EventModel::STATUS_COMPLETED) {

                $kpiResult = true;
                foreach ($kpis as $userKpiId => $value) {
                    if (trim($value) === '') {
                        $kpiResult = false;
                        //continue;
                    }
                    $data = array(
                        'value_fact' => $value,
                        'comments' => trim($comments[$userKpiId]),
                    );
                    $this->getService('AtKpiUserResult')->setResult($userKpiId, $data, $model['evaluation']->relation_type);
                }

                $result = false;
                $result = $this->getService('AtEvaluationResults')->saveResults($this->_event, count($model['criteria']) ? $model['criteria']->getList('criterion_id') : array(), $results);
                $memoResult = $this->getService('AtEvaluationMemoResult')->saveMemoResults($this->_event, $memos);

                // вообще-то это специфика Газнефть
                // ограничение на количество максимальных ответов по одному критерию в рамках подразделения
                $maxResultCondition = true;
                if (HM_At_Evaluation_Results_ResultsModel::MAX_RESULT_LIMIT && count($this->_event->position)) {

                    $events = $this->getService('AtSessionEvent')->getSiblingsEvents($this->_event);

                    if ($model['scale'] && count($model['scale']->scaleValues)) {
                        $values = $model['scale']->scaleValues->getList('value_id', 'value');
                        asort($values);
                        $scaleValueId = array_pop(array_keys($values));
                    }

                    if ($scaleValueId && ($resultsTotal = count($events))) {
                        foreach ($events as $event) {
                            if (count($event->evaluationResults) && count($event->user)) {
                                foreach ($event->evaluationResults as $evaluationResult) {
                                    if ($evaluationResult->value_id == $scaleValueId) {
                                        $resultsWitValue[$evaluationResult->criterion_id][] = $event->user->current();
                                    }
                                }
                            }
                        }
                    }

                    foreach ($resultsWitValue as $criterionId => $users) {
                        if ((count($users) > 1) && (100 * count($users) / $resultsTotal > HM_At_Evaluation_Results_ResultsModel::MAX_RESULT_LIMIT)) {
                            $maxResultCondition = false;
                        }
                    }
                }

                if ($model['evaluation']->relation_type == HM_At_Evaluation_EvaluationModel::RELATION_TYPE_SELF) {
                    $fillCondition = $kpiResult;
                } else {
                    $fillCondition = $kpiResult && $memoResult && (is_array($model['criteria']) && count($model['criteria']) ? $result : true);
                }

                $useClusters = $this->getService('Option')->getOption('competenceUseClusters', $session?$session->getOptionsModifier():'');

                if ($fillCondition) {
                    if (!$maxResultCondition) {
                        $return['lightdialog'] = $this->view->url(array('module' => 'event', 'controller' => 'kpi', 'action' => 'results-correction'));
                    } else {
                        $return['result'] = true;
                        $return['confirm'] = $finalize ? _('Выполняя данную операцию, Вы подтвержаете что анкета заполнена корректно. Продолжить?') : false;
                    }
                } else {
                    if ($finalize && $useClusters) {
                        $return['alert'] = _('Остались незаполненные поля. Выполняя данную операцию, Вы подтвержаете что анкета заполнена корректно и дальнейшему изменению не подлежит. Необходимо заполнить оставшиеся поля, либо прервать заполнение анкеты.');
                    } else {
                        $return['confirm'] = _('Остались незаполненные поля. Вы можете вернуться к заполнению анкеты позже. Продолжить?');
                    }
                }
            } else {
                $return['alert'] = _('Невозможно сохранить результат');
            }
        }
        exit (HM_Json::encodeErrorSkip($return));
    }

    public function stopAction()
    {
        $this->_flashMessenger->addMessage(array('message' => _('Заполнение анкеты прервано'), 'type' => HM_Notification_NotificationModel::TYPE_NOTICE));
        $this->_redirectToIndex();
    }

    public function finalizeAction()
    {
        $this->getService('AtSessionEvent')->updateStatus($this->_event->session_event_id, HM_At_Session_Event_EventModel::STATUS_COMPLETED);

        $this->_flashMessenger->addMessage(array(
            'message' => _('Заполнение анкеты завершено'),
            'type' => HM_Notification_NotificationModel::TYPE_SUCCESS,
        ));
        $this->_redirectToIndex();
    }

    protected function _redirectToIndex()
    {
        $session = $this->getService('AtSession')->getOne(
            $this->getService('AtSession')->findDependence(array('Newcomer', 'Reserve'), $this->_event->session_id));

        if (count($session->newcomer)) {
            $newcomer = $session->newcomer->current();
            $this->_redirector->gotoUrl($this->view->url(array(
                'module' => 'newcomer',
                'controller' => 'report',
                'action' => 'index',
                'baseUrl' => 'recruit',
                'newcomer_id' =>$newcomer->newcomer_id,
            )), array('prependBase' => false));
        } elseif (count($session->reserve)) {
            $reserve = $session->reserve->current();
            $this->_redirector->gotoUrl($this->view->url(array(
                'module' => 'reserve',
                'controller' => 'report',
                'action' => 'index',
                'baseUrl' => 'hr',
                'reserve_id' =>$reserve->reserve_id,
            )), array('prependBase' => false));
        } else {
            $action = $this->getService('Acl')->inheritsRole($this->getService('User')->getCurrentUserRole(), HM_Role_Abstract_RoleModel::ROLE_ENDUSER) ? 'my' : 'list';
            $this->_redirector->gotoSimple($action, 'event', 'session', array('session_id' => $this->_event->session_id, 'ajax' => null));
        }
    }

    public function resultsCorrectionAction()
    {
        $model = $this->_event->getAttempt()->getModel();
        if (!$this->_request->isPost()) {

            $events = $this->getService('AtSessionEvent')->getSiblingsEvents($this->_event);

            if ($model['scale'] && count($model['scale']->scaleValues)) {
                $values = $model['scale']->scaleValues->getList('value_id', 'value');
                asort($values);
                $scaleValueId = array_pop(array_keys($values));
            }

            $resultsWitValue = array();
            foreach ($events as $event) {
                if (count($event->evaluationResults) && count($event->user)) {
                    foreach ($event->evaluationResults as $evaluationResult) {
                        if ($evaluationResult->value_id == $scaleValueId) {
                            $resultsWitValue[$evaluationResult->criterion_id][$event->session_user_id] = $event->user->current();
                        }
                    }
                }
            }

//            $resultsWitValue = array_filter($resultsWitValue, function($item) { return is_array($item) && (count($item) > 1); });

            if (count($resultsWitValue)) {

                $criteria = $this->getService('AtCriterionKpi')->fetchAll(array('criterion_id IN (?)' => array_keys($resultsWitValue)))->getList('criterion_id', 'name');

                $this->view->maxResultLimit = HM_At_Evaluation_Results_ResultsModel::MAX_RESULT_LIMIT . '%';
                $this->view->resultsWitValue = $resultsWitValue;
                $this->view->criteria = $criteria;
                $this->view->event = $this->_event;
            }

        } else {

            $return = array(
                'result' => false,
                'alert' => false,
                'confirm' => false,
                'lightdialog' => false,
            );

            if ($model['scale'] && count($model['scale']->scaleValues)) {
                $values = $model['scale']->scaleValues->getList('value_id', 'value');
                asort($values);
                $scaleValueIds = array_keys($values);
                array_pop($scaleValueIds);
                $scaleValueId = array_pop($scaleValueIds); // 2nd value
            }

            $corrections = $this->_getParam('corrections', array());
            foreach ($corrections as $criterionId => $sessionUserId) {
                $this->getService('AtEvaluationResults')->updateWhere(array(
                    'value_id' => $scaleValueId,
                ), array(
                    'session_user_id = ?' => $sessionUserId,
                    'criterion_id = ?' => $criterionId,
                ));
            }

            $return['result'] = true;
            exit (HM_Json::encodeErrorSkip($return));
        }
    }

}
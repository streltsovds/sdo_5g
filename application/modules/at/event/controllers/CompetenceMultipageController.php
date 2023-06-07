<?php
/**
 * Реализация многостраничной анкеты 360 на основе Quest
 * Используется та же модель, что и в одностраничной анкете 360
 */
class Event_CompetenceMultipageController extends HM_Controller_Action implements HM_Multipage_Controller_Interface
{
    use HM_Controller_Action_Trait_Multipage;

    const NAMESPACE_MULTIPAGE = 'competence-multipage';
    
    protected $_sessionId;

    public function init()
    {
        $this->setNamespaceMultipage(self::NAMESPACE_MULTIPAGE);

        $this->initMultipage();
        
        parent::init();
        if (isset($this->_persistentModel)) {
            $model = $this->_persistentModel->getModel();
            $this->_sessionId = $model['event']->session_id;
            $this->_helper->viewRenderer->setNeverController(true);
            $this->view->addScriptPath(APPLICATION_PATH . "/views/methods/competence/");
        } 
    }

    public function _redirectToIndex($msg = '', $type = HM_Notification_NotificationModel::TYPE_SUCCESS)
    {
        if (!empty($msg)) {
            $this->_flashMessenger->addMessage(array(
                'type'    => $type,
                'message' => $msg
            ));
        }
        if (!$this->isAjaxRequest()) {


            $session = $this->getService('AtSession')->getOne(
                $this->getService('AtSession')->findDependence(array('Newcomer', 'Reserve'), $this->_sessionId));

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
                $url = $this->view->url(array('module' => 'session', 'controller' => 'event', 'action' => $action, 'session_id' => $this->_sessionId));
                $this->_redirector->gotoUrl($url, array('prependBase' => false));
            }
        }
    }

    public function _redirectToMultipage($msg = '')
    {
        if (!empty($msg)) {
            $this->_flashMessenger->addMessage(array(
                'type'    => HM_Notification_NotificationModel::TYPE_CRIT,
                'message' => $msg
            ));
        }
        if (!$this->isAjaxRequest()) {
            $this->_redirector->gotoSimple('view', 'competence-multipage', 'event', array('session_event_id' => $this->_getMultipageId()));
        }
    }
    
    public function _redirectToError($msg = '', $type = HM_Notification_NotificationModel::TYPE_SUCCESS)
    {
        if (!empty($msg)) {
            $this->_flashMessenger->addMessage(array(
                'type'    => $type,
                'message' => $msg
            ));
        }
        if (!$this->isAjaxRequest()) {
            $url = $this->view->url(array('module' => 'default', 'controller' => 'index', 'action' => 'index'));
            $this->_redirector->gotoUrl($url, array('prependBase' => false));
        }
    }    

    public function _getBaseUrl()
    {
        return array('module' => 'event', 'controller' => 'competence-multipage');
    }

    public function _getMultipageId()
    {
        $model = $this->_persistentModel->getModel();

        if (isset($model['event']) && isset($model['event']->session_event_id)) {
            return $model['event']->session_event_id;
        }
        return null;
    }

    public function _isCurrentMultipage()
    {
        $sessionEventId = $this->_getParam('session_event_id');
        $currentId = $this->_getMultipageId();

        if ($currentId && ($currentId == $sessionEventId)) {
            return true;
        }
        return false;
    }

    public function _getPersistentModel()
    {
        $sessionEventId = $this->_getParam('session_event_id');
        $collection = $this->getService('AtSessionEvent')->findDependence('SessionUser', $sessionEventId);
        if (count($collection)) {
            $event = $this->getService('AtSessionEvent')->getOne($collection);
            $attempt = $this->getService('AtSessionEventAttempt')->insert(array(
                'method' => $event->method,
                'session_event_id' => $event->session_event_id,
                'user_id' => $this->getService('User')->getCurrentUserId(),
                'date_begin' => HM_Date::now()->toString('yyyy-MM-dd HH:mm:ss'),
            ));
            if ($event->status != HM_At_Session_Event_EventModel::STATUS_IN_PROGRESS) {
                $this->getService('AtSessionEvent')->updateStatus($event->session_event_id, HM_At_Session_Event_EventModel::STATUS_IN_PROGRESS);
            }
            return $attempt;
        }
    }

    public function _getProgressTitle($clusterId)
    {
        $model = $this->_persistentModel->getModel();
        return $model['clusters'][$clusterId]->name;
    }

    public function _getItemProgress($clusterId)
    {
        $model = $this->_persistentModel->getModel();
        $results = $this->_persistentModel->getResults();

        $total = 0;
        $filled = count($results[$clusterId]);
        foreach ($model['index'][$clusterId] as $indicators) {
            $total += count($indicators);
        }

        return $total ? round(100 * $filled/$total) : ($filled ? 100 : 0); // не уверен, что правильная логика для случая total = 0; это режим без индикаторов
    }

    public function _setInfo()
    {
        $info = false;
        $model = $this->_persistentModel->getModel();
        if (count($model['event']->sessionUser)) {
            $position = $this->getService('Orgstructure')->findDependence(array('User', 'Parent', 'Profile'), $model['event']->sessionUser->current()->position_id)->current();
            $info = array('position' => $position);
            if (count($model['event']->user)) $info['user'] = $model['event']->user->current(); // нельзя брать из $position!
            if (count($model['event']->session)) $info['session'] = $model['event']->session->current(); // нельзя брать из $position!
            if (count($position->parent)) $info['department'] = $position->parent->current();
            if (count($position->profile)) $info['profile'] = $position->profile->current();
        }
        return $info;
    }

    public function _saveResults($clusterId, $results)
    {
        $result = false;
        $model = $this->_persistentModel->getModel();

        if (!$model['options']['competenceUseIndicators']) {
            $result = $this->getService('AtEvaluationResults')->saveResults($model['event'], array_keys($model['index'][$clusterId]), $results);
        } else {
            $result = $this->getService('AtEvaluationIndicator')->saveResults($model['event'], $model['index'][$clusterId], $results);
        }

        if ($result) {
            return array(
                'result' => true,
                'alert' => false,
                'confirm' => false,
                'progress' => $this->_getItemProgress($clusterId),
                'itemId' => $clusterId,
            );
        } else {
            return array(
                'result' => false,
                'alert' => false,
                'confirm' => _('Остались незаполненные поля. Вы можете продолжить заполнение анкеты и вернуться к ним позже. Продолжить?'),
                'progress' => $this->_getItemProgress($clusterId),
                'itemId' => $clusterId,
            );
        }
    }

    public function _saveMemoResults($memos)
    {
        $model = $this->_persistentModel->getModel();
        $memoResults = $this->_persistentModel->getMemoResults();
        foreach ($memos as $evaluationMemoId => $value) {
            // если уже есть в сессии - повторно не сохраняем
            if (!isset($memoResults[$evaluationMemoId])) {
                $this->getService('AtEvaluationMemoResult')->insert(array(
                    'evaluation_memo_id' => $evaluationMemoId,
                    'session_event_id' => $model['event']->session_event_id,
                    'value' => $value,
                ));
            } elseif ($value != $results[$evaluationMemoId]) {
                $this->getService('AtEvaluationMemoResult')->updateWhere(array(
                    'value' => $value,
                ), $this->getService('AtEvaluationMemoResult')->quoteInto(array(
                    'evaluation_memo_id = ? AND ',
                    'session_event_id = ?'
                ), array(
                    $evaluationMemoId,
                    $model['event']->session_event_id
                )));
            }
        }
        return true;
    }

    public function _getTotalResults()
    {
        $totalResults = $this->_saveTotalResults();
        return $totalResults;
    }

    public function _isFinalizeable($totalResults)
    {
        $finalizeable = true;
        foreach ($totalResults as $result) {
            if ($result['indicators_status'] != HM_At_Evaluation_Results_ResultsModel::INDICATORS_STATUS_FINISHED) {
                $finalizeable = false;
                break;
            }
        }
        return $finalizeable;
    }

    // сохранить и подготовить к выводу на экран
    protected function _saveTotalResults()
    {
        $totalResults = array();
        $model = $this->_persistentModel->getModel();
        $criterionIndicatorIds = $model['index'];
        $results = $this->_persistentModel->getResults();

        if ($model['options']['competenceUseIndicators']) {
            $totalResults = $this->getService('AtEvaluationIndicator')->saveTotalResults($model['event'], $model['scale']->scale_id, $criterionIndicatorIds, $results);
            foreach ($totalResults as $criterionId => &$result) {
                $result['text'] = HM_Scale_Converter::getInstance()->value2text($result['value'], $model['scale']->scale_id);
                $result['criterion'] = $model['criteria'][$criterionId]->name;
            }
        } else {
            foreach ($results as $clusterId => $clusterResults) {
                if (is_array($clusterResults)) {
                    $scaleValues = array();
                    // после сортировке по возрастанию value у массива сбиты индексы
                    foreach ($model['scaleValues'] as $scaleValue) {
                        $scaleValues[$scaleValue->value_id] = $scaleValue;
                    }
                    foreach ($clusterResults as $criterionId => $valueId) {
                        $scaleValue = $scaleValues[$valueId];
                        $totalResults[$criterionId]['value'] = $scaleValue->value;
                        $totalResults[$criterionId]['text'] = $scaleValue->text;
                        $totalResults[$criterionId]['criterion'] = $model['criteria'][$criterionId]->name;
                        $totalResults[$criterionId]['indicators_status'] = HM_At_Evaluation_Results_ResultsModel::INDICATORS_STATUS_FINISHED;
                    }
                }
            }
        }

        return $totalResults;
    }

    public function _finalize()
    {
        $model = $this->_persistentModel->getModel();
        // если всего один эксперт оценивает компетенции - сразу выставляем итоговые оценки
        if (!count($this->getService('AtSessionEvent')->getSameMethodEvents($model['event']))) {
            $this->getService('AtSessionUserCriterionValue')->setCriteriaValues($model['event']->session_user_id);
        }
        $this->getService('AtSessionEvent')->updateStatus($model['event']->session_event_id, HM_At_Session_Event_EventModel::STATUS_COMPLETED);
    }
    
    public function _isExecutable()
    {
        if (!count($this->_persistentModel->getItems())) {
            return _('Не указаны компетенции');
        }     
           
        return true;
    }

    public function saveCommentsAction()
    {
        if ($this->isAjaxRequest() && isset($_POST['strength']) && isset($_POST['need2progress']) && isset($_POST['session_event_id'])) {
            $collection = $this->getService('AtEvaluationMemoResult')->fetchAll(
                $this->getService('AtEvaluationMemoResult')->quoteInto(
                    array(
                        ' evaluation_memo_id IN (?) AND ',
                        ' session_event_id   = ? '
                    ),
                    array(
                        array(1, 2),
                        $_POST['session_event_id']
                    )
                )
            );

            if (count($collection)) {
                $this->getService('AtEvaluationMemoResult')->updateWhere(
                    array(
                        'value' => $_POST['strength']
                    ),
                    array(
                        'evaluation_memo_id = ?' => 1,
                        'session_event_id   = ?' => $_POST['session_event_id']
                    )
                );

                $this->getService('AtEvaluationMemoResult')->updateWhere(
                    array(
                        'value' => $_POST['need2progress']
                    ),
                    array(
                        'evaluation_memo_id = ?' => 2,
                        'session_event_id   = ?' => $_POST['session_event_id']
                    )
                );
            } else {
                $this->getService('AtEvaluationMemoResult')->insert(array(
                    'evaluation_memo_id' => 1,
                    'session_event_id' => $_POST['session_event_id'],
                    'value' => $_POST['strength'],
                ));

                $this->getService('AtEvaluationMemoResult')->insert(array(
                    'evaluation_memo_id' => 2,
                    'session_event_id' => $_POST['session_event_id'],
                    'value' => $_POST['need2progress'],
                ));
            }
        }
    }
}
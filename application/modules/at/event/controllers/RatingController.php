<?php
/**
 * Реализация многостраничной анкеты парных сравнений на основе Quest
 */

class Event_RatingController extends HM_Controller_Action_Multipage implements HM_Multipage_Controller_Interface
{
    const NAMESPACE_MULTIPAGE = 'rating-multipage';
    
    protected $_sessionId;

    public function init()
    {
        $this->setNamespaceMultipage(self::NAMESPACE_MULTIPAGE);
        
        parent::init();
        if (isset($this->_persistentModel)) {
            $model = $this->_persistentModel->getModel();
            $this->_sessionId = $model['event']->session_id;
            $this->_helper->viewRenderer->setNeverController(true);
            $this->view->addScriptPath(APPLICATION_PATH . "/views/methods/rating/");
        }
    }

    public function _redirectToIndex($msg = '', $type = HM_Notification_NotificationModel::TYPE_SUCCESS){
        if (!empty($msg)) {
            $this->_flashMessenger->addMessage(array(
                'type'    => $type,
                'message' => $msg
            ));
        }
        if (!$this->isAjaxRequest()) {
            $action = $this->getService('Acl')->inheritsRole($this->getService('User')->getCurrentUserRole(), HM_Role_Abstract_RoleModel::ROLE_ENDUSER) ? 'my' : 'list';
            $this->_redirector->gotoSimple($action, 'event', 'session', array('session_id' => $this->_sessionId));
        }
    }

    public function _redirectToMultipage($msg = ''){
        if (!empty($msg)) {
            $this->_flashMessenger->addMessage(array(
                'type'    => HM_Notification_NotificationModel::TYPE_CRIT,
                'message' => $msg
            ));
        }
        if (!$this->isAjaxRequest()) {
            $this->_redirector->gotoSimple('view', 'rating', 'event', array('session_event_id' => $this->_getMultipageId()));
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
        return array('module' => 'event', 'controller' => 'rating');
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
        $collection = $this->getService('AtSessionEvent')->fetchAllDependence('SessionPair', array('session_event_id = ?' => $sessionEventId)); 
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

        $filled = 0;
        foreach ($model['index'][$clusterId] as $criterionId) {
            $filled += count($results[$clusterId][$criterionId]);
        }
        $total = count($model['index'][$clusterId]) * count($model['pairs']);

        return $total? round(100 * $filled/$total) : 0;
    }

    public function _setInfo()
    {
        return false;
    }

    public function _saveResults($clusterId, $results)
    {
        $result = false;
        $model = $this->_persistentModel->getModel();

        $result = $this->getService('AtSessionPairResult')->saveResults($model['event'], $model['index'][$clusterId], $model['pairs'], $results);

        if($finalize) //#17713 - не сохранялись результаты при безкластерной форме анкетирования (at_session_pair_ratings), т.к. не было страницы результатов, на которой оно сохранялось
            $this->_saveTotalResults();

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
        return false; // no memos
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
            // some check
        }
        return $finalizeable;
    }

    // сохранить и подготовить к выводу на экран
    protected function _saveTotalResults()
    {
        $totalResults = $profiledResults = array();
        $model = $this->_persistentModel->getModel();
        $criterionIndicatorIds = $model['index'];
        $results = $this->_persistentModel->getResults();
        $sessionId = $model['event']->session_id;
        
        $parentSoid = 0;
        if ($respondent = $this->getService('User')->getOne($this->getService('User')->findDependence('Position', $model['event']->respondent_id))) {
            if (count($respondent->positions)) {
                $parentSoid = $respondent->positions->current()->owner_soid;
            } 
        }
        
        // plainify
        foreach ($results as $clusterId => $criteriaResults) {
            if (is_array($profiledResults) && is_array($criteriaResults)) {
                $profiledResults = $profiledResults + $criteriaResults;
            }
        }

        if (count($model['users'])) {
            $this->getService('AtSessionPairRating')->deleteBy(array('session_id = ?' => $sessionId, 'user_id IN (?)' => array_keys($model['users'])));
        }
        $profiledResults = $this->getService('AtSessionPairRating')->profileResultsByCriterion($profiledResults, $model['users'], $model['pairs'], $model['criteria']);
        foreach ($profiledResults as $criterionId => $profiledResult) {
            
            $sessionUsersMap = $this->getService('AtSessionUser')->fetchAll(array('session_id = ?' => $sessionId))->getList('user_id', 'session_user_id');
            
            $ratings = $this->getService('AtSessionPairRating')->getRatings($profiledResult);
            foreach ($ratings as $data) {
                $data['parent_soid'] = $parentSoid;
                $data['criterion_id'] = $criterionId;
                $data['session_id'] = $sessionId;
                $data['session_user_id'] = $sessionUsersMap[$data['user_id']];
                $this->getService('AtSessionPairRating')->insert($data);
            }
            
            if ($criterionId == HM_At_Session_Pair_Rating_RatingModel::TOTAL) {
                foreach ($ratings as $data) {
                    $totalResults[] = $data + array(
                        'user' => $model['users'][$data['user_id']],
                        'status' => ($data['rating'] == HM_At_Session_Pair_Rating_RatingModel::RATING_NA) ? HM_At_Evaluation_Results_ResultsModel::PAIRS_STATUS_IN_PROGRESS : HM_At_Evaluation_Results_ResultsModel::PAIRS_STATUS_FINISHED,                    
                       );
                   }
            }
        }
        return $totalResults;
    }

    public function _finalize()
    {
        $model = $this->_persistentModel->getModel();
        $this->getService('AtSessionEvent')->updateStatus($model['event']->session_event_id, HM_At_Session_Event_EventModel::STATUS_COMPLETED);
    }
    
    public function _isExecutable()
    {
        return true;
    }
}
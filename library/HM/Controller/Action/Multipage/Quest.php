<?php
abstract class HM_Controller_Action_Multipage_Quest extends HM_Controller_Action implements HM_Multipage_Controller_Interface
{
    use HM_Controller_Action_Trait_Multipage;

    private $_model;

    public function init()
    {
        $this->questRestrict();

        $this->initMultipage();
        if (isset($this->_persistentModel)) {
            $this->_model = $model = $this->_persistentModel->getModel();
            $this->_helper->viewRenderer->setNeverController(true);
            $this->view->addScriptPath(APPLICATION_PATH . "/views/methods/quest/");
            $this->view->time_left = $this->getTimeLeft();
        }

        parent::init();
    }

    public function infoAction()
    {
        $currentRole = $this->getService('User')->getCurrentUserRole();
        $isManagerOrDean = $this->getService('Acl')->inheritsRole($currentRole, [HM_Role_Abstract_RoleModel::ROLE_DEAN, HM_Role_Abstract_RoleModel::ROLE_MANAGER]);

        $questId = $this->_getParam('quest_id');

        $quest = $this->getService('Quest')
            ->getOne($this->getService('Quest')
                ->findDependence(
                    array('Settings', 'Cluster', 'QuestionQuest'),
                    $questId
                ));

        if ($quest) {

            if($lessonId = $this->_getParam('lesson_id', 0)) {
                $lesson = $this->getService('Lesson')->getOne(
                    $this->getService('Lesson')->findDependence('Subject', $lessonId)
                );
                $this->view->replaceSidebar('subject', 'subject-lesson', [
                    'model' => $lesson,
                    'order' => 100, // после Subject
                ]);
            } else {
                if($isManagerOrDean) {
                    $this->view->addSidebar('test', [
                        'model' => $quest,
                    ]);
                }
            }

            HM_Quest_Settings_SettingsService::detectScope($quest);

            $url = $this->_getBaseUrl();
            $this->view->quest = $quest;

            $this->view->stopUrl = urldecode($this->_getParam('redirect_url', $_SERVER['HTTP_REFERER'] ? : '/'));
            $this->view->continueUrl = $this->view->url(array_merge($url, array('action' => 'start', 'advance' => 1, 'item_id' => null, 'redirect_url' => $this->view->fullUrlEncode($this->view->stopUrl))));
            /* //Откат #37639

                        $session = new Zend_Session_Namespace('preview-multipage');
                        if ($session && isset($session->persistentModel)) {
                            $base = ($_SERVER['HTTPS'] == 'on' ? 'https' : 'http') . '://' . $_SERVER['HTTP_HOST'];
            //Очень подозрительная обработка!
                            $subUrl = substr($this->view->stopUrl, strlen($base));
                            $session->persistentModel->setRedirectUrl($subUrl);
                        }
            */
            //чтобы попытка не засчиталась раньше времени
            $model = $this->_getPersistentModel(HM_Quest_Attempt_AttemptModel::MODE_ATTEMPT_OFF);
            $model->setupModel($quest);
            /* //Откат #37639
                        if ($session->getNamespace() == 'preview-multipage') {
                            $defaultSession = new Zend_Session_Namespace('default');
            //Очень подозрительная обработка!
                            $base = ($_SERVER['HTTPS'] == 'on' ? 'https' : 'http') . '://' . $_SERVER['HTTP_HOST'];
                            $subUrl = substr($this->view->stopUrl, strlen($base));
                            $defaultSession->quest_redirect_url = urlencode($subUrl);
                        }
            */
            $this->view->settings = $this->getService('Quest')->getSettingsReport($quest, $model);

            $this->_helper->viewRenderer->setNoRender();
            $this->view->addScriptPath(APPLICATION_PATH . "/views/methods/quest/");

            $where = $this->getService('LessonAssign')->quoteInto(
                array('SHEID = ?', ' AND MID = ?'),
                array($lessonId, $this->getService('User')->getCurrentUserId())
            );
            $assign = $this->getService('LessonAssign')->fetchAll($where)->current();

            $this->view->passed_proctoring = $assign->passed_proctoring;
            $this->view->has_proctoring = $lesson->has_proctoring;
            $this->view->isEnduser = $this->getService('User')->isEnduser();
            $this->view->lessonId = $lessonId;
            $this->view->SSID = $assign->SSID;
            $this->view->SHEID = $lesson->SHEID;
            $isTest = HM_Quest_QuestModel::TYPE_TEST == $quest->type;
            $isPoll = HM_Quest_QuestModel::TYPE_POLL == $quest->type;

            if($this->getService('User')->isEnduser() and $lesson->has_proctoring and ($isTest or $isPoll)) {
                $this->view->messages = HM_Proctoring_ProctoringService::getMessages();
            }

            echo $this->view->render('info.tpl');

        } else {
            $this->_redirectToError(_('Тест не найден'), HM_Notification_NotificationModel::TYPE_ERROR);
        }
    }

    /**
     * Возвращает время в секундах, которое осталось для выполнения теста,
     * в т.ч. отрицательное значение, если лимит по времени превышен.
     * Возвращает false, если не удалось выяснить оставшееся время.
     * @return bool|int
     */
    public function getTimeLeft()
    {
        $model = $this->_model;
        if ($this->_model) {
            $quest_limit_time = intval($model['quest']->limit_time); //в минутах
            if (!empty($model['attempt']['date_begin'])) {
                $attempt_date_begin = strtotime($model['attempt']['date_begin']);
            }
            if ($attempt_date_begin && $quest_limit_time > 0) {
                $end_time = $attempt_date_begin + $quest_limit_time * 60;
                $delta_time = $end_time - time();
                return $delta_time;
            }
        }
        return false;
    }

    public function _redirectToIndex($msg = '', $type = HM_Notification_NotificationModel::TYPE_SUCCESS, $redirectUrl = false)
    {
        if (!empty($msg)) {
            $this->_flashMessenger->addMessage(array(
                'type'    => $type,
                'message' => $msg
            ));
        }
//         $model = $this->_persistentModel->getModel();
        if (!$this->isAjaxRequest()) {
            if($redirectUrl) {
                $this->_redirector->gotoUrl($redirectUrl);
            } else {
                $this->_redirector->gotoSimple('index', 'list', 'quest');
            }

        }
    }

    public function getIndexUrl()
    {
        $router = $this->getFrontController()->getRouter();
        $params = array(
            'action'     => 'index',
            'controller' => 'list',
            'module'     => 'quest');
        return $router->assemble($params, 'default', true);

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
            if ($this->_persistentModel) {
                $this->_redirector->gotoUrl($this->_persistentModel->getRedirectUrl());
            } else {
                // это совсем нехорошая ситуация ,напрмер кнопка Back в браузере
                $this->_redirector->gotoSimple('index', 'index', 'default');
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
            $this->_redirector->gotoSimple('view', 'quest', 'quest', array('quest_id' => $this->_getMultipageId()));
        }
    }

    public function _getBaseUrl()
    {
        return array('module' => 'quest', 'controller' => 'quest');
    }

    public function _getMultipageId()
    {
        if (!$this->_persistentModel) {
            $this->_persistentModel = $this->_getPersistentModel();
        }
        $model = $this->_persistentModel->getModel();

        if (isset($model['quest']) && isset($model['quest']->quest_id)) {
            return $model['quest']->quest_id;
        }
        return null;
    }

    public function _isCurrentMultipage()
    {
        $questId = $this->_getParam('quest_id');
        $currentId = $this->_getMultipageId();

        if ($currentId && ($currentId == $questId)) {
            return true;
        }
        return false;
    }

    /* Создается объект, содержащий всю информацию для прохождения Quest'а
     * Объект _persistentModel кэшируется в сессии на всё время заполнения опросника
     * @see HM_Quest_Controller_Interface::_getPersistentModel()
     */
    public function _getPersistentModel($mode = null, $contextEventId = null, $contextEventType = null)
    {
        $questId = $this->_getParam('quest_id');
        $questionId = $this->_getParam('question_id');
        $collection = $this->getService('Quest')->findDependence(array('Settings', 'Cluster', 'QuestionQuest'), $questId);

        if (count($collection)) {
            $quest = $this->getService('Quest')->getOne($collection);

            if ($questionId) {
                $questionQuest = new HM_Collection(); //array();
                $questionQuest->setModelClass('HM_Model_Abstract');
                foreach ($quest->questionQuest as $question) {
                    if ($question->question_id == $questionId) {
                        $questionQuest->offsetSet(null, $question);
                        break;
                    }
                }
                $quest->questionQuest = $questionQuest;
            }

            HM_Quest_Settings_SettingsService::detectScope($quest);
            $data = array(
                'type' => $quest->type,
                'quest_id' => $quest->quest_id,
                'user_id' => $this->getService('User')->getCurrentUserId(),
                'status' => HM_Quest_Attempt_AttemptModel::STATUS_IN_PROGRESS, // @todo: или нам нужна отдельная таблица quest_results?
                'date_begin' => HM_Date::now()->toString('yyyy-MM-dd HH:mm:ss'),
            );

            if ($mode === null) {
                $mode = $quest->getAttemptMode();
            }

            switch ($mode) {
                case HM_Quest_Attempt_AttemptModel::MODE_ATTEMPT_OFF:
                    $attempt = HM_Quest_Attempt_AttemptModel::factory($data, 'HM_Quest_Attempt_AttemptModel');
                break;
                case HM_Quest_Attempt_AttemptModel::MODE_ATTEMPT_SINGLE:
                    $condition = array(
                        'quest_id = ?' => $data['quest_id'],
                        'user_id = ?' => $data['user_id'],
                    );
                    if ($contextEventId) {
                        $condition['context_event_id = ?'] = $contextEventId;
                    }
                    if ($contextEventType) {
                        $condition['context_type = ?'] = $contextEventType;
                    }
                    if ($condition['user_id = ?'] && ($attempt = $this->getService('QuestAttempt')->getOne($this->getService('QuestAttempt')->fetchAll($condition)))) {
                        $data = array_merge($attempt->getData(), $data);
                        $attempt = $this->getService('QuestAttempt')->update($data);
                    } else {
                        $attempt = $this->getService('QuestAttempt')->insert($data);
                    }
                break;
                case HM_Quest_Attempt_AttemptModel::MODE_ATTEMPT_MULTIPLE:
                    $attempt = $this->getService('QuestAttempt')->insert($data);
                break;
            }

            $attempt->setQuest($quest);
            $attempt->setMode($mode);

            return $attempt;
        }
    }

    public function _getProgressTitle($clusterId)
    {
        $model = $this->_persistentModel->getModel();
        if (!isset($model['clusters'][$clusterId])) {
            return '';
        }
        return $model['clusters'][$clusterId]->name;
    }

    public function _getItemProgress($clusterId)
    {
        $model = $this->_persistentModel->getModel();
        $results = $this->_persistentModel->getResults();

        $total = isset($model['index'][$clusterId]) ? count($model['index'][$clusterId]) : 0;
        $filled = isset($results[$clusterId]) ? count($results[$clusterId]) : 0;

        return $total? round(100 * $filled/$total) : 0;
    }

    public function _setInfo()
    {
        return false;
    }

    public function _saveResults($clusterId, $results, $comment = '')
    {
        $result = false;
        $model = $this->_persistentModel->getModel();
        $settings = $model['quest']->getSettings();

        if ($questAttemptId = isset($model['attempt']['attempt_id']) ? $model['attempt']['attempt_id'] : false) {
            if ($model['quest']->isTimeElapsed($model['attempt']['date_begin']) && !($this->getRequest()->getParam('timestop', 0) == 1)) {
                $this->_persistentModel->restoreResults($clusterId);
                return array(
                    'result' => false,
                    'alert' => false,
                    'confirm' => _('Истекло время выполнения; сохранены только ответы, полученные до истечения времени. Продолжить?'),
                    'progress' => $this->_getItemProgress($clusterId),
                    'itemId' => $clusterId,
                );
            }
        }
        $result = $this->getService('QuestQuestionResult')->saveResults($model['quest'], $questAttemptId, $model['index'][$clusterId], $results, $comment);

        $return = array(
            'result'   => true,
            'alert'    => false,
            'confirm'  => false,
            'progress' => $this->_getItemProgress($clusterId),
            'itemId'   => $clusterId,
        );


        $modeTestPage = $settings->mode_test_page;
        $confirms = array(
            0 => array(
                'close' => _('Вы ответили не на все вопросы. Действительно желаете завершить?'),
                'next' =>  _('Остались незаполненные поля. В случае продолжения Вы не сможете вернуться к этому вопросу. Действительно хотите продолжить?')
            ),

            1 => array(
                'close' => _('Вы ответили не на все вопросы. Действительно желаете завершить?'),
                'next' =>  _('Остались незаполненные поля. Вы действительно хотите продолжить?')
            ),

        );


        if ($this->_getParam('finalize') || $this->_getParam('stop')) {
            $totalResults = $this->_getTotalResults();
            foreach ($totalResults as $clusterResults) {
                if ($clusterResults['cluster_status'] != HM_Quest_Question_Result_ResultModel::CLUSTER_STATUS_FINISHED) {
                    $return['result']  = false;
                    $return['confirm'] = $confirms[$modeTestPage]['close'];
                    break;
                }
            }
        } elseif (!$result) {
            $return['result']  = false;
            $return['confirm'] = $confirms[$modeTestPage]['next'];
            $return['next'] = true;
        }

        return $return;
    }

    public function _saveMemoResults($memos)
    {
        return true;
    }

    public function getSettigs() {
        return $this->_settings;
    }
    public function _getTotalResults()
    {
        $totalResults = array();
        if ($this->_persistentModel) {
            $model = $this->_persistentModel->getModel();
            $results = $this->_persistentModel->getResults();

            foreach ($model['clusters'] as $clusterId => $cluster) {
                $clusterResults = $results[$clusterId];
                $totalResults[$clusterId] = array(
                    'cluster_status' => HM_Quest_Question_Result_ResultModel::CLUSTER_STATUS_IN_PROGRESS,
                    'cluster_name' => $model['clusters'][$clusterId]->name,
                );
                if (is_array($clusterResults)) {
                    if (count($clusterResults) == count($model['index'][$clusterId])) {
                        $totalResults[$clusterId]['cluster_status'] = HM_Quest_Question_Result_ResultModel::CLUSTER_STATUS_FINISHED;
                    }
                }
            }
        }
        return $totalResults;
    }

    public function _isExecutable()
    {
        if (!count($this->_persistentModel->getItems())) {
            $questType = $this->_persistentModel->getModel()['quest']->type;
            if(HM_Quest_QuestModel::TYPE_TEST == $questType) {
                return _('Тест не содержит ни одного вопроса');
            } else {
                return _('Опрос не содержит ни одного вопроса');
            }
//@D            return _('Оценочная форма не содержит ни одного вопроса');
        }

        // @todo: здесь и другие проверки: кол-во попыток..

        return true;
    }

    public function _isFinalizeable($totalResults)
    {
        $model = $this->_persistentModel->getModel();
        // тесты, в отличие от form и psycho всегда Finalizeable и никогда не Suspendable
        if ($model['quest']->type == HM_Quest_QuestModel::TYPE_TEST) return true;

        $finalizeable = true;
        foreach ($totalResults as $result) {
            if ($result['cluster_status'] != HM_Quest_Question_Result_ResultModel::CLUSTER_STATUS_FINISHED) {
                $finalizeable = false;
                break;
            }
        }
        return $finalizeable;
    }

    public function _finalize()
    {
        $model = $this->_persistentModel->getModel();
        $results = $this->_persistentModel->getResults();

        // при досрочном/принудительном завершении нужно сохранить нулевые результаты
        // чтоб они соотв. образом испортили статистику;
        $notAnsweredQuestionIds = array();
        foreach ($model['index'] as $itemId => $questionIds) {
            foreach ($questionIds as $questionId) {
                if (!isset($results[$itemId][$questionId])) {
                    $notAnsweredQuestionIds[] = $questionId;
                    $notAnsweredResults[$questionId] = 0;
                }
            }
        }
        if (count($notAnsweredQuestionIds)) {
            $this->getService('QuestQuestionResult')->saveResults($model['quest'], $model['attempt']['attempt_id'], $notAnsweredQuestionIds, $notAnsweredResults);
        }

        $this->getService('QuestAttemptCluster')->saveAttemptResults($model['attempt']['attempt_id']);

        $questAttempt = $this->getService('QuestAttempt')->updateStatus($model['attempt']['attempt_id'], HM_Quest_Attempt_AttemptModel::STATUS_COMPLETED);
        $contextModel = $this->_persistentModel->getContextModel();

        if (isset($contextModel)) {
            if(is_subclass_of($contextModel, 'HM_Meeting_MeetingModel')) {
                $this->_helper->QuestContextProject($contextModel)->finalize($questAttempt);
            } elseif (is_subclass_of($contextModel, 'HM_Lesson_LessonModel')) {
                $this->_helper->QuestContextSubject($contextModel)->finalize($questAttempt);
            } elseif(is_subclass_of($contextModel, 'HM_Subject_Feedback_FeedbackModel')) {
                $this->_helper->QuestContextFeedback($contextModel)->finalize($questAttempt);
            } elseif (is_subclass_of($contextModel, 'HM_Feedback_Users_UsersModel')) {
                $this->_helper->QuestContextFeedbackUser($contextModel)->finalize($questAttempt);
            } elseif (is_subclass_of($contextModel, 'HM_At_Session_Event_EventModel')) {
                $this->_helper->QuestContextEvent($contextModel)->finalize($questAttempt);
            }
        }
    }

    public function contextHelperAction()
    {
        if ($action = $this->_getParam('context-helper-action')) {
            $this->_helper->viewRenderer->setNoRender();
            $contextModel = $this->_persistentModel->getContextModel();
            if ($this->isAjaxRequest() && isset($contextModel)) {
                switch (true) {
                    case $contextModel instanceof HM_Lesson_LessonModel:
                        return $this->_helper->QuestContextSubject($contextModel)->$action();
                    case $contextModel instanceof HM_Subject_Feedback_FeedbackModel:
                        return $this->_helper->QuestContextFeedback($contextModel)->$action();
                    case $contextModel instanceof HM_At_Session_Event_EventModel:
                        return $this->_helper->QuestContextEvent($contextModel)->$action();
                }
            }

            if(isset($contextModel))
                if($contextModel instanceof HM_Lesson_LessonModel)
                    echo $this->_helper->QuestContextSubject($contextModel)->$action();
                else
                if($contextModel instanceof HM_Subject_Feedback_FeedbackModel)
                    $this->_helper->QuestContextFeedback($contextModel)->$action();
                else
                if($contextModel instanceof HM_At_Session_Event_EventModel)
                    echo $this->_helper->QuestContextEvent($contextModel)->$action();
        }
    }
}
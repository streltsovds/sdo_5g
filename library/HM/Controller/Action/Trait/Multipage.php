<?php
/**
 * Базовый класс многостраничного опроса
 * Вся специфичная логика - в наследниках
 */
trait HM_Controller_Action_Trait_Multipage
{
    protected $namespaceMultipage = '';

    protected $_persistentModel;

    public function initMultipage()
    {
        if ($this->namespaceMultipage == ''){
            $this->namespaceMultipage = HM_Controller_Action::NAMESPACE_MULTIPAGE;
        }

//        $this->_helper->ContextSwitch()
//            ->setAutoJsonSerialization(true)$this->_helper->layout->setLayout('/path/to/your/layout_script');
//            ->addActionContexts(array(
//                'start' => 'json',
//                'view' => 'json',
//                'load' => 'json',
//                'stop' => 'json',
//                'finalize' => 'json',
//                'save' => 'json',
//                'results' => 'json',
//            ))
//            ->initContext('json');

//        $this->_helper->layout()->disableLayout();

        $this->_initMultipageView();
    }

    protected function _initMultipageView()
    {
        $this->_helper->layout->setLayout('multipage');

        $session = new Zend_Session_Namespace($this->namespaceMultipage);
        if (isset($session->persistentModel)) {
            $this->_persistentModel = $session->persistentModel;

            $this->view->model = $this->_persistentModel->getModel();
            $this->view->results = $this->_persistentModel->getResults();
            $this->view->comments = $this->_persistentModel->getComments();
            $this->view->memoResults = $this->_persistentModel->getMemoResults();
            $this->view->questId = $this->_getMultipageId();
        }

        // @todo: move to HM_Controller_Action_Multipage_Quest?
        $this->view->headScript()->appendFile($this->view->serverUrl('/js/content-modules/quest.js') );
        return $this;
    }

    public function setNamespaceMultipage($namespace)
    {
        $this->namespaceMultipage = $namespace;
    }

    public function startAction()
    {
        // запуск второго теста параллельно
        if (
            $this->_isStarted() and
            $this instanceof HM_Controller_Action_Multipage_Quest
        ) {
            // скопировано из danone, метод completeLessonsAttemptsWithElapsedTime не перенесён
//            $this->getService('QuestAttempt')->completeLessonsAttemptsWithElapsedTime();
            $this->_finalize();
            $this->_destroyModel();
            unset($this->_persistentModel);
        }


        if (!$this->_isStarted() || $this instanceof Quest_PreviewController) {
            if (
                ($persistentModel = $this->_getPersistentModel()) &&
                ($persistentModel instanceof HM_Multipage_PersistentModel_Interface)
            ) {
                $this->_persistentModel = $persistentModel;
                $this->_persistentModel->setupModel();
/*
                $redirectUrl = false;
                $defaultSession = new Zend_Session_Namespace('default');
                if ($defaultSession && isset($defaultSession->quest_redirect_url)) {
                    $redirectUrl = $defaultSession->quest_redirect_url;
                }
*/
                $url = $this->_getParam('redirect_url', $_SERVER['HTTP_REFERER']);
                $this->_persistentModel->setRedirectUrl(urldecode($url));

                if (($msg = $this->_isExecutable()) !== true) {
                    $this->_destroyModel();
                    $this->_redirectToError($msg, HM_Notification_NotificationModel::TYPE_CRIT);
                }

                $this->_setCurrentItem();

                $session = new Zend_Session_Namespace($this->namespaceMultipage);
                $session->persistentModel = $this->_persistentModel;

            } else {
                // что-то очень неправильно
                $this->_redirectToIndex(_('Произошла ошибка при запуске формы'), HM_Notification_NotificationModel::TYPE_ERROR);
            }
        } elseif (!$this->_isCurrentMultipage()) {
            // тоже неправильно, не сработал onUnload
            $this->_redirectToMultipage(_('Для продолжения необходимо закончить заполнение предыдущей формы.'));
        }
        $this->_redirector->gotoSimple('view');
    }

    public function viewAction()
    {
        $this->view->lessonId = $this->_persistentModel->getContextModel()->SHEID;
        //$this->view->stopUrl = $this->view->url(array_merge($url, array('action' => 'stop')));
        //$this->view->loadUrl = $this->view->url(array_merge($url, array('action' => 'load')));
        //$this->view->saveUrl = $this->view->url(array_merge($url, array('action' => 'save')));
        $this->view->hmVue()->setMainLayoutVisibility(false);
        if (!$this->_isStarted()) {
            $this->_redirectToError(_('Произошла ошибка при запуске формы'), HM_Notification_NotificationModel::TYPE_ERROR);
        }


        $this->view->resultsUrl = $this->view->url(array_merge($this->_getBaseUrl(), array('action' => 'results', 'item_id' => null)));
        $this->view->finalizeUrl = $this->view->url(array_merge($this->_getBaseUrl(), array('action' => 'finalize', 'item_id' => null)));

        $this->view->info = $this->_setInfo();
        $this->view->progress = $this->_getProgress();

        $this->view->module = $this->getRequest()->getModuleName();
        $this->view->controller = $this->getRequest()->getControllerName();

        $this->view->layoutContentFullWidth = true;
    }

    public function loadAction()
    {
        if ($this->_isStarted()) {
            if ($this->isAjaxRequest()) {
                $this->_helper->getHelper('layout')->disableLayout();
                Zend_Controller_Front::getInstance()->unregisterPlugin('HM_Controller_Plugin_Unmanaged');
                //$this->getResponse()->setHeader('Content-type', 'text/html; charset='.Zend_Registry::get('config')->charset);
            }
            $itemId = $this->_getParam('item_id');
            if (!$itemId) {
                $itemId = $this->_persistentModel->getCurrentItem();
            } else {
                $this->_setCurrentItem($itemId);
            }
            if ($this->_hasParam('item_id') && !$this->isAjaxRequest()) {
                $this->_redirector->goToSimple('view');
            } else {
                if ($this->isAjaxRequest()) {

                    if(method_exists($this->_persistentModel, 'getContextModel')) {
                        $contextModel = $this->_persistentModel->getContextModel();
                        $action = 'info';
                        if (isset($contextModel)) {
                            switch (true) {
                                case $contextModel instanceof HM_Lesson_LessonModel:
                                    $this->view->context = $this->_helper->QuestContextSubject($contextModel)->$action();
                                    break;
                                case $contextModel instanceof HM_Subject_Feedback_FeedbackModel:
                                    $this->view->context = $this->_helper->QuestContextFeedback($contextModel)->$action();
                                    break;
                                case $contextModel instanceof HM_At_Session_Event_Method_TestModel:
                                case $contextModel instanceof HM_At_Session_Event_EventModel:
                                    $this->view->context = $this->_helper->QuestContextEvent($contextModel)->$action();

//Костыль! Иначе тест падает! Потом облагородим...
$this->view->context['questionsCount'] = 1000;
$this->view->context['attempts'] = "1000/1000"; //осталось/лимит
                                    break;
                            }
                        }
                    }
                    $this->view->itemId = $itemId;
                    $this->view->saveUrl = $this->view->url(array_merge($this->_getBaseUrl(), array('action' => 'save', 'item_id' => null)));
                    $this->view->resultsUrl = $this->view->url(array_merge($this->_getBaseUrl(), array('action' => 'results', 'item_id' => null)));
                    $this->view->finalizeUrl = $this->view->url(array_merge($this->_getBaseUrl(), array('action' => 'finalize', 'item_id' => null)));
                    $this->view->info = $this->_setInfo();
                    $this->view->progress = array_values($this->_getProgress());

                    // Добавляем настройки в view
                    if(isset($this->view->model['quest'])) {
                        $this->view->settings = $this->view->model['quest']->getSettings();
                    }

//                    $this->sendAsJsonViaAjax($this->view);
                } else {
                    $this->view->itemId = $itemId;
                    $this->view->saveUrl = $this->view->url(array_merge($this->_getBaseUrl(), array('action' => 'save', 'item_id' => null)));
                    $this->view->resultsUrl = $this->view->url(array_merge($this->_getBaseUrl(), array('action' => 'results', 'item_id' => null)));
                    $this->view->navPanel = $this->_getNavPanel();
                }

            }
        } else {
            $this->_redirectToIndex(_('Для продолжения заполнения формы необходимо авторизоваться'), HM_Notification_NotificationModel::TYPE_ERROR);
        }

    }

    public function stopAction()
    {
        $redirectUrl = urldecode($this->_getParam('redirect'));

        if ($this->_getParam('exit') ) {
            $detailsUrl = urldecode($this->_getParam('details'));

            if ($detailsUrl) {
                $this->_redirector->gotoUrl($detailsUrl);
            } else {
                $this->_redirectToIndex('', HM_Notification_NotificationModel::TYPE_NOTICE, $redirectUrl);
            }
        } else {
            if ($this->_isStarted()) {
                $this->_destroyModel();
            }
            $this->_redirectToIndex(_('Заполнение формы прервано'), HM_Notification_NotificationModel::TYPE_NOTICE, $redirectUrl);
        }
    }

    public function finalizeAction()
    {
        $this->getService('AtEvaluationMemoResult')->insert(array(
            'evaluation_memo_id' => 1,
            'session_event_id' => $this->_getParam('session_event_id'),
            'value' => $this->_getParam('strengths'),
        ));

        $this->getService('AtEvaluationMemoResult')->insert(array(
            'evaluation_memo_id' => 2,
            'session_event_id' => $this->_getParam('session_event_id'),
            'value' => $this->_getParam('need2progress'),
        ));

        if ($this->_isStarted()) {
            $model = $this->_persistentModel->getModel();

            $isQuestAttempt = ($model instanceof HM_Quest_Attempt_Type_Abstract);

            if ($model['attempt']['attempt_id'] && (
                    !$isQuestAttempt ||
                    ($model['attempt']['status'] == HM_Quest_Attempt_AttemptModel::STATUS_IN_PROGRESS && $isQuestAttempt)
                )
            ) {
                $this->_finalize();

                //мы не уничтожваем модель, значит надо записать в сессию, что попытка сохранена
                $this->_persistentModel->status   = HM_Quest_Attempt_AttemptModel::STATUS_COMPLETED;
                $this->_persistentModel->date_end = date('Y-m-d H:i:s');
                $session = new Zend_Session_Namespace($this->namespaceMultipage);
                $session->persistentModel = $this->_persistentModel;
            }

            $this->view->timeover = $this->_getParam('timeover', 0);
            $config = Zend_Registry::get('config');

            $redirectUrl = $this->_persistentModel->getRedirectUrl();
            $redirectUrl = $this->view->fullUrlEncode($redirectUrl);

            if (
                $model['quest']->type == HM_Quest_QuestModel::TYPE_TEST or
                (in_array($model['quest']->type, [HM_Quest_QuestModel::TYPE_POLL, HM_Quest_QuestModel::TYPE_PSYCHO]) and $config->poll->vue_enable)
            ) {
                $this->view->quest = $model['quest'];
                $this->view->results = $this->getService('QuestQuestionResult')->reportResults($this->_persistentModel);

                $url = $this->_getBaseUrl();
                $this->view->stopUrl = $this->view->url(array_merge($url, array('action' => 'stop', 'item_id' => null, 'exit' => 1, 'redirect' => $redirectUrl)));

                if ($model['attempt']['attempt_id'] && $this->view->quest->show_log) {

                    $detailsUrl   = $this->view->url(array(
                        'module'     => 'quest',
                        'controller' => 'report',
                        'action'     => 'attempt',
                        'attempt_id' => $model['attempt']['attempt_id'],
                        'redirect' => $redirectUrl
                    ), null, true);

                    $this->view->detailsUrl = $this->view->url(array_merge($url, array('action' => 'stop', 'item_id' => null, 'exit' => 1, 'details' => urlencode($detailsUrl))));
                }
                $this->_destroyModel();
            } else{
                $this->_destroyModel();
                $this->_redirectToIndex(_('Заполнение формы завершено'), HM_Notification_NotificationModel::TYPE_SUCCESS, $redirectUrl);
            }
        } else {
            $this->_redirectToError(_('Заполнение формы уже завершено'), HM_Notification_NotificationModel::TYPE_ERROR);
        }
    }

    public function saveAction()
    {
        $result = false;

        // это была нехорошая идея - ввести параметр real_item_id
        // но оставляем для совместимости с danone
        $itemId = $this->_getParam('real_item_id', $this->_getParam('item_id'));

        $results = $this->_getParamWithFreeVariant('results');
        $comment = $this->_getParam('comment','');
        $memos = $this->_getParam('memos');

        if ($this->_persistentModel) {
            $this->_persistentModel->setResults($itemId, $results);
            $this->_persistentModel->setComments($itemId, $comment);
            $result = $this->_saveResults($itemId, $results, $comment);
            $memoResult = $this->_saveMemoResults($memos);
            $this->_persistentModel->setMemoResults($memos);
        }

        exit (HM_Json::encodeErrorSkip($result)); // $memoResult пока не учитываем, проверим потом при финализации
    }

    public function resultsAction()
    {
        if (!$this->_isStarted()) {
            $this->_redirectToError(_('Произошла ошибка при запуске формы'), HM_Notification_NotificationModel::TYPE_ERROR);
        }

        $url = $this->_getBaseUrl();
        $this->view->stopUrl = $this->view->url(array_merge($url, array('action' => 'stop', 'item_id' => null)));
        $this->view->continueUrl = $this->view->url(array_merge($url, array('action' => 'view', 'item_id' => null)));
        $this->view->finalizeUrl = $this->view->url(array_merge($url, array('action' => 'finalize', 'item_id' => null)));

        $totalResults = $this->_getTotalResults();
        $this->view->suspendable = $this->_isSuspendable();
        $this->view->finalizeable = $this->_isFinalizeable($totalResults);
        $this->view->totalResults = $totalResults;

        $this->view->module = $this->getRequest()->getModuleName();
        $this->view->controller = $this->getRequest()->getControllerName();
        $memoCollection = ($this->_persistentModel->session_event_id)
            ? $this->getService('AtEvaluationMemoResult')->fetchAll(array('session_event_id =?' => $this->_persistentModel->session_event_id))->asArray()
            : array();

        $memoResults = array();

        if (count($memoCollection)) {
            foreach ($memoCollection as $key => $memo) {
                $memoResults[$key] = $memo['value'];
            }
        } else {
            $memoResults = array(
                0 => '',
                1 => ''
            );
        }

        $this->view->memoResults = $memoResults;
    }

    public function getContext()
    {
        $contextModel = $this->_persistentModel->getContextModel();
        if (is_subclass_of($contextModel, 'HM_Lesson_LessonModel')) {
            return HM_Quest_Attempt_AttemptModel::CONTEXT_TYPE_ELEARNING;
        }

        return HM_Quest_Attempt_AttemptModel::CONTEXT_TYPE_NONE;
    }

    public function _destroyModel()
    {
        $session = new Zend_Session_Namespace($this->namespaceMultipage);
        unset($session->persistentModel);
        // unset($this->_persistentModel);
        unset($_SESSION[$this->namespaceMultipage]['persistentModel']);
    }

    public function _getProgress()
    {
        $progress = array();
        foreach ($this->_persistentModel->getItems() as $itemId) {
            $progress[] = array(
                'current' => ($this->_persistentModel->getCurrentItem() == $itemId),
                'itemId' => $itemId,
                'name' => $this->_getProgressTitle($itemId),
                'itemProgress' => $this->_getItemProgress($itemId),
                'url' => $this->view->url(array_merge($this->_getBaseUrl(), array('action' => 'load', 'item_id' => $itemId))),
            );
        }
        return $progress;
    }

    protected function _setCurrentItem($itemId = false)
    {
        if ($itemId) { // jump over the progressbar
            $this->_persistentModel->setCurrentItem($itemId);
        } else {
            $ids = $this->_persistentModel->getItems();
            if (!$this->_persistentModel->getCurrentItem()) { // first time launch
                $this->_persistentModel->setCurrentItem(array_shift($ids));
            } else { // go next
                $index = array_search($this->_persistentModel->getCurrentItem(), $ids);
                $head = array_slice($ids, 0, $index);
                $tail = array_slice($ids, $index);
                foreach (array($tail, $head) as $search) {
                    while (count($search)) { // search for first unanswered
                        $id = array_shift($search);
                        if (!isset($this->populate[$id])) {
                            $this->_persistentModel->setCurrentItem($id);
                            return;
                        }
                    }                 }
                $this->_persistentModel->setCurrentItem(0);
            }
        }
    }

    public function getItem($itemId)
    {
        if (in_array($itemId, $items = $this->_persistentModel->getItems())) {
            $item = $items->find($itemId)->current();
            if (!empty($this->populate[$itemId])) {
                $item->populate = $this->populate[$itemId];
            }
            return $item;
        }
        return false;
    }

    public function _getNavPanel()
    {
        $return = array(
            'test'   => null,
            'prevId' => null,
            'prev'   => null,
            'nextId' => null,
            'next'   => null,
            'end'    => null,
        );
        if ($itemId = $this->_persistentModel->getCurrentItem()) {
            $model = $this->_persistentModel->getModel();
            $modelType = $model['quest']->type;
            $ids = $this->_persistentModel->getItems();
            $key = array_search($itemId, $ids);
            $url = $this->_getBaseUrl();
            if (count($ids) > 1) {

                if (isset($ids[$key-1])) {
                    $return['prevId'] = $ids[$key-1];
                    $return['prev']   = $this->view->url(array_merge($url, array('action' => 'load', 'item_id' => $return['prevId'])));
                }
                if (isset($ids[$key+1])) {
                    $return['nextId'] = $ids[$key+1];
                    $return['next']   = $this->view->url(array_merge($url, array('action' => 'load', 'item_id' => $return['nextId'])));
                } else {

                    if ($model['mode'] == HM_Quest_Attempt_AttemptModel::MODE_ATTEMPT_SINGLE) {
                        // для оценочной формы в оценке, подборе и т.п. - нужно показать список cluster'ов и кнопку "Готово"
                        $return['finalize'] = $this->view->url(array_merge($url, array('action' => 'results', 'item_id' => null)));
                    } else {
                        // для теста не спрашивам - готово/неготово - автоматически финализируем
                        $return['finalize']   = $this->view->url(array_merge($url, array('action' => 'finalize', 'item_id' => null)));
                    }
                }
            } else {
                $redirectUrl = urlencode($this->_persistentModel->getRedirectUrl());
                // вырожденный Multipage из одного page
                $return['stop'] = $this->view->url(array('action' => 'stop', 'redirect' => $redirectUrl));
                $return['finalize'] = $this->view->url(array('action' => 'finalize', 'redirect' => $redirectUrl));
            }
        }
        return $return;
    }

    protected function _isStarted()
    {
        return isset($this->_persistentModel);
    }

    protected function _isSuspendable()
    {
        return true;
    }
}

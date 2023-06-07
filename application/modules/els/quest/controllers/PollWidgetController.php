<?php
class Quest_PollWidgetController extends HM_Controller_Action_Multipage_Quest
{
    const NAMESPACE_MULTIPAGE = 'pollwidget-multipage';

    public function init()
    {
        $this->setNamespaceMultipage(self::NAMESPACE_MULTIPAGE);

        parent::init();

        $serviceContainer = Zend_Registry::get('serviceContainer');
        $select = $this->getService('Infoblock')->getSelect();
        $select->from(array('i' => 'interface'), array('param_id'))
            ->where(new Zend_Db_Expr($serviceContainer->getService('Infoblock')->quoteInto('block = ?', 'manyQuizzesBlock')))
            ->limit(1);
        $questId = 0;
        if ($rowset = $select->query()->fetchAll()) {
            if (!empty($rowset[0]['param_id'])) {
                $params = unserialize($rowset[0]['param_id']);
                $questId = $params['quest_id'];
            }
        }
        if ($questId && $this->_persistentModel) {
            $mm = $this->_persistentModel->getModel();
            $questModel = $mm['quest'];
            $newQuestId = $questModel->quest_id;

            if ($newQuestId != $questId) {
                Zend_Session::namespaceUnset(self::NAMESPACE_MULTIPAGE);
                unset($this->_persistentModel);
            }
            parent::init();
        }
    }

    public function _initModel()
    {
        if (empty($this->_persistentModel)) {
            $this->_persistentModel = $this->_getPersistentModel();
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
            $this->_redirector->gotoSimple('view', 'poll-widget', 'quest', array('quest_id' => $this->_getMultipageId()));
        }
    }
    
    public function _redirectToIndex($msg = '', $type = HM_Notification_NotificationModel::TYPE_SUCCESS, $redirectUrl = false)
    {
        if (!empty($msg)) {
            $this->_flashMessenger->addMessage(array(
                'type'    => $type,
                'message' => $msg
            ));
        }
        echo "<script type='text/javascript'>window.location.reload(true);</script>";
        die;
       // $this->_redirector->gotoUrl('/');
        /*if (!$this->isAjaxRequest()) {
            $this->_redirector->gotoUrl($this->_persistentModel->getRedirectUrl());
//             $this->_redirector->gotoSimple('card', 'index', 'quest', array('quest_id' => $this->_getMultipageId()));
        }*/
    }

    public function _getBaseUrl()
    {
        return array('module' => 'quest', 'controller' => 'poll-widget');
    }    
    
    public function _getPersistentModel($mode = null, $contextEventId = null, $contextEventType = null)
    {
        $questId = $this->_getParam('quest_id');
        $model = parent::_getPersistentModel($mode, $questId, HM_Quest_Attempt_AttemptModel::CONTEXT_TYPE_WIDGET);

        if ($questId) {
            $quest = $this->getService('Quest')->getOne(
                $this->getService('Quest')->fetchAll(array('quest_id = ?' => $questId)));
            $model->setContextModel($quest, HM_Quest_Attempt_AttemptModel::CONTEXT_TYPE_WIDGET);
        }

        return $model;
    }  

    /*public function _isFinalizeable($totalResults)
    {
        return false; // это preview, нечего финализировать
    }*/


    // вырожденный Multipage из одного page  - убираем стоп
    // финалайз меняем на линк с резалтс - ибо $return['next'] с results в перентметоде не прокатывает
    // из-за проверки в tpl на $this->navPanel['nextId'])
    // чтоб не клонить полметода
    public function _getNavPanel()
    {
        $return = parent::_getNavPanel();
        if(isset($return['stop'])) {
            unset($return['stop']);
        }
        if (isset($return['finalize']) || (isset($return['next']) && $return['nextId'] == null)) {
            // грязный хак из-за проверки в tpl на $this->navPanel['nextId']), ытоб не трогать тпл квестов
            unset($return['finalize']);
            $return['nextId'] = true;
            $url = $this->_getBaseUrl();
            $return['next'] = $this->view->url(array_merge($url, array('action' => 'finalize', 'item_id' => null)));
        }
        return $return;
    }

    public function _isFinalizeable($totalResults)
    {
        return true;
    }

    public function viewAction()
    {
        $this->setControllerScriptName();
        parent::viewAction();

    }
    public function loadAction()
    {
        $this->setControllerScriptName();
        parent::loadAction();

    }

    protected function setControllerScriptName()
    {
        $request = $this->getRequest();
        $action = $request->getActionName();
        $controller = $request->getControllerName();
        $this->getHelper('viewRenderer')->setScriptAction("{$controller}/{$action}");
    }
}
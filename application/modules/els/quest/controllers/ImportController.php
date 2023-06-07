<?php
class Quest_ImportController extends HM_Controller_Action_Import
{
    protected $_importManagerClass = 'HM_Quest_Import_Manager';

    public function indexAction()
    {
        $this->questRestrict();
        $questId = (int) $this->_getParam('quest_id', 0);
        $subjectId = (int) $this->_getParam('subject_id', 0);

        if ($subjectId) {
            $url = array('module' => 'quest', 'controller' => 'subject', 'action' => 'test', 'only-type' => 'test', 'subject_id' => $subjectId);
        } else {
            $url = array('module' => 'quest', 'controller' => 'list', 'action' => 'index', 'only-type' => 'test');
        }
        $returnUrl = $this->view->url($url, null, true);
        Zend_Registry::get('session_namespace_default')->quest['import']['returnUrl'] = $returnUrl;

        parent::indexAction(); // required

        if ($this->_valid && !$this->_importManager->getCount()) {
            $this->_flashMessenger->addMessage(_('Новые тесты не найдены'));
            $this->_redirector->gotoUrl($returnUrl);
        }

        $this->view->returnUrl = $returnUrl;

    }

    public function eau2()
    {
        $this->view->setHeader(_('Импортировать тесты из eAuthor2'));
        $this->_importService = $this->getService('QuestEau2');
    }

    public function eau3()
    {
        $this->view->setHeader(_('Импортировать тесты из eAuthor3'));
        $this->_importService = $this->getService('QuestEau3');
    }

    public function processAction()
    {
        $importManager = new $this->_importManagerClass();

        if ($importManager->restoreFromCache()) {
            $importManager->init(array());
        } else {
            $importManager->init($this->_importService->fetchAll());
        }

        if (!$importManager->getCount()) {
            $this->_flashMessenger->addMessage(_('Новые тесты не найдены'));
            $this->_redirector->gotoUrl(Zend_Registry::get('session_namespace_default')->quest['import']['returnUrl']);
        }


        $subjectId = (int) $this->_getParam('subject_id', null);
        $importManager->import($subjectId);

        $this->_flashMessenger->addMessage(sprintf(_('Успешно импортировано тестов: %d'), $importManager->getInsertsCount()));
        $this->_redirector->gotoUrl(Zend_Registry::get('session_namespace_default')->quest['import']['returnUrl']);
    }
}
<?php
class Quest_ImportQuestionsController extends HM_Controller_Action_Import
{
    protected $_importManagerClass = 'HM_Quest_Question_Import_Manager';

    protected $_quest;

    public function init()
    {
        $this->questRestrict();
        $this->id = (int) $this->_getParam($this->idParamName, 0);
        $this->_quest = $subject = $this->getOne($this->getService('Quest')->findDependence(array('Settings', 'SubjectAssign'), $this->id));
        if ($this->_quest) {
            HM_Quest_Settings_SettingsService::detectScope($this->_quest);
        }
        parent::init();
    }

    public function indexAction()
    {
        $questId = (int) $this->_getParam('quest_id', 0);
        $subjectId = (int) $this->_getParam('subject_id', 0);

        if ($questId) {
            $url = array('module' => 'quest', 'controller' => 'question', 'action' => 'list', 'quest_id' => $questId);
            if ($subjectId) {
                $url['subject_id'] = $subjectId;
            }
            $returnUrl = $this->view->url($url, null, true);
        }

        Zend_Registry::get('session_namespace_default')->question['import']['returnUrl'] = $returnUrl;

        parent::indexAction(); // required

        if ($this->_valid && !$this->_importManager->getCount()) {
            $this->_flashMessenger->addMessage(_('Новые вопросы не найдены'));
            $this->_redirector->gotoUrl($returnUrl);
        }

        $this->view->returnUrl = $returnUrl;

    }

    public function txt()
    {
        $this->view->setHeader(_('Импортировать тест из текстового файла'));
        $this->_importService = $this->getService('QuestQuestionTxt');
    }

    public function excel()
    {
        $this->view->setHeader(_('Импортировать тест из Excel'));
        $this->_importService = $this->getService('QuestQuestionExcel');
    }

    public function processAction()
    {
        $importManager = new $this->_importManagerClass();

        if ($importManager->restoreFromCache()) {
            $importManager->init(array());
        } else {
            $form = $this->_importService->getForm();
            $importManager->init($this->_importService->fetchAll(), $form->file->getFileName());
        }

        if (!$importManager->getCount()) {
            $this->_flashMessenger->addMessage(_('Новые вопросы не найдены'));
            $this->_redirector->gotoUrl(Zend_Registry::get('session_namespace_default')->question['import']['returnUrl']);
        }

        $importManager->import();

        $this->_flashMessenger->addMessage(sprintf(_('Успешно импортировано вопросов: %d'), $importManager->getInsertsCount()));
        $this->_redirector->gotoUrl(Zend_Registry::get('session_namespace_default')->question['import']['returnUrl']);
    }

}
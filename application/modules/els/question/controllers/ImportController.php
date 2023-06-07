<?php
class Question_ImportController extends HM_Controller_Action_Import
{
    protected $_importManagerClass = 'HM_Question_Import_Manager';

    protected $service     = 'Subject';
    protected $idParamName = 'subject_id';
    protected $idFieldName = 'subid';
    protected $id          = 0;

    public function init()
    {
        parent::init();

        $subjectId = (int) $this->_getParam('subject_id', 0);
        if ($subjectId) { // Делаем страницу расширенной
            $this->id = (int) $this->_getParam($this->idParamName, 0);
            $subject = $this->getOne($this->getService($this->service)->find($this->id));

            $this->view->setExtended(
                array(
                    'subjectName' => $this->service,
                    'subjectId' => $this->id,
                    'subjectIdParamName' => $this->idParamName,
                    'subjectIdFieldName' => $this->idFieldName,
                    'subject' => $subject
                )
            );
        }

        $testId = (int) $this->_getParam('test_id', 0);
        $quizId = (int) $this->_getParam('quiz_id', 0);

        $isEditable = false;
        if ($testId) {
            $test = $this->getOne($this->getService('TestAbstract')->find($testId));
            if ($test) {
                $this->view->setSubHeader($test->title);
                $isEditable = $this->getService('TestAbstract')->isEditable($test->subject_id, $subjectId, $test->location);

            }
        }

        if ($quizId) {
            $quiz = $this->getOne($this->getService('Poll')->find($quizId));
            if ($quiz) {
                $this->view->setSubHeader($quiz->title);
                $isEditable = $this->getService('Poll')->isEditable($quiz->subject_id, $subjectId, $quiz->location);
            }
        }

        if (!$isEditable) {
            $this->_flashMessenger->addMessage(_('Нет прав на добавление вопросов'));
            $this->_redirector->gotoUrl($_SERVER['HTTP_REFERER']);
        }

        $this->view->setHeader(_('Импорт вопросов'));
    }

    public function indexAction()
    {
        $testId = (int) $this->_getParam('test_id', 0);
        $quizId = (int) $this->_getParam('quiz_id', 0);
        $subjectId = (int) $this->_getParam('subject_id', 0);

        if ($testId) {
            $returnUrl = $this->view->url(array('module' => 'question', 'controller' => 'list', 'action' => 'test', 'test_id' => $testId, 'subject_id' => $subjectId), null, true);
        }

        if ($quizId) {
            $returnUrl = $this->view->url(array('module' => 'question', 'controller' => 'list', 'action' => 'quiz', 'quiz_id' => $quizId, 'subject_id' => $subjectId), null, true);
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
        $this->view->setHeader(_('Импортировать вопросы из текстового файла'));
        $this->_importService = $this->getService('QuestionTxt');
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
            $this->_flashMessenger->addMessage(_('Новые вопросы не найдены'));
            $this->_redirector->gotoUrl(Zend_Registry::get('session_namespace_default')->question['import']['returnUrl']);
        }

        $importManager->import();

        $this->_flashMessenger->addMessage(sprintf(_('Были добавлены %d вопросов'), $importManager->getInsertsCount()));
        $this->_redirector->gotoUrl(Zend_Registry::get('session_namespace_default')->question['import']['returnUrl']);
    }

}
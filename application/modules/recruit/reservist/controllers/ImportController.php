<?php
class Reservist_ImportController extends HM_Controller_Action_Import
{
    protected $_importManagerClass = 'HM_Recruit_Reservist_Import_Manager';

    public function indexAction()
    {
        $url = array('baseUrl' => false, 'module' => 'reservist', 'controller' => 'list', 'action' => 'index');
        $returnUrl = $this->view->url($url, null, true);

        parent::indexAction(); // required

        if ($this->_valid && !$this->_importManager->getCount()) {
            $this->_flashMessenger->addMessage(_('Новые записи не найдены'));
            $this->_redirector->gotoUrl($returnUrl);
        }

        $this->view->returnUrl = $returnUrl;

        if ($this->_request->isPost()) {
            $this->processAction();
        }

    }

    public function template()
    {
        $this->view->setHeader(_('Импорт данных кандидатов внешнего кадрового резерва'));
        $this->_importService = $this->getService('RecruitReservistImportTemplate');
    }

    public function processAction()
    {
        $url = array('baseUrl' => false, 'module' => 'reservist', 'controller' => 'list', 'action' => 'index');
        $returnUrl = $this->view->url($url, null, true);

        $importManager = $this->_importManager;

        if (!$importManager->getCount()) {
            $this->_flashMessenger->addMessage(_('Новые записи не найдены'));
            $this->_redirector->gotoUrl($returnUrl);
        }

        $importManager->import();

        $this->_flashMessenger->addMessage(sprintf(_('Импортировано строк из исходного файла: %d'), $importManager->getSourceCount()));
        $this->_redirector->gotoUrl($returnUrl);
    }
}
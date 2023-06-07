<?php
class Orgstructure_ImportController extends HM_Controller_Action_Import
{
    public $_importManagerClass = 'HM_Orgstructure_Import_Manager';

/*    public function init()
    {
        parent::init();

        $config = Zend_Registry::get('config');
        $this->importServiceName = "OrgstructureCsv";
        $this->importOptions = $config->orgstructure->import->options->toArray();
        $this->importService = $this->getService($this->importServiceName);

        $this->importService->setOptions($this->importOptions);

    }*/

    public function csv()
    {
        $this->view->setHeader(_('Импорт оргструктуры из CSV'));
        $this->_importService = $this->getService('OrgstructureCsv');
    }

    public function csvUser()
    {
        $this->getService('Unmanaged')->setHeader(_('Импорт должностей из CSV'));
        $this->_importService = $this->getService('UserCsv');
        $this->_importManagerClass = 'HM_User_Import_Manager';
    }

    public function cronAction(){
        $this->getService('CronTask')->init();
        $this->getService('CronTask')->testrun();
    }

    public function processAction()
    {
        $classManager = $this->_importManagerClass;
        $importManager = new $classManager();


        if ($importManager->restoreFromCache()) {
            $importManager->init(array());
        } else {
            $importManager->init($this->importService->fetchAll());
        }

        if (!$importManager->getCount()) {
            $this->_flashMessenger->addMessage(_('Изменения структуры организации не найдены'));
            // Актуализируем профили должностей (поскольку в файле импорта информации о профилях нет)
            $this->_redirector->gotoSimple('check', 'list', 'orgstructure', array('from' => 'import', 'for' => 'all', 'mode' => 'show'));
            // $this->_redirector->gotoSimple('index', 'list', 'orgstructure');
        }

        $importManager->import();
        
        $this->_flashMessenger->addMessage(sprintf(_('Были добавлены %d элемента(ов) и %d пользователя(ей), обновлены %d элемента(ов) и %d пользователя(ей), удалены %d элемента(ов)'), $importManager->getInsertsCount(), $importManager->getInsertsPeopleCount(), $importManager->getUpdatesCount()-$importManager->getInsertsCount(), $importManager->getUpdatesPeopleCount(), $importManager->getDeletesCount()));
        
        // Актуализируем профили должностей
        $this->_redirector->gotoSimple('check', 'list', 'orgstructure', array('from' => 'import', 'for' => 'all', 'mode' => 'edit'));
        // $this->_redirector->gotoSimple('index', 'list', 'orgstructure');

    }


    public function indexAction()
    {
        parent::indexAction(); // TODO: Change the autogenerated stub
        $this->view->setScriptPath(APPLICATION_PATH . '/modules/els/orgstructure/views/scripts');
        $this->view->indexUrl = $this->view->serverUrl($this->view->url(array('module' => 'orgstructure', 'controller' => 'list', 'action' => 'index')));
        $this->view->processUrl = $this->view->serverUrl($this->view->url(array('module' => 'orgstructure', 'controller' => 'import', 'action' => 'process')));

    }
}
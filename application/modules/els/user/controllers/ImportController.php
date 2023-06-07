<?php
class User_ImportController extends HM_Controller_Action
{
    private $_importService = null;
    protected $_importManagerClass = 'HM_User_Import_Manager';

    public function indexAction()
    {
        $source = $this->_getParam('source', false);
        if (strpos($source, '-') !== false) {
            $source = lcfirst(join('', array_map('ucfirst', explode('-', $source))));
        }
        if (!$source || !method_exists($this, $source)) {
            throw new HM_Exception(sprintf(_('Источник %s не найден.'), $source));
        }

        call_user_func(array($this, $source));

        $this->view->form = false;
        if ($this->_importService->needToUploadFile()) {
            $this->_valid = false;
            $form = $this->_importService->getForm();
            if ($this->_request->isPost() && $form->isValid($this->_request->getPost())) {
                if ($form->file->isUploaded()) {
                    $form->file->receive();
                    if ($form->file->isReceived()) {
                        $this->_importService->setFileName($form->file->getFileName());
                        $this->_valid = true;
                    }
                }
            } else {
                $this->view->form = $form;
            }
        }

        try {
            $class = $this->_importManagerClass;
            $this->_importManager = $importManager = new $class();
            if ($this->_valid) {
                $importManager->init($this->_importService->fetchAll());
            }
        } catch(HM_Exception $e) {
            $this->_flashMessenger->addMessage(array('message' => $e->getMessage(), 'type' => HM_Notification_NotificationModel::TYPE_ERROR));
            $this->_redirectToIndex();
        }
        $this->view->importManager = $importManager;
        $this->view->source = $source;
        $this->view->cancelBtnClass = 'primary--text v-btn v-btn--outlined v-size--large';
    }

    public function indexCsvAction()
    {
        // Если в navigation/main.php в actions добавить массив урла с params, то пункт меню не выводится,
        // потому пока так, потом стоит разобраться...
        $this->_redirect($this->view->url([
            'action' => 'index',
            'controller' => 'import',
            'module' => 'user',
            'source' => 'csv'
        ]));
    }

    public function ad()
    {
        $this->view->setHeader(_('Импорт учетных записей из Active Directory'));
        $this->_importService = $this->getService('UserAd');
    }

    public function csv()
    {
        $this->view->setSubHeader(_('Импорт учетных записей из CSV'));
        $this->_importService = $this->getService('UserCsv');
    }

    public function csvUser()
    {
        return self::csv();
    }

    public function xml()
    {
        $this->view->setHeader(_('Импорт учетных записей из Xml'));
        $this->_importService = $this->getService('UserXml');
    }


    public function studyHistoryCsv()
    {
        $this->view->setHeader(_('Импорт истории обучения из CSV'));
        $this->_importService = $this->getService('RoleGraduatedCsv');
        $this->_importManagerClass = 'HM_Role_Graduated_Import_Manager';
    }

    public function processAction()
    {
        $source = $this->_getParam('source', false);

        if (strpos($source, '-') !== false) {
            $source = lcfirst(implode('', array_map(function($v) {return ucfirst($v);}, explode('-', $source))));
        }

        if (!$source || !method_exists($this, $source)) {
            throw new HM_Exception(sprintf(_('Источник %s не найден.'), $source));
        }

        call_user_func(array($this, $source));

//        $importManager = new HM_User_Import_Manager();
        $importManagerClass = $this->_importManagerClass;
        $importManager = new $importManagerClass();

        if ($importManager->restoreFromCache()) {
            $importManager->init(array());
        } else {
            $importManager->init($this->_importService->fetchAll());
        }

        if (!$importManager->getCount()) {
            $this->_flashMessenger->addMessage(_('Новые пользователи не найдены'));
        }

        $importManager->import();

        if ($importManager->getUpdatesCount() || $importManager->getInsertsCount()) {
            $this->_flashMessenger->addMessage(sprintf(_('Были добавлены %d пользователя(ей) и обновлены %d пользователя(ей)'), $importManager->getInsertsCount(), $importManager->getUpdatesCount()));
        }

//        if ($importManager->getGroupsCount()) {
//            $this->_flashMessenger->addMessage(sprintf(_('Были обновлены группы у %d пользователя(ей)'), $importManager->getGroupsCount()));
//        }
//        if ($importManager->getUserTagsCount()) {
//            $this->_flashMessenger->addMessage(sprintf(_('Были обновлены метки у %d пользователя(ей)'), $importManager->getUserTagsCount()));
//        }
        if ($importManager->getNotProcessedCount()) {
            $this->_flashMessenger->addMessage(sprintf(_('Не были обработаны %d записи(ей)'), $importManager->getNotProcessedCount()));
        }

        $session = new Zend_Session_Namespace('default');
        if ($session->orgstructure_id) {
            $this->_redirector->gotoSimple('index', 'list', 'orgstructure');
        }

        if ($this->_importManagerClass == 'HM_Role_Graduated_Import_Manager') {
            $this->_redirector->gotoSimple('index', 'history', 'subject');
        } else {
            switch ($this->getService('User')->getCurrentUserRole()) {
                case HM_Role_Abstract_RoleModel::ROLE_DEAN :
                    $this->_redirector->gotoSimple('index', 'list', 'study-groups', ['subject_id' => '0']);
                    break;
                default :
                    $this->_redirector->gotoSimple('index', 'list', 'user');
            }

        }
    }
}
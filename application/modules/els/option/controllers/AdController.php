<?php

class Option_AdController extends HM_Controller_Action
{
    public function indexAction()
    {
        // решено вернуть настройки в конфиг, т.к. теперь происходит подключение к 6 LDAP серверам

//        $form = new HM_Form_Options();
//
//        if ($this->_request->isPost()) {
//            if ($form->isValid($this->_request->getParams())) {
//                $update = $form->getValues();
//                $this->getService('Option')->setOptions($update);
//                $this->_flashMessenger->addMessage(_('Настройки интеграции успешно изменены.'));
//                $this->_redirector->gotoSimple('index', 'ad', 'option');
//            } else {
//                $form->populate($this->_request->getParams());
//            }
//        } else {
//            $default = $this->getService('Option')->getOptions(HM_Option_OptionModel::SCOPE_AD);
//            $form->populate($default);
//        }
//
//        $this->view->form = $form;
    }
    
    public function runAction()
    {
        if (!$this->getService('Acl')->inheritsRole($this->getService('User')->getCurrentUserRole(), HM_Role_Abstract_RoleModel::ROLE_ADMIN)){
            exit();
        }
        setlocale(LC_ALL, 'ru_RU.UTF8'); // чтобы работал fgetcsv в процессеимпорта из 1С
    
        $this->getService('CronTask')->init()->run('usersSync');
        $this->_flashMessenger->addMessage(_('Задания планировщика успешно выполнены'));
        $this->_redirector->gotoSimple('index');
    }    
}
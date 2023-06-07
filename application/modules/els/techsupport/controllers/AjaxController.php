<?php

class Techsupport_AjaxController extends HM_Controller_Action
{

    public function init()
    {
        $this->_helper->getHelper('layout')->disableLayout();
//        $this->getHelper('viewRenderer')->setNoRender();
        Zend_Controller_Front::getInstance()->unregisterPlugin('HM_Controller_Plugin_Unmanaged');
        $this->getResponse()->setHeader('Content-type', 'text/html; charset=' . Zend_Registry::get('config')->charset);
    }

    public function getFormAction()
    {
        //не удалять! форма в шаблоне
    }

    public function viewAction()
    {
        $supportRequestId = (int)$this->_getParam('support_request_id', 0);
        $techsupportService = $this->getService('Techsupport');

        /** @var HM_Hr_Reserve_Request_RequestModel $request */
        $request = $techsupportService->find($supportRequestId)->current();

        $this->view->request = $request;

        $this->view->viewPageUrl = $this->view->url(
            array(
                'module' => 'techsupport',
                'controller' => 'ajax',
                'action' => 'view-page',
                'support_request_id' => $supportRequestId
            ), null, true
        );

        if ($this->isAjaxRequest()) {
            $isAjax = true;
            $this->_helper->getHelper('layout')->disableLayout();
            Zend_Controller_Front::getInstance()->unregisterPlugin('HM_Controller_Plugin_Unmanaged');

//            $this->view->title = $request->theme;
            $fields = $request->getCardFields();
            return $this->view->fields = $this->view->card($request, $fields, [], $isAjax);
        }
    }

    public function viewPageAction() {
        $this->_redirector = $this->_helper->getHelper('ConditionalRedirector');

        $supportRequestId = (int) $this->_getParam('support_request_id', 0);
        $techsupportService = $this->getService('Techsupport');

        $request = $techsupportService->find($supportRequestId)->current();

        if (!$request) {
            $this->_redirector->gotoSimple('index', 'list', 'techsupport');
            exit();
        }

        if ($request->user_id != $this->getService('User')->getCurrentUserId()) {
            //не делаем проверку на достуаность "Войти от имени", так как страница доступна только администратору
            $this->getService('User')->authorizeOnBehalf($request->user_id);
        }
        $this->_redirector->gotoUrl($request->url);
        exit();
    }


    public function postRequestAction() {
        $this->getHelper('viewRenderer')->setNoRender();
                
        $params  = $this->getJsonParams();
        $request = $this->getRequest();
        $referer = $request->getHeader('referer');
        
        $userService        = $this->getService('User');
        $techsupportService = $this->getService('Techsupport');
        
        if (!($params['theme'] == '')) {
            $data = array();
            $data['theme']               = $params['theme'];
            $data['problem_description'] = $params['problem_description'];
            $data['wanted_result']       = $params['wanted_result'];
            $data['date_']               = date("Y-m-d H:i:s");
            $data['status']              = HM_Techsupport_TechsupportModel::STATUS_NEW;
            $data['user_id']             = $userService->getCurrentUserId();
            $data['url']                 = $referer;

            if ($data['user_id']) {
                $result = $techsupportService->insert($data);
                if ($result) {
                    echo _('Запрос успешно отправлен!');

                    $user = $userService->getCurrentUser();
                    $statuses = HM_Techsupport_TechsupportModel::getStatuses();

                    $messageData = array(
                        'id'     => $result->support_request_id,
                        'title'  => $result->theme,
                        'status' => $statuses[$result->status],
                        'lfname' => $user->LastName . ' ' . $user->FirstName . ' ' . $user->Patronymic,
                    );
                    $this->statusChangedMessage($messageData, $result->user_id);

                    $requestMessage = "\n" .
                        _('Описание проблемы:') . "\n" .
                        $result->problem_description . "\n" .
                        _('Ожидаемый результат:') . "\n" .
                        $result->wanted_result;
                    $messageData['request'] = $requestMessage;

                    $this->newMessage($messageData);

                } else {
                    echo _('Ошибка, заявка не была добавлена!');
                }
            } else {
                echo _('Сообщение не было отправлено.') . "<br>" .  _('Пожалуйста, авторизуйтесь и повторите попытку.');
            }
        } else {
            echo _('Не заполнено обязательно поле!');
        }
    }
    
    public function statusChangedMessage($messageData, $user_id) {
        $messenger = $this->getService('Messenger');

        $messenger->setOptions(
            HM_Messenger::TEMPLATE_SUPPORT_STATUS,
            $messageData
        );

        $messenger->send(HM_Messenger::SYSTEM_USER_ID, $user_id);
    }
    
    public function newMessage($messageData) {
        $messenger = $this->getService('Messenger');

        $messenger->setOptions(
            HM_Messenger::TEMPLATE_SUPPORT_NEW,
            $messageData
        );

        $messenger->send(HM_Messenger::SYSTEM_USER_ID, HM_Messenger::SYSTEM_USER_ID);
    }
    
}
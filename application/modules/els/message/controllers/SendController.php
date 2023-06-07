<?php
class Message_SendController extends HM_Controller_Action_Activity
{
    public function indexAction()
    {
        $isAtManager = (Zend_Registry::get('serviceContainer')->getService('Acl')->inheritsRole(
            Zend_Registry::get('serviceContainer')->getService('User')->getCurrentUserRole(),array(
            HM_Role_Abstract_RoleModel::ROLE_ATMANAGER,
            HM_Role_Abstract_RoleModel::ROLE_ATMANAGER_LOCAL
        )));

        $defaultNS   = new Zend_Session_Namespace('default');
        $subjectId   = (int) $this->_getParam('subject_id', 0);
        if ($isAtManager) {
            $sessionId = (int) $this->_getParam('session_id', 0);
            $session   = $this->getService('AtSession')->find($sessionId)->current();
        }

        $subject     = $this->_getParam('subject', $subjectId ? 'subject' : false);
        $postMassIds =
            $this->_getParam('postMassIds_grid_contacts',
            $this->_getParam('postMassIds_grid',
            $this->_getParam('MID',
            $this->_getParam('postMassIds_grid'.$subjectId,
            $this->_getParam('postMassIds_grid'.$sessionId,'')))));

        // со страницы заявок и прошедших обучения пришли не ИД пользователей, а ИД соотв элементов
        $postMassIds = $this->getUserIDs(explode(',', trim($postMassIds)));

        // сохраняем ссылку с которой пришли если пришли не из этого экшена
        if ( !strstr($_SERVER['HTTP_REFERER'],'message/send') ) {
            $defaultNS->message_referer_page = $_SERVER['HTTP_REFERER'];
        }

        $form = new HM_Form_Message();
        $request = $this->getRequest();
        if ($request->isPost() && $this->_hasParam('theme') && $this->_hasParam('message')) {
            if ($form->isValid($request->getParams())) {

                $theme     = $form->getValue('theme');
                $message   = $form->getValue('message');
                $sessionId = $form->getValue('session_id');

                $messenger = $this->getService('Messenger');

                $postMassIds = $form->getValue('users');
                if (strlen($postMassIds)) {
                    $ids = explode(',', $postMassIds);
                    if (count($ids)) {
                        $users = $this->getService('User')->fetchAll(array('MID IN (?)' => $ids));

                        foreach($users as $user) {

                            $userMessage = $message;
                            $userMessage = str_replace('[LOGIN]', $user->Login, $userMessage);

                            if (strpos($userMessage, '[URL]') !== false) {
                                $url = ($_SERVER['HTTPS'] == 'on' ? 'https' : 'http') . '://' . $_SERVER['HTTP_HOST'];
                                $userMessage = str_replace('[URL]', $url, $userMessage);
                            }

                            if ((strpos($userMessage, '[NEW_PASSWORD]') !== false) && $this->getService('Acl')->inheritsRole($this->getService('User')->getCurrentUserRole(), array(HM_Role_Abstract_RoleModel::ROLE_ADMIN, HM_Role_Abstract_RoleModel::ROLE_ATMANAGER))) {
                                $data = $user->getData();
                                $password = $this->getService('User')->getRandomString();
                                $data['Password'] = new Zend_Db_Expr("PASSWORD(" . $this->getService('User')->getSelect()->getAdapter()->quote($password) . ")");
                                $userMessage = str_replace('[NEW_PASSWORD]', $password, $userMessage);
                                $this->getService('User')->update($data);
                            }

                            if ((strpos($userMessage, '[URL_SESSION]') !== false) && $isAtManager && $sessionId) {
                                $urlSession = ($_SERVER['HTTPS'] == 'on' ? 'https' : 'http') . '://' . $_SERVER['HTTP_HOST'] .
                                    $this->view->url(
                                    array(
                                        'module' => 'session',
                                        'controller' => 'report',
                                        'action' => 'card',
                                        'session_id' => $sessionId,
                                        'baseUrl' => 'at',
                                        'gridmod' => null,
                                        'MID' => null
                                    )
                                );
                                $userMessage = str_replace('[URL_SESSION]', $urlSession, $userMessage);
                            }

                            if ((strpos($userMessage, '[SESSION_BEGIN]') !== false) && $isAtManager && $session) {
                                $sessionBegin = date('d.m.Y', strtotime($session->begin_date));
                                $userMessage = str_replace('[SESSION_BEGIN]', $sessionBegin, $userMessage);
                            }

                            if ((strpos($userMessage, '[SESSION_END]') !== false) && $isAtManager && $session) {
                                $sessionEnd = date('d.m.Y', strtotime($session->end_date));
                                $userMessage = str_replace('[SESSION_END]', $sessionEnd, $userMessage);
                            }

                            if ((strpos($userMessage, '[CONTACTS]') !== false) && $isAtManager && $session) {
                                $contacts = $this->getService('AtSession')->getManagerContacts($session->initiator_id);
                                $userMessage = str_replace('[CONTACTS]', $contacts, $userMessage);
                            }

                            $messenger->setOptions(
                                HM_Messenger::TEMPLATE_PRIVATE,
                                array(
                                    'text' => $userMessage,
                                    'subject' => $theme
                                ),
                                $subject,
                                $subjectId
                            );

                            try {
                                $messenger->send($this->getService('User')->getCurrentUserId(), $user->MID);
                            } catch (Exception $e) {

                            }
                        }
                    }
                }

                $this->_flashMessenger->addMessage( ( count($ids) == 1)? _('Сообщение отправлено') : _('Сообщения отправлены'))  ;
                //$this->_redirector->gotoSimple('index', 'contact', 'message', array('subject' => $subject, 'subject_id' => $subjectId));
                $this->_redirector->gotoUrl($defaultNS->message_referer_page);
            }
        } else {

            if (!strlen($postMassIds)) {

                $this->_flashMessenger->addMessage(_('Пользователи не выбраны'));
                //$this->_redirector->gotoSimple('index', 'contact', 'message');
                $this->_redirector->gotoUrl($defaultNS->message_referer_page);
            }
            $form->setDefault('users', $postMassIds);
            $form->setDefault('subject', $subject);
            $form->setDefault('subject_id', $subjectId);
            $form->setDefault('session_id', $sessionId);

            $currentUser = $this->getService('User')->find($this->getService('User')->getCurrentUserId())->current();
            $form->setDefault(
                'theme',
                ('Личное сообщение'));
        }

        $users = array();
        if (strlen($postMassIds)) {
            $ids = explode(',', $postMassIds);
            if (count($ids)) {
                $users = $this->getService('User')->fetchAll(
                    $this->getService('User')->quoteInto('MID IN (?)', $ids)
                );
            }
        }

        $this->view->users = $users;
        $this->view->form = $form;

    }

    /**
     * По HTTP_REFERER определяем откуда пришли, и если это страница заявок или прошедших обучение,
     * по элементам в массиве $postMassIds получаем ИД соответствующих пользователей
     * @param array $postMassIds
     * @return string:
     */
    private function getUserIDs($postMassIds)
    {
        $arResult = array();

        if ( !$postMassIds ) return '';

        // если явно передается MID - это пипл
        if ( $this->_hasParam('MID') ) return implode(',', $postMassIds);

        if ( strstr($_SERVER['HTTP_REFERER'],'order/list') ) { // заявки на обучение
            $result = $this->getService('Claimant')->fetchAll('SID IN (' . implode(',', $postMassIds) . ')');
            if ( count($result)>0 ) {
                foreach ($result as $rItem) {
                    $arResult[] = $rItem->MID;
                }
            }
        } elseif ( strstr($_SERVER['HTTP_REFERER'],'assign/graduated') ) { // прошедшие обучение
            $result = $this->getService('Graduated')->fetchAll('SID IN (' . implode(',', $postMassIds) . ')');
            if ( count($result)>0 ) {
                foreach ($result as $rItem) {
                    $arResult[] = $rItem->MID;
                }
            }
        } elseif ( strstr($_SERVER['HTTP_REFERER'],'session/user') ) { // участники оц.сессии
            $result = $this->getService('Orgstructure')->fetchAll('soid IN (' . implode(',', $postMassIds) . ')');
            if ( count($result)>0 ) {
                foreach ($result as $rItem) {
                    $arResult[] = $rItem->mid;
                }
            }
        } elseif ( strstr($_SERVER['HTTP_REFERER'],'session/respondent') ) { // респонденты в оц.сессии
            $result = $this->getService('AtSessionRespondent')->fetchAll('session_respondent_id IN (' . implode(',', $postMassIds) . ')');
            if ( count($result)>0 ) {
                foreach ($result as $rItem) {
                    $arResult[] = $rItem->user_id;
                }
            }
            // TODO: index и monitoring перепутаны местами?
        } elseif ( strstr($_SERVER['HTTP_REFERER'],'session/index/monitoring') ) { // супервайзеры
            $result = $this->getService('Orgstructure')->fetchAll('owner_soid IN (' . implode(',', $postMassIds) . ') AND is_manager = 1');
            if ( count($result)>0 ) {
                foreach ($result as $rItem) {
                    $arResult[] = $rItem->mid;
                }
            }
        } elseif (strstr($_SERVER['HTTP_REFERER'], 'session/monitoring/index')) { // результаты в оц.сессии
            $result = $this->getService('Orgstructure')->fetchAll('owner_soid IN (' . implode(',', $postMassIds) . ') AND is_manager = 1');
            if (count($result) > 0) {
                foreach ($result as $rItem) {
                    $arResult[] = $rItem->mid;
                }
            }
        } else { // если пришли с другой страницы - возвращаем что было - это пиплы
            return implode(',', $postMassIds);
        }
        return ( $arResult )? implode(',', $arResult): '';
    }

    public function instantSendAction(){

        $users = $this->_getParam('users', array());
        $subject = $this->_getParam('subject', false);
        $subjectId = (int) $this->_getParam('subject_id', 0);
        $addresatModeAjax = trim($subject)=="";
        $form = new HM_Form_InstantSend();
        $request = $this->getRequest();
        if ($request->isPost() && $this->_hasParam('message')) {
            if ($form->isValid($request->getParams())) {

                $message = $form->getValue('message');

                /*@var $messenger HM_Messanger */
                $messenger = $this->getService('Messenger');
                $messenger->setTemplate(HM_Messenger::TEMPLATE_PRIVATE);
                $messenger->assign(array('text' => $message, 'subject' => _('Личное сообщение')));
                $messenger->setRoom($subject, $subjectId);
                $postMassIds = $form->getValue('users');

                if (!empty($users)) {
                    if (count($users)) {
                        foreach($users as $id) {
                            $messenger->send($this->getService('User')->getCurrentUserId(), $id);
                        }
                    }
                }

                $this->_flashMessenger->addMessage(_('Сообщение отправлено'));
                $this->_redirector->gotoSimple('index', 'view', 'message', array('subject' => $subject, 'subject_id' => $subjectId));
            } else {

                if (empty($users)) {

                    $this->_flashMessenger->addMessage(_('Пользователи не выбраны'));
                    $this->_redirector->gotoSimple('index', 'contact', 'message');
                }
                $form->setDefault('subject', $subject);
                $form->setDefault('subject_id', $subjectId);
//#16837
                if($addresatModeAjax)
                {
                    $users = $form->getValue('users');
                    $users2init = array();
                    $users = $this->getService('User')->fetchAll('mid in ('.implode(',', $users).')');
                    foreach($users as $u){
                        $fio =  "{$u->LastName} {$u->FirstName} {$u->Patronymic}";
                        $users2init[$u->MID] = trim($fio)?$fio:$u->Login;
                    }
                    $form->setDefault('users', $users2init); 
                }
            }
        }else{
//#16837
            if(!$addresatModeAjax)
            {
            $multiElement = $form->getElement('users');
            if ($subjectId <= 0) {
                $collection = array($this->getService('Activity')->getActivityUsers());
            } else {
                    $collection = array($this->getService('Subject')->getAssignedUsers($subjectId),
                                        $this->getService('Subject')->getAssignedTeachers($subjectId));
            }
                $ret = array();
            foreach ($collection as $subcollection)
            {
                if (count($subcollection)>0)
                {
                    foreach($subcollection as $value){
                        if($value->MID != $this->getService('User')->getCurrentUserId()) {
                            $ret[$value->MID] = $value->getName();
                        }
                    }
                }
            }
//[che 20.05.2014 #16837]
	    $ret2 = array();
	    foreach($ret as $i=>$r)
	    {
		$ret2[$i] = trim($r)."[SPLIT]{$i}";
	    }
 	    sort($ret2);
	    $ret = array();
	    foreach($ret2 as $r)
	    {
		$split = explode('[SPLIT]', $r);
		$ret[$split[1]] = $split[0];
	    }
//////////////////////////

                $multiElement->setOptions(array('multiOptions' => $ret));
        }
        }

        $this->view->form = $form;
    }

    public function simpleAction()
    {
        $userIds = array_filter(explode(',', $this->getParam('user_ids')));
        array_walk($userIds, 'trim');
        if(!count($userIds)) {
            return $this->_helper->json([
                'result' => false,
                'message' => _('Не указаны id получателей'),
            ]);
        }

        $message = $this->getJsonParams()['message'];
        $users = $this->getService('User')->fetchAll(array('MID IN (?)' => $userIds));
        $messenger = $this->getService('Messenger');
        $currentUser = $this->getService('User')->getCurrentUser();
        $currentUserName = $currentUser->FirstName . ' ' . $currentUser->LastName;
        $subjectId = $this->getParam('subject_id', 0);

        foreach ($users as $user) {
            $theme = _('Личное сообщение');

            $messenger->setOptions(
                HM_Messenger::TEMPLATE_PRIVATE,
                array(
                    'text' => $message,
                    'subject' => $theme
                ),
                'subject',
                $subjectId
            );
            try {
                $messenger->send($currentUser->MID, $user->MID);
            } catch (Exception $e) {

            }
        }

        return $this->_helper->json([
            'result' => true,
            'message' => _('Сообщение успешно отправлено'),
        ]);
    }
}

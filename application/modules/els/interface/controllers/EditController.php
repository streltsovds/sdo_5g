<?php

class Interface_EditController extends HM_Controller_Action
{
    public function indexAction()
    {
        $view = $this->view;

        $view->roles = HM_Role_Abstract_RoleModel::getBasicRoles(true, true);
        $view->role = $this->_getParam('role', 'admin');
        $view->getUrl = $view->url(array('module' => 'interface', 'controller' => 'edit', 'action' => 'get-infoblocks'));
        $view->saveUrl = $view->url(array('module' => 'interface', 'controller' => 'edit', 'action' => 'update'));
        $view->layoutContentFullWidth = true;
    }

    // починка json_encode() ошибки
    public function _fixBlocksForRole(&$infoBlocksForRole) {
        foreach ($infoBlocksForRole as &$block) {
            /** @see https://stackoverflow.com/a/49564258 */
            $block['innerHtml'] = mb_convert_encoding($block['innerHtml'], 'UTF-8', 'UTF-8');
        }
    }

    public function getInfoblocksAction()
    {
        $roles = HM_Role_Abstract_RoleModel::getBasicRoles(true, true);
        $role = $this->_getParam('role', null);

        $userId = $this->getService('User')->getCurrentUserId();

        $allInfoblocks = $this->getService('Infoblock')->getTree($role, true, $userId);

        $infoBlocksForRole = $this->view->infoBlocks(true, $role, $roles, true);

        $this->_fixBlocksForRole($infoBlocksForRole);

        $data = [
            'items' => $infoBlocksForRole,
            'allItems' => $allInfoblocks
        ];

         return $this->responseJson($data);
    }

    public function clearMeAction()
    {
        $this->_helper->getHelper('layout')->disableLayout();
        Zend_Controller_Front::getInstance()->unregisterPlugin('HM_Controller_Plugin_Unmanaged');
        $this->getHelper('viewRenderer')->setNoRender();

        $userId = $this->getService('User')->getCurrentUserId();
        $currentUserRole = $this->getService('User')->getCurrentUserRole(true);

        if ($userId && $currentUserRole) {
            $this->getService('Infoblock')->clearUserData($currentUserRole, $userId);
        }
    }

    public function updateAction()
    {
        /** @var HM_User_UserService $userService */
        $userService = $this->getService('User');
        /** @var HM_Infoblock_InfoblockService $infoBlockService */
        $infoBlockService = $this->getService('Infoblock');

        $userId = $this->getService('User')->getCurrentUserId();

        if (!$userService->isRoleExists($userId, HM_Role_Abstract_RoleModel::ROLE_ADMIN) ) {
            throw new Exception("U'r not allowed he-he", 403);
        }

        $role = $this->_request->getParam('role');

        $widgets = Zend_Json::decode($this->_getParam('widgets'));

        if ($userId){
            $infoBlockService->insertBlocks($widgets, $role, 0);
        }

        $roles = HM_Role_Abstract_RoleModel::getBasicRoles(true, true);


        $infoBlocks = $this->view->infoBlocks(true, $role, $roles, true);

        /**
         * Note: магическим образом кодируется в
         * @see HM_Controller_Action_Trait_Ajax::postDispatchAjax()
         */
//        $this->_helper->getHelper('layout')->disableLayout();
//        Zend_Controller_Front::getInstance()->unregisterPlugin('HM_Controller_Plugin_Unmanaged');
//        $this->getHelper('viewRenderer')->setNoRender();
//        $this->view->items = $infoBlocks;

        return $this->responseJson($infoBlocks);
    }


    public function updateMyAction()
    {
        /** @var HM_User_UserService $userService */
        $userService = $this->getService('User');
        /** @var HM_Infoblock_InfoblockService $infoBlockService */
        $infoBlockService = $this->getService('Infoblock');

        $this->_helper->getHelper('layout')->disableLayout();
        Zend_Controller_Front::getInstance()->unregisterPlugin('HM_Controller_Plugin_Unmanaged');
        $this->getHelper('viewRenderer')->setNoRender();

        $widgets = Zend_Json::decode($this->_getParam('widgets'));

        $userId = $userService->getCurrentUserId();
        $role   = $userService->getCurrentUserRole(true);

        if ($userId){
            $infoBlockService->insertBlocks($widgets, $role, $userId);
        }
    }

    public function prepare()
    {
        $this->getService('Unmanaged')->getController()->page_id = 'm00';

        $userId = $this->_getParam('user_id', 0);
        if ($userId > 0) {
            if (!$this->getService('Acl')->isCurrentAllowed(HM_Acl::RESOURCE_USER_CONTROL_PANEL, HM_Acl::PRIVILEGE_VIEW)) {
                $userId = $this->getService('User')->getCurrentUserId();
            }
        } else {
            $userId = $this->getService('User')->getCurrentUserId();
        }

        if ($this->getRequest()->getActionName() == 'card') {
            $this->view->setHeader(_('Личный кабинет'));
            if ($userId != $this->getService('User')->getCurrentUserId()) {
                $user = $this->getOne($this->getService('User')->find($userId));
                if ($user) {
                    $this->view->setHeader(sprintf(_('Пользователь %s'), $user->getName()));
                }
            }
        }

        $this->_userId = $userId;

        // Если нет истории обучения, то удаляем этот пункт из меню
        $container = $this->view->getContextNavigation();
        if (null != $container) {
            if ($userId != $this->getService('User')->getCurrentUserId()) {
                if (!$this->getService('Acl')->isCurrentAllowed(HM_Acl::RESOURCE_USER_CONTROL_PANEL, HM_Acl::PRIVILEGE_EDIT)) {
                    $page = $container->findBy('resource', 'cm:user:page2');
                    $page->visible = false;
                    //$container->removePage($page);
                }

                $page = $container->findBy('resource', 'cm:user:page3');
                $page->visible = false;
                //$container->removePage($page);
            }

            $page = $container->findByAction('study-history');
            if ($page) {
                $collection = $this->getService('Graduated')->fetchAll($this->getService('Graduated')->quoteInto('MID = ?', $userId));
                if (!count($collection)) {
                    $page->visible = false;
                    //$container->removePage($page);
                }
            }
        }



    }


    public function deleteActionsExtendedAction() {
        if(unlink('../data/temp/actions_extended.xml')){
            echo 'actions_extended.xml удален';
        } else {
            echo 'Не удалось actions_extended.xml, возможно кто-то удалил его раньше ) ';
        }
        exit;
    }

    public function designSettingsAction()
    {
        $form = new HM_Form_DesignSettings();
        $request = $this->getRequest();
        $isReset = $this->_getParam('reset-settings', 0);

        /** @var HM_Option_OptionService $optionService */
        $optionService = $this->getService('Option');

        if ($isReset) {
            $result = $optionService->deleteOptions(
                array_keys($optionService->getDefaultOptions(HM_Option_OptionModel::SCOPE_DESIGN))
            );
            if($result) {
                $message = array(
                    'type' => HM_Notification_NotificationModel::TYPE_SUCCESS,
                    'message' => _('Настройки успешно сброшены к стандартным.')
                );
            } else {
                $message = array(
                    'type' => HM_Notification_NotificationModel::TYPE_ERROR,
                    'message' => _('При сбросе настроек произошли ошибки.')
                );
            }
            $this->_flashMessenger->addMessage($message);
            $this->_redirector->gotoUrl($this->view->serverUrl());
        }

        if($request->isPost()) {
            if ($form->isValid($request->getParams())) {
                $values = array(
                    'index_description' => $form->getValue('index_description'),
                    'skin' => $form->getValue('skin'),
                    'windowTitle' => $form->getValue('windowTitle'),
                    'vk' => $form->getValue('vk'),
//                    'facebook' => $form->getValue('facebook'),
                    'youtube' => $form->getValue('youtube'),
                    'telegram' => $form->getValue('telegram'),
//                    'instagram' => $form->getValue('instagram'),
                );
                $logo = $form->getElement('logo');
                $timePostfix = '?mtime='.mktime();

                if ($logo->isUploaded()) {
                    $filename = $logo->getFileName(null, false);
                    $extension = pathinfo($filename, PATHINFO_EXTENSION);

                    $path = Zend_Registry::get('config')->path->upload->custom_design . 'custom_logo.' . $extension;
                    $logo->addFilter('Rename', array('target' => $path, 'overwrite' => true));
                    $logo->receive();
                    /*$img = PhpThumb_Factory::create($path);
                    // костыль для виджета subjectSlider
                    if (imagesx($img->getOldImage()) != HM_View_Infoblock_SubjectsSliderBlock::THUMB_WIDTH) {
                        $img->adaptiveResize(90, 90);
                    }
                    $img->save($path);*/
                    $values['logo'] =
                        "/" . Zend_Registry::get('config')->src->upload->custom_design . 'custom_logo.' . $extension . $timePostfix;

                }

                for($i=1; $i <= 5; $i++) {
                    $elementName = 'loginBg'.$i;
                    $loginBg = $form->getElement($elementName);
                    if ($loginBg->isUploaded()) {
                        $filename = $loginBg->getFileName(null, false);
                        $extension = pathinfo($filename, PATHINFO_EXTENSION);

                        $path = Zend_Registry::get('config')->path->upload->custom_design . $elementName . '.' . $extension;
                        $loginBg->addFilter('Rename', array('target' => $path, 'overwrite' => true));
                        $loginBg->receive();
                        $values[$elementName] =
                            "/" . Zend_Registry::get('config')->src->upload->custom_design . $elementName . '.' . $extension . $timePostfix;

                    }
                }

                $result = $optionService->setOptions($values);
                if ($result) {
                    $message = array(
                        'type' => HM_Notification_NotificationModel::TYPE_SUCCESS,
                        'message' => _('Тема оформления успешно изменена.')
                    );
                } else {
                    $message = array(
                        'type' => HM_Notification_NotificationModel::TYPE_ERROR,
                        'message' => _('При изменении темы оформления произошли ошибки.')
                    );
                }

                $this->_flashMessenger->addMessage($message);
                $this->_redirector->gotoUrl($this->view->serverUrl());
            }
        }
        $designOptions = $optionService->getOptions(HM_Option_OptionModel::SCOPE_DESIGN);
        $socialOptions = $optionService->getOptions(HM_Option_OptionModel::SCOPE_SOCIAL);
        $options = array_merge($designOptions, $socialOptions);
        $form->populate($options);
        $this->view->form = $form;

    }


}
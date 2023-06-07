<?php
class User_RegController extends HM_Controller_Action
{
    /**
     * Сохранение данных пользователя, отправка уведомления
     * @param HM_Form_User $form
     * @return null | HM_User_UserModel;
     */
    private function createUser(HM_Form_User $form)
    {
         //Извлекаем из формы регистрации ФИО пользователя
        //$lastName   =   $form->getValue('lastname');
        //$firstName  =   $form->getValue('firstname');
        //$patronymic =   $form->getValue('patronymic');
        //Делаем запрос в БД(Table)`People` и проверяем существует ли такой пользователь
        //если существует, то кладем в переменную dublicated MID пользователя на которого
        //похож, регистрирующийся пользователь - дубликат
        //$dublicated = $this->getService('User')->checkDublicate($lastName,$firstName,$patronymic);
        # создаем юзера


        $array = array(
                   'Login' => $form->getValue('userlogin'),
                   'FirstName' => $this->FilterString($form->getValue('firstname')),
                   'LastName' => $this->FilterString($form->getValue('lastname')),
                   'LastNameLat' => ((null !== $form->getValue('lastnameLat')) ? $form->getValue('lastnameLat') : ''),
                   'FirstNameLat' => ((null !== $form->getValue('firstnameLat')) ? $form->getValue('firstnameLat') : ''),
                   'Patronymic' => $this->FilterString($form->getValue('patronymic')),
                   'email' => $form->getValue('email'),
                   'Gender' => $form->getValue('gender'),
                   'email_confirmed' => HM_User_UserModel::EMAIL_NOT_CONFIRMED,
                   'Password' => new Zend_Db_Expr("PASSWORD(" . $this->getService('User')->getSelect()->getAdapter()->quote($form->getValue('userpassword')) . ")"),
                   'blocked' => $this->getService('Option')->getOption('regValidateEmail') ? 1 : 0 || $this->getService('Option')->getOption('regAutoBlock') ? 1 : 0,
                   'Registered'     => 0,
                   //'dublicate'      =>((null !==  $dublicated) ?  $dublicated : '')
                );

        if ($form->getValue('birthdate')) {
            $dateArr = explode('.', $form->getValue('birthdate'));
            $array['BirthDate'] = $dateArr[2].'-'.$dateArr[1].'-'.$dateArr[0];
        }

        $user = $this->getService('User')->insert($array);

        if ( $user ) {
            $this->getService('User')->updateAdditionalFields($user->MID, $form);

            // Добавляем в оргструктуру
            if ($form->getValue('position_id')) {
                $profileId = (int) $form->getValue('position_name');
                $profile = $this->getService('AtProfile')->find($profileId);
                $positionName = count($profile) ? $profile->current()->name : '';
                $this->getService('Orgstructure')->assignUser($user->MID, $form->getValue('position_id'), $positionName, $profileId, true);
            }

            $messenger = $this->getService('Messenger');
            if ($this->getService('Option')->getOption('regValidateEmail')) {

                $hash = $this->getService('User')->getEmailConfirmationHash($user->MID);
                // @todo: как определить scheme?
                $url = ($_SERVER['HTTPS'] == 'on' ? ' https' : 'http') . '://'. $_SERVER['SERVER_NAME'] . Zend_Registry::get('config')->url->base . $this->view->url(array(
                    'module' => 'user',
                    'controller' => 'reg',
                    'action' => 'confirm-email',
                    'user_id' => $user->MID,
                    'key' => $hash,
                ), NULL, true);
                $url = '<a href="' . $url . '">' . $url . '</a>';

                # Шлём письмо о необходимости подтверждения email
                $messenger->setOptions(
                    HM_Messenger::TEMPLATE_REG_CONFIRM_EMAIL,
                    array(
                        'email_confirm_url' => $url,
                    )
                );
            } else {
                # Шлём письмо о регистрации
            $messenger->setOptions(
                HM_Messenger::TEMPLATE_REG,
                array(
                    'fio' => $user->getNameCyr(),
                    'login' => $user->Login,
                    'password' => $form->getValue('userpassword')
                )
            );
            }
            $messenger->send(HM_Messenger::SYSTEM_USER_ID, $user->MID);

            # Сохраняем метаданные
            $user->setMetadataValues($this->getService('User')->getMetadataArrayFromForm($form));
            $user = $this->getService('User')->update($user->getValues());

            # сохраняем фото
            $photo = $form->getElement('photo');
            if($photo->isUploaded()) {
                $path = $this->getService('User')->getPath(Zend_Registry::get('config')->path->upload->photo, $user->MID);
                $photo->addFilter('Rename', array('target' => $path . $user->MID . '.jpg', 'overwrite' => true));
                @unlink($path . $user->MID . '.jpg');
                $photo->receive();
                if ($photo->isReceived()) {
                    $img = PhpThumb_Factory::create($path . $user->MID . '.jpg');
                    $img->resize(HM_User_UserModel::PHOTO_WIDTH, HM_User_UserModel::PHOTO_HEIGHT);
                    $img->save($path . $user->MID . '.jpg');
                }
            }
        }

        return $user;
    }

    public function confirmEmailAction()
    {
        $hash = $this->_getParam('key');
        $userId = $this->_getParam('user_id');
        if ($user = $this->getService('User')->checkEmailConfirmationHash($hash, $userId)) {
            $this->getService('User')->updateWhere(array(
                'email_confirmed' => HM_User_UserModel::EMAIL_CONFIRMED,
                'blocked' => $this->getService('Option')->getOption('regAutoBlock') ? 1 : 0, // разблокируем если не предусмотрено ручное разблокирование
            ), array(
                'MID = ?' => $userId
            ));

            if (!$this->getService('Option')->getOption('regAutoBlock')) {
                $this->_flashMessenger->addMessage(_('Email успешно подтверждён'));

                try {
                    $this->getService('User')->authorize($user->Login, null, false, true); // авторизовать без пароля; сработает только один раз
                } catch(HM_Exception_Auth $e) {
                    // nope
                }

            } else {
                $this->_flashMessenger->addMessage(array(
                    'type'    => HM_Notification_NotificationModel::TYPE_SUCCESS,
                    'message' => _('Email успешно подтверждён. Вы сможете начать работу с системой после подтверждения администрацией.'))
                );
            }
        } else {
            $this->_flashMessenger->addMessage(array(
                'type'    => HM_Notification_NotificationModel::TYPE_ERROR,
                'message' => _('Email не подтверждён.'))
            );
        }
        $this->_redirector->gotoSimple('index', 'index', 'default');
    }

    private function _prepareForm(Zend_Form $form)
    {
        if ($this->getService('Option')->getOption('regRequireAgreement')) {
            $this->addContractOfferFields($form);
        }
        $form->removeElement('role');
        $form->removeElement('status');
        $form->removeElement('generatepassword');
        $form->removeElement('mid_external');

        $form->removeElement('gender');
        $form->removeElement('tel2');

        $form->removeDisplayGroup('Users3');

        if (!count($this->getService('Orgstructure')->fetchAll(array('type = ?' => HM_Orgstructure_OrgstructureModel::TYPE_DEPARTMENT)))) {
            $form->removeElement('position_id');
            $form->removeElement('position_name');
            $form->removeDisplayGroup('UserOrgstructure');
            }

        return $form;
    }

    /**
     * Экшен само-регистрации пользователя
     */
    public function selfAction()
    {

	    if ($this->getService('Option')->getOption('regDeny')) {
		    $this->_flashMessenger->addMessage(_('Свободная регистрация закрыта'));
		    $this->_redirector->gotoSimple('index', 'index', 'default');
	    }

        # установка заголовка
        $this->view->setHeader(_('Регистрация'));
        # настройка формы регистрации
        $form = new HM_Form_User();
        $this->_prepareForm($form);

        $elem = $form->getElement('cancelUrl');
        $elem->setOptions(array('Value' => $this->view->url(array(
            'module' => 'default',
            'controller' => 'index',
            'action' => 'index'
        ))));

        $elem = $form->getElement('userpassword');
        $elem->setOptions(array('Required' => true));

        if ($elem = $form->getElement('userpasswordrepeat')) {
            $elem->setOptions(array('Required' => true));
        }

        # обработка результатов формы
        if ($this->_request->isPost()) {
            $requestPost = $this->_request->getPost();
            //Генерация пароля при необходимости
            if ($requestPost["generatepassword"]) {
                $password = $this->getService('User')->getRandomString();
                $requestPost = array_merge($requestPost, ["userpassword" => $password, "userpasswordrepeat" => $password]);
            }
            if ($form->isValid($requestPost)) {
                $codeword = $form->getValue('codeword');
                $isCodewordValid = $this->validateCodeword($codeword);
                if (!$codeword || ($codeword && $isCodewordValid)) {
                    $user = $this->createUser($form);

                    if ($user) {
                        # Назначаем роль
                        $this->getService('User')->assignRole($user->MID, HM_Role_Abstract_RoleModel::ROLE_STUDENT);
                        //Отправляем уведомление по почте если необходимо
                        if ($requestPost["generatepassword"]) {
                            $messenger = $this->getService('Messenger');
                            $messenger->setOptions(HM_Messenger::TEMPLATE_PASS, array('login' => $user->Login, 'password' => $password));
                            $messenger->send(HM_Messenger::SYSTEM_USER_ID, $user->MID);
                            $this->_flashMessenger->addMessage(_('Пароль успешно отправлен на электронную почту.'));
                        }
                    }

                    if (!$this->getService('Option')->getOption('regValidateEmail')) {
                        $this->_flashMessenger->addMessage(_('Ваша регистрация успешно завершена'));

                        # авторизация нового пользователя
                        try {
                            $this->getService('User')->authorize($user->Login, $form->getValue('userpassword'));
                        } catch(HM_Exception_Auth $e) {
                            // nope
                        }

                    } else {
                        $this->_flashMessenger->addMessage(array(
                                'type'    => HM_Notification_NotificationModel::TYPE_CRIT,
                                'message' => _('Для завершения регистрации необходимо подтвердить email. На адрес, указанный в форме регистрации, отправлено письмо, содержащее ссылку для подтверждения.'))
                        );
                    }
                } elseif ($codeword && !$isCodewordValid) {
                    $this->_flashMessenger->addMessage(array(
                        'type'    => HM_Notification_NotificationModel::TYPE_CRIT,
                        'message' => _('Кодовое слово введено неверно.'))
                    );
                }

                $this->_redirector->gotoSimple('index', 'index', 'default');
            }
        }

         $this->replaceContractAgreeValue($form);
         $this->view->form = $form;

    }

    protected function validateCodeword($codeword)
    {
        return $codeword == $this->getService('Option')->getOption('codeword');
    }

    public function subjectAction()
    {
        $redirect = $this->_getParam('redirect');
        if ($redirect) {
            $this->view->setBackUrl($redirect = urldecode($redirect));
        }

        $subjectId = (int) $this->_getParam('subid', $this->_getParam('subject_id', 0));
        $programmId = (int) $this->_getParam('programm_id', 0);

        if (!$subjectId) {
            $this->_flashMessenger->addMessage(_('Не выбран учебный курс для регистрации'));
            $this->_redirector->gotoSimple('index', 'catalog', 'subject');
        }

        $subject = $this->getOne($this->getService('Subject')->find($subjectId));
        if (!$subject) {
            $this->_flashMessenger->addMessage(_('Учебный курс не найден'));
            $this->_redirector->gotoSimple('index', 'catalog', 'subject');
        }

        if (!in_array($subject->reg_type, array(HM_Subject_SubjectModel::REGTYPE_FREE, HM_Subject_SubjectModel::REGTYPE_SELF_ASSIGN))) {
            $this->_flashMessenger->addMessage(_('Данный учебный курс не имеет свободной регистрации'));
            $this->_redirector->gotoSimple('index', 'catalog', 'subject');
        }

        // Юзер уже зарегистрирован
        if ($this->getService('User')->getCurrentUserId()) {

            $this->getService('Subject')->assignUser($subjectId, $this->getService('User')->getCurrentUserId());

            $this->getService('Eclass')->subjectWebinarsReassign($subjectId);

            if ($subject->reg_type == HM_Subject_SubjectModel::REGTYPE_SELF_ASSIGN && $subject->claimant_process_id) {
                $this->_flashMessenger->addMessage(sprintf(_('Ваша заявка на учебный курс "%s" успешно отправлена'), $subject->name));
                $params = array('confirm_id' => null);

                $backUrl = urldecode($this->getParam('redirect'));
                if($backUrl) {
                    $this->_redirector->gotoUrl($backUrl);
                }
            } else {
                $this->_flashMessenger->addMessage(sprintf(_('Вы успешно зарегистрировались на учебный курс "%s"'), $subject->name));
                $this->_redirector->gotoUrl($subject->getDefaultUri());
            }

            if ($programmId > 0) {
                $this->_redirector->gotoSimple('index', 'list', 'subject', array('switcher'=>'programm'));
            }

            $this->getService('User')->switchRole(HM_Role_Abstract_RoleModel::ROLE_STUDENT); // переключаем на слушателя в текущей сессии;
            $messenger = $this->getService('Messenger');
            $messenger->sendAllFromChannels();
            $this->_redirector->gotoSimple('index', 'index', 'default', $params);
        }

        // Юзер не зареген

        $this->view->setHeader(sprintf(_('Регистрация на &laquo;%s&raquo;'), $subject->name));

        $form = new HM_Form_User();
        $this->_prepareForm($form);

        $elem = $form->getElement('cancelUrl');
        $elem->setOptions(array('Value' => $this->view->url(array(
            'module' => 'subject',
            'controller' => 'catalog',
            'action' => 'index'
        ))));

        $elem = $form->getElement('userpassword');
        $elem->setOptions(array('Required' => true));

        if ($elem = $form->getElement('userpasswordrepeat')) {
            $elem->setOptions(array('Required' => true));
        }

        $form->addElement('hidden', 'subject_id', array(
            'value' => $subjectId
        ));

        if ($this->_request->isPost()) {
            if ($form->isValid($this->_request->getPost())) {


                $user = $this->createUser($form);

                if ($user) {
                    # Назначаем на учебный курс
                    $this->getService('Subject')->assignUser($subjectId, $user->MID);

                    $this->getService('Eclass')->subjectWebinarsReassign($subjectId);
                }

                if ($subject->reg_type == HM_Subject_SubjectModel::REGTYPE_SELF_ASSIGN) {
                    $this->_flashMessenger->addMessage(sprintf(_('Ваша заявка на учебный курс "%s" успешно отправлена'), $subject->name));
                    $params = array('confirm_id' => null);
                } else {
                    $this->_flashMessenger->addMessage(sprintf(_('Вы успешно зарегистрировались на учебный курс "%s"'), $subject->name));
                    $params = array('confirm_id' => $subjectId);
                }

                try {
                    $this->getService('User')->authorize($user->Login, $form->getValue('userpassword'));
                } catch(HM_Exception_Auth $e) {
                    // nope
                }
                $messenger = $this->getService('Messenger');
                $messenger->sendAllFromChannels();

                $this->_redirector->gotoUrl($redirect ?: 'subject/catalog/index', $params);
            }
        }
        $infoblock_random_subjects_session = new Zend_Session_Namespace('infoblock_random_subjects');
        unset($infoblock_random_subjects_session->subjects);

        $this->replaceContractAgreeValue($form);
        $this->view->form = $form;

    }


    private function replaceContractAgreeValue($form)
    {
        $agreeCheck = $form->getElement('contract_agree');
        if ($agreeCheck) {
            $agreeCheck->setValue(0);
        }
    }

    private function addContractOfferFields(HM_Form_User $form)
    {
        $contractTexts = $this->getService('Option')->getOptions(HM_Option_OptionModel::SCOPE_CONTRACT);

        // Удаляем &nbsp; и управляющие ASCII-символы
        $sanitizedOffer = preg_replace('/[\x00-\x1F\x7F\xc2\xa0]/', '', trim(strip_tags(html_entity_decode($contractTexts['contractOfferText'] . $contractTexts['contractPersonalDataText']))));

        if (!empty($sanitizedOffer)) {
            $form->addElement(
                'hidden',
                'contract_agree'
            );

            $agreeCheck = $form->getElement('contract_agree');
            $agreeCheck->setLabel(
                'Нажимая кнопку «Сохранить», я соглашаюсь с ' .
                $this->view->dialogLink(
                    'Публичной офертой на оказание образовательных услуг',
                    [
                        'content' => $contractTexts['contractOfferText'],
                        'printUrl' => $this->view->url(['module' => 'contract', 'controller' => 'index', 'action' => 'print', 'contract' => 'offer']),
                        'title' => 'Публичная оферта на оказание образовательных услуг',
                    ]
                ) .
                    ' и даю ' .
                    $this->view->dialogLink(
                        'Согласие на обработку моих персональных данных',
                        [
                            'content' => $contractTexts['contractPersonalDataText'],
                            'printUrl' => $this->view->url(['module' => 'contract', 'controller' => 'index', 'action' => 'print', 'contract' => 'personal']),
                        ]
                    )
            );
            $agreeCheck->getDecorator('Label')
                ->setOptions($agreeCheck->getDecorator('Label')->getOptions() +
                    [
                        'escape' => false,
                        'style' => 'margin: 20px 0; display: block;',
                    ]);

            $agreeCheck->setOrder(10);

            $form->getElement('submit')->setOrder(20)->setAttrib('disabled', true);
        }
    }

    public function subjectsAction(){

        $postMassIds = $this->_getParam('postMassIds_grid', '');

        if (strlen($postMassIds) && $this->getService('User')->getCurrentUserId()) {
            $ids    = explode(',', $postMassIds);
            $params = array('confirm_id' => null);
            if (count($ids)) {
                foreach($ids as $subjectId) {

                    if (!$subjectId) continue;
                    $subject = $this->getOne($this->getService('Subject')->find($subjectId));
                    if (!$subject) continue;
                    if (!in_array($subject->reg_type, array(HM_Subject_SubjectModel::REGTYPE_FREE, HM_Subject_SubjectModel::REGTYPE_SELF_ASSIGN))) continue;
                    $this->getService('Subject')->assignUser($subjectId, $this->getService('User')->getCurrentUserId());

                    $this->getService('Eclass')->subjectWebinarsReassign($subjectId);

                    if (count($ids) == 1 && $subject->reg_type == HM_Subject_SubjectModel::REGTYPE_SELF_ASSIGN && !$subject->claimant_process_id) {
                        $params = array('confirm_id' => $subjectId);
                    }
                }

                if ( $this->getService('User')->getCurrentUserRole() == HM_Role_Abstract_RoleModel::ROLE_USER) {
                    $this->getService('User')->switchRole(HM_Role_Abstract_RoleModel::ROLE_STUDENT);
                }

                $messenger = $this->getService('Messenger');
                $messenger->sendAllFromChannels();
                $this->_flashMessenger->addMessage(_('Ваши заявки поданы успешно'));
                $this->_redirector->gotoSimple('index', 'catalog', 'subject', $params);
            }
        }

        // заполняем нового юзера

        $this->view->setHeader(_('Регистрация на учебные курсы'));

        $form = new HM_Form_User();
        if ($this->getService('Option')->getOption('regRequireAgreement')) {
            $this->addContractOfferFields($form);
        }
        $form->removeElement('role');
        $form->removeElement('status');
        $form->removeElement('generatepassword');
        $form->removeElement('mid_external');
        $form->removeDisplayGroup('Users3');

        $elem = $form->getElement('cancelUrl');
        $elem->setOptions(array('Value' => $this->view->url(array(
            'module' => 'subject',
            'controller' => 'catalog',
            'action' => 'index'
        ))));

        $elem = $form->getElement('userpassword');
        $elem->setOptions(array('Required' => true));

        $elem = $form->getElement('userpasswordrepeat');
        $elem->setOptions(array('Required' => true));

        $form->addElement('hidden', 'subjects', array(
            'value' => $postMassIds
        ));

        # регим нового юзера
        if ($this->_request->isPost() && !$this->_getParam('postMassIds_grid', '')) {
            if ($form->isValid($this->_request->getPost())) {

                $user = $this->createUser($form);

                if ($user) {
                    # Назначаем на учебный курс
                    $ids = explode(',', $form->getValue('subjects'));
                    $params = array('confirm_id' => null);
                    if (count($ids)) {
                        foreach($ids as $subjectId) {

                            if (!$subjectId) continue;
                            $subject = $this->getOne($this->getService('Subject')->find($subjectId));
                            if (!$subject) continue;
                            if (!in_array($subject->reg_type, array(HM_Subject_SubjectModel::REGTYPE_FREE, HM_Subject_SubjectModel::REGTYPE_SELF_ASSIGN))) continue;
                            $this->getService('Subject')->assignUser($subjectId, $user->MID);

                            $this->getService('Eclass')->subjectWebinarsReassign($subjectId);
                            if (count($ids) == 1 && $subject->reg_type == HM_Subject_SubjectModel::REGTYPE_SELF_ASSIGN && !$subject->claimant_process_id) {
                                $params = array('confirm_id' => $subjectId);
                            }
                        }
                    }
                }


                $this->_flashMessenger->addMessage(_('Ваши заявки на учебные курсы успешно отправлены'));

                try {
                    $this->getService('User')->authorize($user->Login, $form->getValue('userpassword'));
                } catch(HM_Exception_Auth $e) {
                    // nope
                }
                $messenger = $this->getService('Messenger');
                $messenger->sendAllFromChannels();
                $this->_redirector->gotoSimple('index', 'catalog', 'subject', $params);
            }
        }

        $this->replaceContractAgreeValue($form);
        $this->view->form = $form;

    }
    public function FilterString($stringInput)
    {
        return mb_convert_case(str_replace(" ","",trim($stringInput)),MB_CASE_TITLE,"UTF-8");
    }
}
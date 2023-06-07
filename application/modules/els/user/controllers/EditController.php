<?php

class User_EditController extends HM_Controller_Action_User {

    private function _prepareForm(Zend_Form $form, $user)
    {

        if ($this->getService('User')->getCurrentUserId()) {
            $form->removeElement('captcha');
            $form->removeDisplayGroup('CaptchaBlock');
        }

        //Удаляем административные поля
        if ($this->getService('Acl')->inheritsRole($this->getService('User')->getCurrentUserRole(), HM_Role_Abstract_RoleModel::ROLE_ENDUSER)) {
            $form->removeElement('role');
            $form->removeElement('status');
            $form->removeElement('generatepassword');
            $form->removeElement('mid_external');
            $form->removeDisplayGroup('Users3');

            $form->removeElement('position_id');
            $form->removeElement('position_name');
            $form->removeDisplayGroup('UserOrgstructure');
        }

        $elem = $form->getElement('cancelUrl');
        $elem->setOptions(array('Value' => $this->view->url(array(
                'module' => 'user',
                'controller' => 'edit',
                'action' => 'card',
                'user_id' => $user->MID
            )))
        );

        $elem = $form->getElement('userpassword');
        $elem->setOptions(array('Required' => false));

        //removeValidator
        $elem = $form->getElement('userlogin');
        $elem->removeValidator('Db_NoRecordExists');

        $elem->addValidator('Db_NoRecordExists', false, array('table' => 'People',
            'field' => 'Login',
            'exclude' => array(
                'field' => 'MID',
                'value' => $user->MID
            )
                )
        );

        $elem = $form->getElement('user_id');
        $elem->addValidator('Db_RecordExists', false, array('table' => 'People',
            'field' => 'MID'
                )
        );

        // Убираем редактирование логина и пароля для пользователя из AD
        if ($user->isAD) {
            $user->prepareFormLdap($form, $this);
        }
    }

    public function indexAction() {

        $this->view->setSubSubHeader(_('Редактирование'));

        if (!$this->_userId) {
            $this->_userId = $this->getService('User')->getCurrentUserId();
        }
        if ($this->_userId != $this->getService('User')->getCurrentUserId()) {
            if (!$this->getService('Acl')->isCurrentAllowed(HM_Acl::RESOURCE_USER_CONTROL_PANEL, HM_Acl::PRIVILEGE_EDIT)) {
                    throw new HM_Permission_Exception(_('Не хватает прав доступа.'));
            }
        }

        $userId = $this->_userId;

        $user = $this->getOne($this->getService('User')->findDependence('Position', $userId));
        $arrayPast = array(
            'FirstName' => $user->FirstName,
            'LastName' => $user->LastName,
            'Patronymic' => $user->Patronymic,
            'mid_external' => $user->mid_external,
        );
        if (!$user) {
            $this->_flashMessenger->addMessage(array('message' => _('Пользователь не найден'), 'type' => HM_Notification_NotificationModel::TYPE_ERROR));
            $this->_redirector->gotoSimple('index', 'index', 'default');
        }

        $form = new HM_Form_User();

        $this->_prepareForm($form, $user);

        // нехороший код, продублирован в ListController
        if ($this->_request->isPost()) {
            if ($form->isValid($this->_request->getPost())) {
                $disabledArray = ($user->isAD) ? $user->getLdapDisabledFormFields() : array();

                $array = array(
                    'MID' => $userId,
                    'email' => $form->getValue('email'),
                    'need_edit' => 0,
                    'blocked' => $form->getValue('status'),
                );

                $formValuesArray = array(
                    'userlogin' => 'Login',
                    'userpassword' => 'Password',
                    'firstname' => 'FirstName',
                    'lastname' => 'LastName',
                    'patronymic' => 'Patronymic',
                    'mid_external' => 'mid_external',
                   'birthdate' => 'BirthDate',
                    'gender' => 'gender',
                    'lastnameLat' => 'LastNameLat',
                    'firstnameLat' => 'FirstNameLat',
                   'tel' => 'Phone',
                   'tel2' => 'Fax',
                );

                $checkNotEmpty = array(
                    'lastnameLat' => 'LastNameLat',
                    'firstnameLat' => 'FirstNameLat',
                );

                $checkedValuesArray = array_intersect_key(
                        $formValuesArray, array_flip(array_diff(array_keys($formValuesArray), $disabledArray))
                );
                foreach ($checkedValuesArray as $field => $title) {
                    if (null !== ($value = $form->getValue($field))) {
                        /**
                         * @todo Нужно избавиться от следующей проверки.
                         */
                        if (in_array($field, $checkNotEmpty)) {
                            if (!strlen($value)) {
                                continue;
                            }
                        }
                        $array[$title] = $value;
                    }
                }

                //шифруем пароль
                if (empty($array['Password']))
                    unset($array['Password']);
                else
                    $array['Password'] = new Zend_Db_Expr("PASSWORD('" . $array['Password'] . "')");

                if (isset($array['BirthDate'])) {
                    $dateArr = explode('.', $array['BirthDate']);
                    $array['BirthDate'] = $dateArr[2].'-'.$dateArr[1].'-'.$dateArr[0];
                }

                $user = $this->getService('User')->update($array);
//				$claimant = $this->getService('Claimant')->updateClaimant();

                // Сохраняем метаданные
                if ($user) {
                    $this->getService('User')->updateAdditionalFields($user->MID, $form);
// DEPRECATED!
//                     $user->setMetadataValues(
//                         $this->getService('User')->getMetadataArrayFromForm($form)
//                     );

//                     $user = $this->getService('User')->update($user->getValues());

                    if (count($classifiers = $form->getClassifierValues())) {
                        $this->getService('Classifier')->unlinkItem($user->MID, HM_Classifier_Link_LinkModel::TYPE_PEOPLE);
                        if (is_array($classifiers) && count($classifiers)) {
                            foreach ($classifiers as $classifierId) {
                                if ($classifierId > 0) {
                                    $this->getService('Classifier')->linkItem($user->MID, HM_Classifier_Link_LinkModel::TYPE_PEOPLE, $classifierId);
                                }
                            }
                        }
                    }

                    $atProfileId = $form->getValue('position_name');
                    if ($ownerSoid = $form->getValue('position_id')) {
                        $positionName = $this->getService('AtProfile')->findOne($atProfileId)->name;
                        $this->getService('Orgstructure')->assignUser($user->MID, $ownerSoid, $positionName, $atProfileId);
                    }

                }

                $post = $this->_request->getParams();
                $photo = $form->getElement('photo');
                if ($photo->isUploaded()) {
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
				else
            	if($post['photo_delete'])	{
                    $path = $this->getService('User')->getPath(Zend_Registry::get('config')->path->upload->photo, $user->MID);
                    @unlink($path . $user->MID . '.jpg');
				}

                // set classifiers
                /*                $classifiers = $form->getClassifierValues();
                  $this->getService('ClassifierLinks')->setClassifiers($userId, HM_Classifier_Link_LinkModel::TYPE_PEOPLE, $classifiers); */

                //Обрабатываем область ответственности - wtf??

                if ($user->MID > 0
                        && $this->getService('Acl')->inheritsRole($this->getService('User')->getCurrentUserRole(), HM_Role_Abstract_RoleModel::ROLE_ADMIN)
                        //&& $this->getService('User')->getCurrentUserRole() == HM_Role_Abstract_RoleModel::ROLE_ADMIN
                        && $this->getService('User')->isRoleExists($user->MID, HM_Role_Abstract_RoleModel::ROLE_DEAN)) {
                    $this->getService('Dean')->setResponsibilityOptions($user->MID, $form->getValue('unlimited_subjects'), $form->getValue('assign_new'));
                    if ($form->getValue('unlimited_subjects') == 1) {
                        $this->getService('Dean')->deleteBy(array('MID = ?' => $user->MID));
                        $this->getService('Dean')->insert(array('MID' => $user->MID, 'subject_id' => 0));
                    }
                }

                // метки
                // Изменять метки может только администратор
                if ($user->MID > 0
                        && $this->getService('Acl')->inheritsRole(
                                $this->getService('User')->getCurrentUserRole(), HM_Role_Abstract_RoleModel::ROLE_ADMIN)) {

                    $tags = array_unique($form->getParam('tags', array()));
                    $tags = $this->getService('Tag')->updateTags($tags, $user->MID, $this->getService('TagRef')->getUserType());
                    $this->getService('StudyGroup')->addUserByTags($user->MID, $tags);
                }

                if ($user && !empty($array['Password'])) {
                    // Шлём сообщение о смене пароля
                    $messenger = $this->getService('Messenger');
                    $messenger->setOptions(
                            HM_Messenger::TEMPLATE_PASS, array(
                        'login' => $user->Login,
                        'password' => $form->getValue('userpassword')
                            )
                    );
                    $messenger->send(HM_Messenger::SYSTEM_USER_ID, $user->MID);
                }

                $user->role = $this->getService('User')->getCurrentUserRole();
                if ($this->getService('User')->getCurrentUserId() == $user->MID) {
                    $this->getService('User')->initUserIdentity($user);
                    $s = new Zend_Session_Namespace('s');
                    $s->login = $user->Login;
                    $s->user['lname'] = $user->LastName;
                    $s->user['fname'] = $user->FirstName;
                    $s->user['patronymic'] = $user->Patronymic;
                    $this->_flashMessenger->addMessage(_('Данные пользователя успешно изменены'));
                    $this->_redirector->gotoSimple('card', 'edit', 'user', array('user_id' => $user->MID, 'report' => 1));
                } else {
                    $this->_flashMessenger->addMessage(_('Личные данные успешно изменены!'));
                    $this->_redirector->gotoSimple('card', 'edit', 'user', array('user_id' => $user->MID, 'report' => 1));
                }
            } else {
                $elem = $form->getElement('photo');
                $elem->setOptions(array('user_id' => $userId));
                // $form->populate($arr);
                $arr = array(
                    'userlogin' => $user->Login,
                    'firstname' => $user->FirstName,
                    'lastname' => $user->LastName,
                    'patronymic' => $user->Patronymic,
                    'firstnameLat' => $user->FirstNameLat,
                    'lastnameLat' => $user->LastNameLat,
                    'email' => $user->EMail,
                    'tel' => $user->Phone,
                    'tel2' => $user->Fax,
                    'status' => $user->blocked,
                    'user_id' => $userId,
                    'mid_external' => $user->mid_external
                );

                if (count($user->positions)) {
                    $position = $user->positions->current();
                    $arr['position_id'] = $position->soid;
                    $arr['position_name'] = $position->profile_id;
                }

                // чтобы тэги не сбрасывались после неуспешной валидации
                $post = $this->_request->getParams();
                $post['tags'] = $this->getService('Tag')->convertAllToStrings($post['tags']);
                $form->populate(array_merge($arr, $post));
            }
        } else {

            $birthDate = new HM_Date($user->BirthDate);
            $arr = array(
                'userlogin' => $user->Login,
                'firstname' => $user->FirstName,
                'lastname' => $user->LastName,
                'patronymic' => $user->Patronymic,
                'firstnameLat' => $user->FirstNameLat,
                'lastnameLat' => $user->LastNameLat,
                'gender' => $user->Gender,
                'birthdate' => $birthDate->toString('dd.MM.yyyy'),
                'email' => $user->EMail,
                'tel' => $user->Phone,
                'tel2' => $user->Fax,
                'status' => $user->blocked,
                'user_id' => $userId,
                'mid_external' => $user->mid_external
            );

            $arr = array_merge($arr, $this->getService('User')->getAdditionalFields($user->MID));
            if (count($user->positions)) {
                $position = $user->positions->current();
                $arr['position_id'] = $position->soid;
                $arr['position_name'] = $position->profile_id;
            }

            $metadata = $user->getMetadataValues();
            if (count($metadata)) {
                foreach ($metadata as $name => $value) {
                    $arr[$name] = $value;
                }
            }

            $elem = $form->getElement('photo');
            $elem->setOptions(array('user_id' => $userId));
            $form->populate($arr);
            $this->view->user = $user;
        }
        $this->view->form = $form;
    }

    public function cardAction()
    {
        $params = ['MID' => $this->_userId];
        if ($this->_getParam('recruit_candidate', 0)) {
            $params['recruit_candidate'] = 1;
        }
        if ($this->_request->getParam('report', 0)) {
            $params['user_id'] = $params['MID'];
            unset($params['MID']);
            $this->_redirector->gotoSimple('index', 'report', 'user', $params);
        } else {
            $this->_redirector->gotoSimple('edit', 'list', 'user', $params);
        }
    }

	public function filterString($stringInput)
	{
        return mb_convert_case(str_replace(" ", "", trim($stringInput)), MB_CASE_TITLE, "UTF-8");
    }

}


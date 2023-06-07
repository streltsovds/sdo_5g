<?php
class User_ListController extends HM_Controller_Action
{
    use HM_Controller_Action_Trait_Grid;

    protected $departmentCache = array();

    /**
     * Стандартная функция displayTags делает очень много запросов в БД
     *
     * @param int      $itemId
     * @param string   $itemType
     * @param Bvb_Grid $grid
     *
     * @return string
     */
    public function displayTagsForGrid($itemId, $itemType, $grid)
    {
        static $tagsCache = null;

        $tagService = $this->getService('Tag');

        if ($tagsCache === null) {
            $result = $grid->getResult();
            $mids = array();

            foreach ($result as $raw) {
                $mids[$raw['MID']] = $raw['MID'];
            }

            $tagsCache = $tagService->getTagsCache($mids, $itemType);
        }

        $arResult = isset($tagsCache[$itemId]) ? $tagsCache[$itemId] : array();

        if (!count($arResult)) {
            return '';
        }

        asort($arResult);

        //форматирование в раскрывающийся список

        $txt = (count($arResult) > 1) ? '<p class="total">'. $tagService->pluralTagCount(count($arResult)) . '</p>' : '';

        foreach ($arResult as $item) {
            $txt .= "<p>$item</p>";
        }

        return $txt;
    }

    /**
     * Экшн для списка пользователей
     * @throws Zend_Exception
     */
    public function indexAction()
    {
        if (!$this->isGridAjaxRequest() && $this->_request->getParam('statusgrid') == "")
            $this->_request->setParam('statusgrid', null);

        $this->getSwitcherSetOrder(null, 'fio_ASC', 'notempty DESC');
        $this->dataGrid = new HM_User_DataGrid_UserListDataGrid($this->view);
        Zend_Registry::get('session_namespace_default')->userCard['returnUrl'] = $_SERVER['REQUEST_URI'];
    }

    public function setPasswordAction()
    {
        $ids = explode(',', $this->_request->getParam('postMassIds_grid'));
        $pass = $this->_getParam('pass', '');

        /** @var HM_User_Password_PasswordService $passwordService */
        $passwordService = $this->getService('UserPassword');
        $userService = $this->getService('User');

        $userAdapter = $userService->getSelect()->getAdapter();

        if ($errors = $passwordService->checkPassword($pass)) {
            // Можно уточнять через вывод $errors
            $this->_flashMessenger->addMessage([
                'message' => _('Пароль не соответствует требованиям парольной политики'),
                'type' => HM_Notification_NotificationModel::TYPE_CRIT
            ]);
            $this->_redirector->gotoSimple('index', 'list', 'user');
        } else {
            foreach ($ids as $id) {
                $data = array(
                    'Password' => new Zend_Db_Expr("PASSWORD(" . $userAdapter->quote($pass) . ")"),
                    'MID' => $id,
                );
                $user = $userService->update($data, true);
                // Отправить сообщение
                if ($user) {
                    $messenger = $this->getService('Messenger');
                    $messenger->setOptions(HM_Messenger::TEMPLATE_PASS, array('login' => $user->Login, 'password' => $pass));
                    $messenger->send(HM_Messenger::SYSTEM_USER_ID, $id);
                }
            }
            $this->_flashMessenger->addMessage(_('Пароль успешно назначен!'));
        }

        $this->_redirector->gotoSimple('index', 'list', 'user');
    }



    public function deparmentFilter($data)
    {
        $field = $data['field'];
        $value = $data['value'];
        $select = $data['select'];

        // Только больше 4 символов чтобы много не лезло в in
        if (strlen($value) > 4) {
            $fetch = $this->getService('Orgstructure')->fetchAll(array('name LIKE LOWER(?)' => "%" . $value . "%"));

            $data = $fetch->getList('soid', 'name');
            $select->where('d.owner_soid IN (?)', array_keys($data));
        }
    }

    public function roleFilter($data)
    {
        $value=$data['value'];
        $select=$data['select'];
        if (!empty($value)) {
            $select->joinInner(array('rs'=>'roles_source'),$this->quoteInto('rs.user_id = t1.MID AND rs.role = ?', $value),array());
        }
    }

    public function positionFilter($data)
    {

        $field = $data['field'];
        $value = $data['value'];
        $select = $data['select'];

        // Только больше 4 символов чтобы много не лезло в in
        if (strlen($value) > 4) {
            $fetch = $this->getService('Orgstructure')->fetchAll(array('name LIKE LOWER(?)' => "%" . $value . "%"));

            $data = $fetch->getList('soid', 'name');
            $select->where('d.soid IN (?)', array_keys($data));
        }
    }





    /**
     * Экшен снятия ролей с юзера
     */
    public function unassignAction()
    {
        $arRoles = HM_Role_Abstract_RoleModel::getBasicRoles();
        $ids = explode(',', $this->_request->getParam('postMassIds_grid'));
        $role = $this->_request->getParam('role', array());
        $service = $this->getService('User');

        foreach ($ids as $value) {
            if (array_key_exists($role, $arRoles)) {
                $service->removalRole($value, $role);
            }
        }

        $this->_flashMessenger->addMessage(_('Роли успешно убраны'));
        $this->_redirector->gotoSimple('index', 'list', 'user');
    }


    /**
     * Экшн для присваивания ролей
     */
    public function assignAction() {

        $ids = explode(',', $this->_request->getParam('postMassIds_grid'));
        $role = $this->_request->getParam('role');

        /** @var HM_User_UserService $service */
        $service = $this->getService('User');
        // Флаг, есть ли ошибки
        $error = false;
        foreach ($ids as $value)
            foreach ($role as $singleRole)
                if (false === $service->assignRole($value, $singleRole)) $error = true;

        if ($error === true) {
            $this->_flashMessenger->addMessage(_('Некоторым пользователям уже была присвоена роль '));
        } else {
            $this->_flashMessenger->addMessage(_('Пользователям успешно добавлена роль '));
        }
        $this->_redirector->gotoSimple('index', 'list', 'user');

    }


    /**
     * Экшн для блокировки
     */
    public function blockAction() {
        $ids = explode(',', $this->_request->getParam('postMassIds_grid'));
        // Нельзя заблокировать себя
        if ($key = array_search($this->getService('User')->getCurrentUserId(), $ids)) {
            unset($ids[$key]);
        }

        $array = array('blocked' => 1);
        $res = $this->getService('User')->updateWhere($array, array('MID IN (?)' => $ids));
        if ($res > 0) {
            $this->_flashMessenger->addMessage(_('Пользователи успешно заблокированы!'));
            $this->_redirector->gotoSimple('index', 'list', 'user');
        } else {
            $this->_flashMessenger->addMessage(_('Произошла ошибка во время блокировки пользователей!'));
            $this->_redirector->gotoSimple('index', 'list', 'user');
        }
    }

    /**
     * Экшн для разблокировки
     */
    public function unblockAction() {
        $ids = explode(',', $this->_request->getParam('postMassIds_grid'));

        $array = array('blocked' => 0);

        $res = $this->getService('User')->updateWhere($array, array('MID IN (?)' => $ids));
        if ($res > 0) {
            $url = 'http://' . $_SERVER['SERVER_NAME'] . Zend_Registry::get('config')->url->base;
            $this->getService('User')->notifyUserUnblock(
                array(
                    'id' => $ids,
                    'placeholders' => array(
                        'URL' =>  '<a href="' . $url . '">' . $url . '</a>'
                    ),
                )
            );
            $this->_flashMessenger->addMessage(_('Пользователи успешно разблокированы!'));
            $this->_redirector->gotoSimple('index', 'list', 'user');
        } else {
            $this->_flashMessenger->addMessage(_('Произошла ошибка во время разблокировки пользователей!'));
            $this->_redirector->gotoSimple('index', 'list', 'user');
        }
    }

    /**
     * Экшн для удаления
     */
    public function deleteAction() {
        $userId = (int) $this->_getParam('MID', 0);
        if ($userId) {
            if ($userId == $this->getService('User')->getCurrentUserId()) {
                $this->_flashMessenger->addMessage(_('Вы не можете удалить сами себя'));
                $this->_redirector->gotoSimple('index', 'list', 'user');
            }
            $this->getService('User')->delete($userId);
        }
        $this->_flashMessenger->addMessage(_('Пользователь успешно удалён'));
	 if ($this->getService('Acl')->inheritsRole($this->getService('User')->getCurrentUserRole(), array(HM_Role_Abstract_RoleModel::ROLE_HR, HM_Role_Abstract_RoleModel::ROLE_HR_LOCAL))) {
        $this->_redirector->gotoUrl($this->view->url(array('module' => 'candidate', 'controller' => 'list', 'action' => 'index', 'baseUrl' => 'recruit', 'MID' => null)));
} else {
        $this->_redirector->gotoSimple('index', 'list', 'user');
    }
    }

    /**
     * Экшн для массового удаления
     */
    public function deleteByAction() {
        $ids = explode(',', $this->_request->getParam('postMassIds_grid'));
        $service = $this->getService('User');
        foreach ($ids as $value) {

            if ($value != $this->getService('User')->getCurrentUserId()) {
                $service->delete(intval($value));
            } else {
                $this->_flashMessenger->addMessage(_('Вы не можете удалить себя!'));
                $this->_redirector->gotoSimple('index', 'list', 'user');
            }
        }
        $this->_flashMessenger->addMessage(_('Пользователи успешно удалены'));
        $this->_redirector->gotoSimple('index', 'list', 'user');
    }

    /**
     * Экшн для создания пользователя
     */
    public function newAction()
    {
        $this->view->setSubHeader(_('Создание учетной записи'));
        $form = new HM_Form_User();
        $this->_prepareNewForm($form);

        if ($this->_getParam('generatepassword', 0) == 1) {
            $password = $this->getService('User')->getRandomString();
            $this->_setParam('userpassword', $password);
            $this->_setParam('userpasswordrepeat', $password);
        }

        if ($this->_request->isPost()) {
            if ($form->isValid($this->_request->getParams())) {

                // Дубли email не допускаем
                $duplicateEmailUser = $this->getService('User')->getOne($this->getService('User')->fetchAll(array('EMail = ?' => $form->getValue('email'))));
                if ($duplicateEmailUser) {
                    $this->_flashMessenger->addMessage(array(
                        'type' => HM_Notification_NotificationModel::TYPE_ERROR,
                        'message' => _('Учетная запись с таким электронным адресом уже есть в системе.')
                    ));
                    $this->_redirector->gotoSimple('new', 'list', 'user');
                }

                $array = array('login' => $form->getValue('userlogin'),
                    //'password' => $form->getValue('userpassword'),
                    'firstname' => $this->filterString($form->getValue('firstname')),
                    'lastname' => $this->filterString($form->getValue('lastname')),
                    'patronymic' => $this->filterString($form->getValue('patronymic')),
                    'email' => $form->getValue('email'),
                    'blocked' => $form->getValue('status'),
                );

                if ($this->getService('Acl')->inheritsRole($this->getService('User')->getCurrentUserRole(), array(HM_Role_Abstract_RoleModel::ROLE_HR, HM_Role_Abstract_RoleModel::ROLE_HR_LOCAL))) {
                    $array['blocked'] = HM_User_UserModel::BLOCKED_ON;
                }

                $array += array('Password' => new Zend_Db_Expr("PASSWORD(" . $this->getService('User')->getSelect()->getAdapter()->quote($form->getValue('userpassword')) . ")"));

                if (null !== $form->getValue('mid_external')) {
                    $array+= array('mid_external' => $form->getValue('mid_external'));
                }

                if (null !== $form->getValue('lastnameLat')) {
                    $array+= array('LastNameLat' => $form->getValue('lastnameLat'));
                }

                if (null !== $form->getValue('firstnameLat')) {
                    $array+= array('FirstNameLat' => $form->getValue('firstnameLat'));
                }

                if (null !== $form->getValue('gender')) {
                    $array+= array('Gender' => $form->getValue('gender'));
                }

                // нет необходимости использовать Metadata для сохранения одного лишь телефона
                // пишем и в Phone и в Information для обратной совместимости
                // правильное поле - Phone
                if (null !== $form->getValue('tel')) {
                    $array+= array('Phone' => $form->getValue('tel'));
                }

                if (null !== $form->getValue('tel2')) {
                    $array+= array('Fax' => $form->getValue('tel2'));
                }

                if ($form->getValue('birthdate')) {
                    $dateArr = explode('.', $form->getValue('birthdate'));
                    $array['BirthDate'] = $dateArr[2].'-'.$dateArr[1].'-'.$dateArr[0];
                }

                $user = $this->getService('User')->duplicateInsert($array);
//                $claimant = $this->getService('Claimant')->updateClaimant();
                // Добавляем метаданные
                if ($user) {
                    $this->getService('User')->updateAdditionalFields($user->MID, $form);

                    $user->setMetadataValues(
                        $this->getService('User')->getMetadataArrayFromForm($form)
                    );

                    $user = $this->getService('User')->update($user->getValues());

                    $classifiers = $form->getClassifierValues();
                    $this->getService('Classifier')->unlinkItem($user->MID, HM_Classifier_Link_LinkModel::TYPE_PEOPLE);
                    if (is_array($classifiers) && count($classifiers)) {
                        foreach($classifiers as $classifierId) {
                            if ($classifierId > 0) {
                                $this->getService('Classifier')->linkItem($user->MID, HM_Classifier_Link_LinkModel::TYPE_PEOPLE, $classifierId);
                            }
                        }
                    }

                    $profileId = (int) $form->getValue('position_name');
                    $profile = $this->getService('AtProfile')->find($profileId);
                    $positionName = count($profile) ? $profile->current()->name : '';
                    if ($ownerSoid = $form->getValue('position_id')) {
                        $this->getService('Orgstructure')->assignUser($user->MID, $ownerSoid, $positionName, $profileId);
                    }

                }

                // Обрабатываем фотку
                $photo = $form->getElement('photo');
                if ($photo->isUploaded()) {
                    $path = $this->getService('User')->getPath(Zend_Registry::get('config')->path->upload->photo, $user->MID);
                    $photo->addFilter('Rename', $path . $user->MID . '.jpg', 'photo', array( 'overwrite' => true));
                    $photo->receive();
                    $img = PhpThumb_Factory::create($path . $user->MID . '.jpg');
                    $img->resize(HM_User_UserModel::PHOTO_WIDTH, HM_User_UserModel::PHOTO_HEIGHT);
                    $img->save($path . $user->MID . '.jpg');
                }



                // Добавляем роль если такая существует
                if (array_key_exists($form->getValue('role'), HM_Role_Abstract_RoleModel::getBasicRoles(false))) {
                    $this->getService('User')->assignRole($user->MID, $form->getValue('role'));
                }

                // метки
                $tags = array_unique($form->getParam('tags', array()));
                $tags = $this->getService('Tag')->updateTags(
                    $tags,
                    $user->MID,
                    $this->getService('TagRef')->getUserType(),
                    false
                );
                $this->getService('StudyGroup')->addUserByTags($user->MID, $tags);

                if ($user) {

                    // Шлём сообщение о регистрации
                    $messenger = $this->getService('Messenger');
                    $messenger->setOptions(
                        HM_Messenger::TEMPLATE_REG,
                        array(
                            'fio' => $user->getNameCyr(),
                            'login' => $user->Login,
                            'password' => $form->getValue('userpassword')
                        )
                    );
                    $messenger->send(HM_Messenger::SYSTEM_USER_ID, $user->MID);
                }

                if ($this->getService('Acl')->inheritsRole($this->getService('User')->getCurrentUserRole(), array(HM_Role_Abstract_RoleModel::ROLE_HR, HM_Role_Abstract_RoleModel::ROLE_HR_LOCAL))) {

                    $vacancyId = (int) $this->_request->getParam('vacancy_id', 0);

                    // для рекрутера создаем кандидата и делаем редирект на страницу кандидатов
                    $values = array(
                        'source' => $form->getValue('provider_id'),
                        'user_id' => $user->MID
                    );

                    if (null !== $form->getValue('resume_html')) {
                        $values += array('resume_html' => $form->getValue('resume_html'));
                    }

                    $candidate = $this->getService('RecruitCandidate')->insert($values);
                    $candidate->setAutoBlocked();

                    $candidateId = $candidate->candidate_id;

                    if ($resumeFile = $form->getElement('resume_file')) {
                        $this->receiveResume($resumeFile, $candidateId);
                    }

                    $url = $this->view->url(array(
                        'baseUrl'    => 'recruit',
                        'module'     => 'candidate',
                        'controller' => 'assign',
                        'action'     => 'index',
                    ));


                    $vacancy = $this->getService('RecruitVacancy')->getOne($this->getService('RecruitVacancy')->findDependence('RecruiterAssign', $vacancyId));
                    $vacancyCandidates = $this->getService('RecruitVacancyAssign')->fetchAllDependence(array('Candidate', 'Vacancy'), array(
                        'vacancy_id = ?' => $vacancyId
                    ));

                    // Status по умолчанию?
                    $status = HM_Recruit_Vacancy_Assign_AssignModel::STATUS_ACTIVE;

                    $vacancyCandidate = $this->getService('RecruitVacancyAssign')->assign($vacancyId, $candidateId, $status);

                    if (!count($vacancyCandidates) && $vacancyCandidate) {
                        $session = $this->getService('AtSession')->getOne($this->getService('AtSession')->find(intval($vacancy->session_id)));
                        $this->getService('RecruitVacancy')->startSession($vacancy, $session);

                    }


                    $this->_flashMessenger->addMessage(_('Учётная запись кандидата создана успешно'));
                    $this->_redirector->gotoUrlAndExit($url);

                } else {
                    $this->_flashMessenger->addMessage(_('Учётная запись создана успешно!'));
                    $this->_redirector->gotoSimple('index', 'list', 'user');
                }
            } else {
                $form->populate($this->_request->getParams());
            }
        }

        $form->populate(array('status' => 0));
        $this->view->form = $form;
    }

    private function _prepareEditForm(Zend_Form $form, $user)
    {
//        $form->removeElement('provider_id');
//        $form->removeDisplayGroup('source');

        if ($elem = $form->getElement('generatepassword')) {
            $elem->setValue(false);
        }

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

        if ($this->_getParam('generatepassword') == 1) {
            $password = $this->getService('User')->getRandomString();
            $this->_setParam('userpassword', $password);
            $this->_setParam('userpasswordrepeat', $password);
        }

        // Убираем редактирование логина и пароля для пользователя из AD
        if ($user->isAD) {
            $user->prepareFormLdap($form, $this);
        }

        $form->removeElement('captcha');
        $form->removeDisplayGroup('CaptchaBlock');
    }

    protected function _prepareNewForm($form)
    {
        $currentUser = $this->getService('User')->getCurrentUser();

        if ($currentUser->role === HM_Role_Abstract_RoleModel::ROLE_HR) {

            $form->removeDisplayGroup('Users3');
            $form->removeElement('status');
            $form->removeElement('role');

            $form->removeDisplayGroup('UserOrgstructure');
            $form->removeElement('position_id');
            $form->removeElement('position_name');

            if ($form->getElement('generatepassword')) $form->getElement('generatepassword')->setValue(false);
            if ($form->getElement('userpassword')) $form->getElement('userpassword')->setOptions(array('Required' => false));
            if ($form->getElement('userpasswordrepeat')) $form->getElement('userpasswordrepeat')->setOptions(array('Required' => false));
        }

    }

    /**
     * Экшн для редактирования пользователя
     */
    public function editAction()
    {
        $this->view->setSubHeader(_('Редактирование учетной записи'));
        /** @var HM_User_UserService $userService */
        $userService = $this->getService('User');

        /** @var HM_Acl $aclService */
        $aclService = $this->getService('Acl');

        $userId = (int) $this->_request->getParam('MID', 0);
        $candidateId = (int) $this->_request->getParam('candidate_id', 0);
        $candidateService = $this->getService('RecruitCandidate');
        $currentUserId = $userService->getCurrentUserId();

        if(($aclService->checkRoles(HM_Role_Abstract_RoleModel::ROLE_ENDUSER) || $aclService->checkRoles(HM_Role_Abstract_RoleModel::ROLE_DEAN, HM_Role_Abstract_RoleModel::ROLE_DEAN_LOCAL)) && $userId !== $currentUserId) {
            $this->_flashMessenger->addMessage([
                'message' => _('Недостаточно прав'),
                'type' => HM_Notification_NotificationModel::TYPE_ERROR
            ]);

            $this->_redirector->gotoSimple('index', 'index', 'default');
        }
        
        /** @var HM_User_UserModel $user */
        $user = $this->getOne($userService->find($userId));
        if (!$user) {
            $this->_flashMessenger->addMessage(_('Пользователь не найден'));
            $this->_redirector->gotoSimple('index', 'list', 'user');
        }

        $form = new HM_Form_User();

        if (!$candidateId) {
            $form->removeElement('resume_file');
            $form->removeElement('resume_html');
            $form->removeDisplayGroup('resumeGroup');

            $form->removeElement('provider_id');
            $form->removeDisplayGroup('source');

        } else {
            $candidate = $candidateService->findOne($candidateId);
            if ($candidate->isJsonResume()) {
                $form->removeElement('resume_file');
                $form->removeElement('resume_html');
                $form->removeDisplayGroup('resumeGroup');
            }
        }
        $this->_prepareEditForm($form, $user);

        if ($this->_request->isPost()) {

            if ($form->isValid($this->_request->getParams())) {

                // Дубли email не допускаем
                $duplicateEmailUser = $this->getService('User')->getOne($this->getService('User')->fetchAll(array(
                    'EMail = ?' => $form->getValue('email'),
                    'MID != ?' => $userId
                )));
                if ($duplicateEmailUser) {
                    $this->_flashMessenger->addMessage(array(
                        'type' => HM_Notification_NotificationModel::TYPE_ERROR,
                        'message' => _('Учетная запись с таким электронным адресом уже есть в системе.')
                    ));
                    $this->_redirector->gotoSimple('edit', 'list', 'user', [
                        'gridmod' => $this->_request->getParam('gridmod'),
                        'MID' => $userId
                    ]);
                }

                $disabledArray = ($user->isAD)? $user->getLdapDisabledFormFields() : array();

                $array = array(
                    'MID' => $userId,
                    'email' => $form->getValue('email'),
                    'blocked' => $form->getValue('status'),
                    'need_edit' => 0
                );

                $formValuesArray = array (
                   'userlogin' => 'Login',
                   'firstname' => 'FirstName',
                   'lastname' => 'LastName',
                   'patronymic' => 'Patronymic',
                   'userpassword' => 'Password',
                   'mid_external' => 'mid_external',
                   'gender' => 'gender',
                   'birthdate' => 'BirthDate',
                   'lastnameLat' => 'LastNameLat',
                   'firstnameLat' => 'FirstNameLat',
                   'tel' => 'Phone',
                   'tel2' => 'Fax',
                );

                $checkNotEmpty = array (
                   'lastnameLat' => 'LastNameLat',
                   'firstnameLat' => 'FirstNameLat',
                );

                $checkedValuesArray = array_intersect_key(
                    $formValuesArray,
                    array_flip(array_diff(array_keys($formValuesArray), $disabledArray))
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

                if (!empty($array['BirthDate'])) {
                    $array['BirthDate'] = date('Y-m-d', strtotime($array['BirthDate']));
                } else {
                    $array['BirthDate'] = new Zend_Db_Expr('NULL');
                }

                $isUserActivated = $user->blocked ? !$array['blocked'] : false;

                $user = $userService->update($array);
                if ($user->MID == $this->getService('User')->getCurrentUserId())
                    $userService->updateUserIdentity($user);

                if ($isUserActivated) {
                    $url = 'http://' . $_SERVER['SERVER_NAME'] . Zend_Registry::get('config')->url->base;
                    $userService->notifyUserUnblock(
                        array(
                            'id' => $userId,
                            'placeholders' => array(
                                'URL' =>  '<a href="' . $url . '">' . $url . '</a>'
                            ),
                        )
                    );
                }
//                $claimant = $this->getService('Claimant')->updateClaimant();

                // Сохраняем метаданные
                if ($user) {
                    $userService->updateAdditionalFields($user->MID, $form);

// DEPRECATED!
//                     $user->setMetadataValues(
//                         $this->getService('User')->getMetadataArrayFromForm($form)
//                     );

//                     $user = $this->getService('User')->update($user->getValues());

                    $classifiers = $form->getClassifierValues();
                    $this->getService('Classifier')->unlinkItem($user->MID, HM_Classifier_Link_LinkModel::TYPE_PEOPLE);
                    if (is_array($classifiers) && count($classifiers)) {
                        foreach($classifiers as $classifierId) {
                            if ($classifierId > 0) {
                                $this->getService('Classifier')->linkItem($user->MID, HM_Classifier_Link_LinkModel::TYPE_PEOPLE, $classifierId);
                            }
                        }
                    }

                    $profileId = (int) $form->getValue('position_name');
                    $profile = $this->getService('AtProfile')->find($profileId);
                    $positionName = count($profile) ? $profile->current()->name : '';
                    if ($ownerSoid = $form->getValue('position_id')) {
                        $this->getService('Orgstructure')->assignUser($user->MID, $ownerSoid, $positionName, $profileId);
                    }
                }

                /**
                 * Обрабатываем фотку
                 * @var HM_Form_Element_Vue_File $photoElement
                 */
                $photoElement = $form->getElement('photo');
                if ($photoElement->isUploaded()) {
                    $inputFile = rtrim($photoElement->getDestination(), '/') . '/' . $photoElement->getValue();

                    $outputFolder = $userService->getPath(Zend_Registry::get('config')->path->upload->photo, $user->MID);
                    $outputFile = $outputFolder . $user->MID . '.jpg';
//                    $photoElement->addFilter('Rename', $outputFolder . $user->MID . '.jpg', 'photo', true);
//                    unlink($outputFile);
//                    $photoElement->receive();
                    $img = PhpThumb_Factory::create($inputFile);
                    $img->resize(HM_User_UserModel::PHOTO_WIDTH, HM_User_UserModel::PHOTO_HEIGHT);
                    $img->save($outputFile);
                }

                if ($resumeFile = $form->getElement('resume_file')) { // его могет и не быть, он только для рекрутера
                    $this->receiveResume($resumeFile, $candidateId, true);
                }


                //Обрабатываем область ответственности

                /*
                 * Этот код не нужен, так так область ответственности теперь редактируется в отдельной форме
                 *
                if ($user->MID > 0
                    && $this->getService('Acl')->inheritsRole($this->getService('User')->getCurrentUserRole(), HM_Role_Abstract_RoleModel::ROLE_ADMIN)
                   //&& $this->getService('User')->getCurrentUserRole() == HM_Role_Abstract_RoleModel::ROLE_ADMIN
                   && $this->getService('User')->isRoleExists($user->MID, HM_Role_Abstract_RoleModel::ROLE_DEAN)) {
                    $this->getService('Dean')->setResponsibilityOptions(array(
                                                                             'user_id' => (int) $user->MID,
                                                                             'unlimited_subjects' => $form->getValue('unlimited'),
                                                                             'unlimited_classifiers' => $form->getValue('unlimited'),
                                                                             'assign_new_subjects' => $form->getValue('unlimited')
                                                                        ));
                }
                */

                // метки
                $tags = array_unique($form->getParam('tags', array()));
                $tagsIds = $this->getService('Tag')->updateTags(
                    $tags,
                    $user->MID,
                    $this->getService('TagRef')->getUserType(),
                    false
                );
                $this->getService('StudyGroup')->addUserByTags($user->MID, $tagsIds);

                if ($user && !empty($array['Password'])) {
                    // Шлём сообщение о смене пароля
                    $messenger = $this->getService('Messenger');
                    $messenger->setOptions(
                        HM_Messenger::TEMPLATE_PASS,
                        array(
                            'login' => $user->Login,
                            'password' => $form->getValue('userpassword')
                        )
                    );
                    $messenger->send(HM_Messenger::SYSTEM_USER_ID, $user->MID);
                }

                if ($aclService->inheritsRole($userService->getCurrentUserRole(), array(HM_Role_Abstract_RoleModel::ROLE_HR, HM_Role_Abstract_RoleModel::ROLE_HR_LOCAL))) {

                    $candidateData = array('resume_html' => $form->getValue('resume_html'));
                    if ($source = $form->getValue('provider_id')) {
                        $candidateData['source'] = $source;
                    }

                    $candidate = $this->getService('RecruitCandidate')->updateWhere(
                        $candidateData,
                        array('user_id = ?' => $user->MID)
                    );

                    $candidateId = $candidate->candidate_id;

                    if ($resumeFile = $form->getElement('resume_file')) {
                        $this->receiveResume($resumeFile, $candidateId);
                    }
                }

                $this->_flashMessenger->addMessage(_('Учётная запись отредактирована успешно!'));
                $this->_redirector->gotoUrl($form->getValue('cancelUrl'));

            } else {
                $elem = $form->getElement('photo');
                $elem->setOptions(array('user_id' => $userId));

                if (!empty($user->birthdate)) {
                    $birthDate = new HM_Date($user->birthdate);
                    $birthDate = $birthDate->toString('dd.MM.yyyy');
                } else {
                    $birthDate = '';
                }

                // $form->populate($arr);
                $arr = array(
                    'userlogin' => $user->Login,
                    'userpassword' => $user->Password,
                    'firstname' => $user->FirstName,
                    'lastname' => $user->LastName,
                    'patronymic' => $user->Patronymic,
                    'firstnameLat' => $user->FirstNameLat,
                    'lastnameLat' => $user->LastNameLat,
                    'email' => $user->EMail,
                    'status' => $user->blocked,
                    'user_id' => $userId,
                    'mid_external' => $user->mid_external,
                    'provider_id' => $user->provider_id,
                    'birthdate' => $birthDate
                );

                // чтобы тэги не сбрасывались после неуспешной валидации
                $post = $this->_request->getParams();
                if (isset($post['tags']))
                    $post['tags'] = $this->getService('Tag')->convertAllToStrings($post['tags']);
                $form->populate(array_merge($arr, $post));

            }
        } else {
            if (!empty($user->BirthDate)) {
                $birthDate = new HM_Date($user->BirthDate);
                $birthDate = $birthDate->toString('dd.MM.yyyy');
            } else {
                $birthDate = '';
            }

            $arr = array(
                'userlogin' => $user->Login,
                //'userpassword' => $user->Password,
                'firstname' => $user->FirstName,
                'lastname' => $user->LastName,
                'patronymic' => $user->Patronymic,
                'firstnameLat' => $user->FirstNameLat,
                'lastnameLat' => $user->LastNameLat,
                'gender' => $user->Gender,
                'tel' => $user->Phone,
                'tel2' => $user->Fax,
                'birthdate' => $birthDate,
                'email' => $user->EMail,
                'status' => $user->blocked,
                'user_id' => $userId,
                'mid_external' => $user->mid_external,
                'hh_email' => $user->hh_email,
                'hh_managerId' => $user->hh_managerId,
                'provider_id' => $user->provider_id,
            );

            if ($form->getElement('position_id')) {
                $units = $this->getService('Orgstructure')->fetchAll(array('mid = ?' => $userId));
                if (count($units)) {
                    $arr['position_id'] = $units->current()->soid;
                    $arr['position_name'] = $units->current()->profile_id;
                    //pr($form->getElement('position_id')->g)
                }
            }

            /** @var HM_Form_Element_Vue_File $photoElement */
            $photoElement = $form->getElement('photo');
            $photoElement->setPreviewImg($user->getRealPhoto());


            if ($aclService->inheritsRole($userService->getCurrentUserRole(), array(HM_Role_Abstract_RoleModel::ROLE_HR, HM_Role_Abstract_RoleModel::ROLE_HR_LOCAL))) {
                $candidate = $this->getService('RecruitCandidate')->fetchOne(array(
                    'user_id = ?' => $user->MID
                ));
                $arr['provider_id'] = $candidate->source;
                $arr['resume_html'] = $candidate->resume_html;
            }

            $arr = array_merge($arr, $userService->getAdditionalFields($user->MID));

            $elem = $form->getElement('photo');
            $elem->setOptions(array('user_id' => $userId));
            $form->populate($arr);


            $populatedFiles = array();
            $resumeFileNew = realpath($candidateService->getResumeFile($candidateId));

            if (file_exists($resumeFileNew)) {
                $populatedFiles[] = new HM_File_FileModel(array(
                    'id' => $candidateId,
                    'displayName' => $candidateId . '.docx',
                    'path' => $resumeFileNew,
                    'url' => $this->view->url(array('module' => 'candidate', 'controller' => 'index', 'action' => 'resume', 'candidate_id' => $candidateId, 'baseUrl' => 'recruit')),
                ));
                if ($file = $form->getElement('resume')) {
                    $file->setValue($populatedFiles);
                }
            }

        }
        $this->view->form = $form;
    }

    protected function receiveResume($resumeFile, $candidateId, $update = false)
    {
        if ($resumeFile->isUploaded()) {
            $candidateService = $this->getService('RecruitCandidate');
            $extension = 'docx';

            if ($update) {
                $resumeFileOld = realpath($candidateService->getResumeFile($candidateId));
                if ($resumeFileOld) {
                    @unlink($resumeFileOld);
                }

                $extension = pathinfo($resumeFile->getTransferAdapter()->getFileName(), PATHINFO_EXTENSION);
            }

            $path = $this->getService('User')->getPath(Zend_Registry::get('config')->path->upload->resume, $candidateId);
            $resumeFile->addFilter('Rename', $path . $candidateId . '.' . $extension, 'resume_file', array('overwrite' => true)); // @todo: у меня что-то не работает overwrite..?
            $resumeFile->receive();
        }
    }

    /**
     * Экшн для обзора пользователя
     */
    public function viewAction() {

        $isAjax = false;
        //$this->getResponse()->setHeader('Content-type', 'text/html; charset=' . Zend_Registry::get('config')->charset);

        $userId = $this->_getParam('user_id', 0);
        $subjectId = $this->_getParam('subject_id', 0);
        $user = $this->getOne($this->getService('User')->find($userId));
        if ($user) {
            $metaData = $user->getMetadataValues();
            $user->additionalData = isset($metaData['additional_info']) ? $metaData['additional_info'] : null;

            if ( $subjectId ) {
                $userGrop = $this->getService('GroupAssign')
                                 ->getOne($this->getService('GroupAssign')
                                               ->fetchAllDependence('Group',
                                                                     array('mid=?'=>$userId,'cid=?' => $subjectId)));
                if( count($userGrop->groups) ) {
                    $userGropsList = array();
                    foreach ($userGrop->groups as $group) {
                        $userGropsList[] = $group->name;
                    }
                    $user->currentCourseGroups = implode('<br/>', $userGropsList);
                }
            }

            $userGropsList = $this->getService('StudyGroupUsers')->getUserGroups($user->MID);
            if ($userGropsList) {
                $user->studyGroups = '';
                foreach ($userGropsList as $group) {
                    if ($user->studyGroups == '') {
                        $user->studyGroups = $group['name'];
                    } else {
                        $user->studyGroups .= ', ' . $group['name'];
                    }
                }
            }
        }

        if ($this->isAjaxRequest()) {
            $isAjax = true;
            $this->_helper->getHelper('layout')->disableLayout();
            Zend_Controller_Front::getInstance()->unregisterPlugin('HM_Controller_Plugin_Unmanaged');

            $this->view->title =  $user->getName();
            $this->view->photo  = ($photo = $user->getPhoto()) ? $this->view->baseUrl($photo) : '/images/people/noPhoto.png';
            $fields = $user->getCardFields();
            if (isset($user->studyGroups)) $fields['studyGroups'] = _('Группы');
            if (isset($this->data->currentCourseGroups)) $fields['currentCourseGroups'] = _('Подгруппы');

            if (!empty($user->BirthDate)) {
                $user->BirthDate = date('Y', strtotime($user->BirthDate));
            }

            return $this->view->fields = $this->view->card($user, $fields, [], $isAjax);
        }

        $this->view->isAjax = $isAjax;
        $this->view->data = $user;

    }


    public function duplicateMergeAction()
    {
        $userId = (int) $this->_request->getParam('MID', 0);
        $user = $this->getOne($this->getService('User')->find($userId));

        if (!$user) {
            $this->_flashMessenger->addMessage(_('Пользователь не найден'));
            $this->_redirector->gotoSimple('index', 'list', 'user');
        }

        $baseUser = $this->getOne($this->getService('User')->find($user->duplicate_of));

        if(empty($baseUser)) {
            $this->_flashMessenger->addMessage(array(
                'message' => _('Данный пользователь не имеет дубликатов'),
                'type' => HM_Notification_NotificationModel::TYPE_SUCCESS
            ));
            $this->_redirectToIndex();
        }

        $this->view->baseUser = $baseUser;
        $this->view->newUser = $user;

        switch ($this->_getParam('from')) {
            case 'user-list':
                $this->view->backUrl = $this->view->url(array(
                        'module' => 'user',
                        'controller' => 'list',
                        'action' => 'index',
                    ), null, true
                );
                break;
            case 'resume-base':
                $this->view->backUrl = $this->view->url(array(
                        'module' => 'candidate',
                        'controller' => 'list',
                        'action' => 'index',
                        'baseUrl' => 'recruit'
                    ), null, true
                );
                break;
            case 'vacancy':
                $this->view->backUrl = $this->view->url(array(
                        'module' => 'candidate',
                        'controller' => 'assign',
                        'action' => 'index',
                        'baseUrl' => 'recruit',
                        'vacancy_id' => $this->_getParam('vacancy_id'),
                    ), null, true
                );
                break;
            case 'new-assignments':
                $this->view->backUrl = $this->view->url(array(
                        'module' => 'newcomer',
                        'controller' => 'new-assignments',
                        'action' => 'index',
                        'baseUrl' => 'recruit'
                    ), null, true
                );
                break;
            default:
                $this->view->backUrl = $this->view->url(array(
                        'module' => 'user',
                        'controller' => 'list',
                        'action' => 'index',
                    ), null, true
                );
                break;
        }

        $this->view->setHeader(_('Объединение дубликатов'));

        if ($this->_request->isPost()) {
            if (isset($_POST['back'])) {
                // ничего не делаем, переход на страницу откуда пришли
            }

            if (isset($_POST['merge'])) {

                // "старый" пользователь обновляется перс.данными новыми (только непустые атрибуты);
                // "новый" - удаляется;
                $values = $user->getValues();
                $newValues = array();
                unset($values['MID']);
                unset($values['blocked']);
                unset($values['duplicate_of']);
                foreach ($values as $key => $value) {
                    if ($value === '') {
                        continue;
                    }
                    $newValues[$key] = $value;
                }

                $newPhoto = $user->getRealPhoto();
                if (file_exists($newPhoto)) {
                    $fileinfo = pathinfo($newPhoto);
                    $photo = sprintf('%s%s.%s', $baseUser->getPath(), $baseUser->MID, $fileinfo['extension']);
                    @copy($newPhoto, $photo);
                }

                $newValues['MID'] = $baseUser->MID;
                $this->getService('User')->update(
                    $newValues
                );

                $this->getService('RecruitCandidate')->updateWhere(
                    array('user_id' => $baseUser->MID),
                    array('user_id = ?' => $user->MID)
                );
                $this->getService('RecruitCandidate')->removeDuplicatedAssigns($baseUser->MID);

                $this->getService('Orgstructure')->updateWhere(
                    array('mid' => $baseUser->MID),
                    array('mid = ?' => $user->MID)
                );

                $this->getService('User')->delete(
                    $user->MID
                );
            }

            if (isset($_POST['saveboth'])) {
                // просто удаляется пометка "дубликат";
                $this->getService('User')->update(
                    array(
                        'MID' => $user->MID,
                        'duplicate_of' => 0
                    )
                );
            }

            $backUrl = $this->_getParam('backUrl', $this->view->url(
                array(
                    'module' => 'user',
                    'controller' => 'list',
                    'action' => 'index'
                )
            ));

            $this->_redirect($backUrl);
        }
    }

    public function updateActions($ldap, $duplicate_of, $actions)
    {
        $userRole = $this->getService('User')->getCurrentUserRole();

//        if ((($userRole == 'admin') && $ldap) || ($userRole == 'simple_admin')) {
//            unset($actions[1]);
//        }

        if (($userRole == 'admin') && ($duplicate_of == 0)) {
            $this->unsetAction($actions, ['module' => 'user', 'controller' => 'list', 'action' => 'duplicate-merge']);
        }

        return $actions;
    }

    public function generateAction() {
        $form = new HM_Form_Generate();

        if ($this->_request->isPost()) {
            if ($form->isValid($this->_request->getParams())) {
                $this->getService('User')->generate(
                    $form->getValue('number'),
                    $form->getValue('prefix'),
                    $form->getValue('password'),
                    $form->getValue('role')
                );
                $this->_flashMessenger->addMessage(_('Учётные записи сгенерированы успешно!'));
                $this->_redirector->gotoSimple('index', 'list', 'user', array('logingrid' => $form->getValue('prefix'), 'ordergrid' => 'login_ASC'));
            }
        }
        $this->view->form = $form;
    }

    public function assignTagAction()
    {
        $ids = explode(',', $this->_request->getParam('postMassIds_grid'));
        $tags = $this->_getParam('tags', []);
        $tags = array_filter($tags);

        /** @var HM_Tag_TagService $tagService */
        $tagService = $this->getService('Tag');

        $tagsCache = $tagService->getTagsCache($ids, HM_Tag_Ref_RefModel::TYPE_USER);

        foreach ($ids as $userId) {
            if (!isset($tagsCache[$userId]))
                $tagsCache[$userId] = array();

            $tagsIds = $tagService->updateTags(
                ($tagsCache[$userId] + $tags),
                $userId,
                $this->getService('TagRef')->getUserType(),
                false
            );
            $this->getService('StudyGroup')->addUserByTags($userId, $tagsIds);
        }

        $this->_flashMessenger->addMessage(_('Метка успешно назначена пользователям'));
        $this->_redirector->gotoSimple('index', 'list', 'user');

    }

    public function unassignTagAction()
    {
        $ids = explode(',', $this->_request->getParam('postMassIds_grid'));

        $tags = array_unique(array_filter($this->_getParam('tagsUnassign', [])));

        $tagsCache = $this->getService('Tag')->getTagsCache($ids, $this->getService('TagRef')->getUserType());

        foreach ($ids as $userId) {

            if (!isset($tagsCache[$userId])) {
                $tagsCache[$userId] = array();
            }

            $tagsCacheByText = array_flip($tagsCache[$userId]);

            foreach ($tags as $tag) {
//                if ($this->getService('Tag')->isNewTag($tag)) continue;

                if (isset($tagsCacheByText[$tag])) {
                    unset($tagsCacheByText[$tag]);
                }
            }

            $tagsCache[$userId] = array_flip($tagsCacheByText);

            $this->getService('Tag')->updateTags(
                $tagsCache[$userId],
                $userId,
                $this->getService('TagRef')->getUserType(),
                false
            );
        }

        $this->_flashMessenger->addMessage(_('Назначение меток пользователям отменено'));
        $this->_redirector->gotoSimple('index', 'list', 'user');

    }

    public function sendConfirmationAction()
    {
        if (count($ids = explode(',', $this->_request->getParam('postMassIds_grid')))) {
            $users = $this->getService('User')->fetchAll(array('MID IN (?)' => $ids));
            foreach ($users as $user) {

                $hash = $this->getService('User')->getEmailConfirmationHash($user->MID);
                $url = ($_SERVER['HTTPS'] == 'on' ? ' https' : 'http') . '://'. $_SERVER['SERVER_NAME'] . Zend_Registry::get('config')->url->base . $this->view->url(array(
                    'module' => 'user',
                    'controller' => 'reg',
                    'action' => 'confirm-email',
                    'user_id' => $user->MID,
                    'key' => $hash,
                ));
                $url = '<a href="' . $url . '">' . $url . '</a>';

                $messenger = $this->getService('Messenger');
                $messenger->setOptions(
                    HM_Messenger::TEMPLATE_REG_CONFIRM_EMAIL,
                    array(
                        'email_confirm_url' => $url,
                    )
                );
                $messenger->send(HM_Messenger::SYSTEM_USER_ID, $user->MID);

                if ($user->email_confirmed == HM_User_UserModel::EMAIL_CONFIRMED) {
                    $data = $user->getValues();
                    $data['email_confirmed'] = HM_User_UserModel::EMAIL_NOT_CONFIRMED;
                    $this->getService('User')->update($data);
                }
            }

            $this->_flashMessenger->addMessage(_('Письма с подтверждением email-адреса успешно разосланы'));
            $this->_redirector->gotoSimple('index', 'list', 'user');
        }
    }

    public function setConfirmedAction()
    {
        if (count($ids = explode(',', $this->_request->getParam('postMassIds_grid')))) {
            $users = $this->getService('User')->fetchAll(array('MID IN (?)' => $ids));
            foreach ($users as $user) {

                if ($user->email_confirmed == HM_User_UserModel::EMAIL_NOT_CONFIRMED) {
                    $data = $user->getValues();
                    $data['email_confirmed'] = HM_User_UserModel::EMAIL_CONFIRMED;
                    //$data['blocked'] = $this->getService('Option')->getOption('regAutoBlock') ? 1 : 0; // разблокируем если не предусмотрено ручное разблокирование
                    $this->getService('User')->update($data);
                }
            }

            $this->_flashMessenger->addMessage(_('Email-адреса подтверждены'));
            $this->_redirector->gotoSimple('index', 'list', 'user');
        }
    }

    public function filterString($stringInput)
    {
        return mb_convert_case(str_replace(" ","",trim($stringInput)),MB_CASE_TITLE,"UTF-8");
    }
}

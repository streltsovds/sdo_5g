<?php

class IndexController extends HM_Controller_Action {

	protected $userId = 0;
	protected $userRole = HM_Role_Abstract_RoleModel::ROLE_GUEST;

	const PASSWORD_RECOVERY_LIFETIME = 1800; // время жизни ссылки на восстановление пароля, сек.

    public function init()
    {
        parent::init();

        /** @var HM_User_UserService $userService */
        $userService = $this->getService('User');
        /** @var HM_Acl $aclService */
        $aclService = $this->getService('Acl');

        $this->userId = (int) $userService->getCurrentUserId();
        $this->userRole = $userService->getCurrentUserRole();

        if ($aclService->inheritsRole($this->userRole, HM_Role_Abstract_RoleModel::ROLE_ENDUSER)){
            $this->userRole  = HM_Role_Abstract_RoleModel::ROLE_ENDUSER;
        }
    }

    public function indexAction()
    {
        $currentUserId = $this->getService('User')->getCurrentUserId();
        $loginStart = $this->getService('Option')->getOption('loginStart');
        if(!$currentUserId && $loginStart) {
            $this->_redirector->gotoSimple('login', 'index', 'default');
        }
        $this->session = new Zend_Session_Namespace('default');

         if ($this->_hasParam('oauth_token') && $this->userRole != HM_Role_Abstract_RoleModel::ROLE_GUEST) {
            $this->_redirector->gotoUrl($this->view->url(array(
                'module'	=> 'oauth',
                'controller'=> 'v1',
                'action'	=> 'authorize',
            ))
            .'?&oauth_token='.$this->_getParam('oauth_token')
            .'&oauth_callback='.$this->_getParam('oauth_callback', ''));
         }

        // It's a hack to remove some js and css from error page
        if(count($this->_response->getException()) > 0){
            $this->getHelper('viewRenderer')->setNoRender();
            return;
        }

        $curUser = $this->getService('User')->getCurrentUser();
        if ($this->getService('User')->needDataAgreement()) {
            if ($this->_getParam('data_agreement', 0)) {
                $user = $this->getService('User')->getOne($this->getService('User')->fetchAll(array('MID = ?' => $this->getService('User')->getCurrentUserId())));
                $user ->data_agreement = 1;
                $this->getService('User')->update($user->getValues());

                $curUser->data_agreement = 1;
                $this->_redirector->gotoSimple('index', 'index', 'default');
            } else {
                $contractTexts = $this->getService('Option')->getOptions(HM_Option_OptionModel::SCOPE_CONTRACT);
                $this->view->dataAgreement = array(
                    'title'    => _('Согласие на обработку персональных данных'),
                    'content'  => $contractTexts['contractPersonalDataText'],
            );
            }
        }

        if (!$this->_hasParam('oauth_token')) {
            $blocks = $this->getService('Infoblock')->getTree($this->userRole, false, $this->userId);
        }
        else $blocks = array('current' => array());

        if (Zend_Registry::get('config')->offline && $this->userRole === HM_Role_Abstract_RoleModel::ROLE_GUEST){
            // Мы добавляем инфоблок авторизации принудительно для offline-версий
            $block = array(
                'name' => 'Authorization',
                'title' => _('Авторизация'),
                'y' => 0
            );

            $found = false;
            if (count($blocks['current'])) {
                foreach ($blocks['current'] as $key => $bl) {
                    if ($bl['name'] == $block['name']) {
                        $blocks['current'][$key]['y'] = $block['y'];
                        $found = true;
                        break;
                    }
                }
            }
            if (!$found) {
                $blocks['current'][] = $block;
            }
        }

        $this->view->layoutContentFullWidth = true;
        $this->view->initIndexMenu();
    }

    public function loginAction() {
        // login form
        $serviceContainer = Zend_Registry::get('serviceContainer');
        $currentRole = $serviceContainer->getService('User')->getCurrentUserRole();
        $isGuest = $serviceContainer->getService('Acl')->inheritsRole($currentRole, HM_Role_Abstract_RoleModel::ROLE_GUEST);

        if (!$isGuest) {
            $this->_redirector->gotoSimple('index', 'index', 'default');
        }

        $designOptions = $this->getService('Option')->getOptions(HM_Option_OptionModel::SCOPE_DESIGN);
        $this->view->designOptions = $designOptions;
        $this->view->contentBackground = $this->view->getRandomBackground();
    }

    public function simpleAuthorizationAction()
    {
        $key = $this->_getParam('key');

        $this->getService('User')->authorizeSimple($key);
    }

    public function authorizationAction()
    {
        $this->getHelper('viewRenderer')->setNoRender();

        $form = new HM_Form_Authorization();

        $return = array(
            'code' => 0,
            'message' => _('Вы неверно ввели имя пользователя или пароль')
        );

        if ($this->isAjaxRequest()) {

            $request_params = $this->getJsonParams();

            if (!$form->isValid($request_params)) {
                $return = array(
                    'code' => 0,
                    'message' => _('Недопустимые значения в полях авторизации')
                );
            } elseif ($request_params['start_login']) {
                try {
                    $user = $this->getService('User')->authorize($form->getValue('login'), $form->getValue('password'), false, false);//, $systemInfo);
                    $return['code']    = 1;
                    $return['message'] = _('Пользователь успешно авторизован.');
                } catch(HM_Exception_Auth $e) {
                    $return['code']    = $e->getCode();
                    $return['message'] = $e->getMessage();
                }
            } else {
                $return = array(
                    'code' => 0,
                    'message' => _('Превышено количество неуспешных попыток авторизации, для продолжения необходимо ввести код подтверждения.')
                );
                $this->getService('Captcha')->attempt($form->getValue('login'));
            }

            $this->sendAsJsonViaAjax($return);
        }


        $request = $this->getRequest();
        if ($request->isPost() || $request->isGet()) {

            $systemInfo = $request->getParam('systemInfo');
            if (!$form->isValid($request->getParams())) {
                $return = array(
                    'code' => 0,
                    'message' => _('Недопустимые значения в полях авторизации')
                );
            } elseif ($request->getParam('start_login', false)) {
                try {
                    $user = $this->getService('User')->authorize($form->getValue('login'), $form->getValue('password'), false, false, $systemInfo);
                    $return['code']    = 1;
                    $return['message'] = _('Пользователь успешно авторизован.');

                    $this->view->jQuery()->addOnLoad('window.location.reload()');
                } catch(HM_Exception_Auth $e) {
                    $return['code']    = $e->getCode();
                    $return['message'] = $e->getMessage();
                }
            } else {
            	$return = array(
            			'code' => 0,
            			'message' => _('Превышено количество неуспешных попыток авторизации, для продолжения необходимо ввести код подтверждения.')
            	);
                $this->getService('Captcha')->attempt($form->getValue('login'));
            }
        }

        if ($request->getParam('start_login', false) && $return['code'] != 1) {
            $form = new HM_Form_Authorization();
            $form->isValid($request->getParams());

            $this->getService('UserLoginLog')->login($form->getValue('login'), $return['message'], HM_User_Loginlog_LoginlogModel::STATUS_FAIL);
        } else {
            if ($form->getValue('remember')) {
                $this->getService('Session')->setAuthorizerKey();
            }

            $this->getService('UserLoginLog')->login($form->getValue('login'), $return['message'], HM_User_Loginlog_LoginlogModel::STATUS_OK);

        }
        echo $form->render();

        if ($request->getParam('start_login', false)) {
            echo $this->view->notifications(array(array(
                'type' => $return['code'] != 1 ? HM_Notification_NotificationModel::TYPE_ERROR : HM_Notification_NotificationModel::TYPE_SUCCESS,
                'message' => $return['message']
            )), array('html' => true));
            if ($return['code'] === 1) {
                // хак для перегрузки страницы
                // @TODO: Придумать менее варварский способ
                echo '<script>window.location.reload()</script>';
            }

        }

    }

    /**
     * @throws Zend_Exception
     * @throws Zend_Form_Exception
     */
    public function editInfoblockAction(){
        $infoblock = $this->_getParam('infoblock', '');

        $form = new HM_Form_Infoblock();
        $request = $this->getRequest();
        if ($request->isPost()) {
            if ($form->isValid($request->getParams())) {
                $this->updateInfoblock($form, $infoblock);

                $this->_flashMessenger->addMessage(_('Элемент успешно обновлён'));
                $this->_redirector->gotoSimple('index');
            }
        } else {
            $elem = $form->getElement('background');
            $elem->setOptions(array('infoblock_name' => $infoblock));
        }
        $this->view->form = $form;
    }

    /**
     * Редактирует Инфоблок
     *
     * @param HM_Form $form
     * @param string $infoblock
     * @throws Zend_Exception
     * @throws Zend_Form_Exception
     */
    public function updateInfoblock(HM_Form $form, $infoblock)
    {
        $post = $this->_request->getParams();
        $background = $form->getElement('background');
        $path = Zend_Registry::get('config')->path->upload->infoblock;
        if ($background && $background->isUploaded()) {
            $background->addFilter('Rename', $path . $infoblock . '.jpg');
            unlink($path . $infoblock . '.jpg');
            $background->receive();
            $img = PhpThumb_Factory::create($path . $infoblock . '.jpg');
            $img->resize(HM_Infoblock_InfoblockModel::BACKGROUND_WIDTH, HM_Infoblock_InfoblockModel::BACKGROUND_HEIGHT);
            $img->save($path . $infoblock . '.jpg');
        } else {
            if ($post['background_delete']) {
                @unlink($path . $infoblock . '.jpg');
            }
        }
    }

    public function authorizationTmcAction()
    {
        $request = $this->getRequest();
        $login = $request->getParam('login');
        $password = $request->getParam('password');
        $remember = $request->getParam('remember');

        if ($request->isPost() || $request->isGet()) {
            try {
                $user = $this->getService('User')->authorize($login, $password, false, false, '');
                $message = _('Пользователь успешно авторизован.');
                $this->getService('UserLoginLog')->login($login, $message, HM_User_Loginlog_LoginlogModel::STATUS_OK);

                if ($remember) {
                    $this->getService('Session')->setAuthorizerKey();
                }

            } catch (HM_Exception_Auth $e) {
                $message = _('Вы неверно ввели имя пользователя или пароль');
                $this->getService('UserLoginLog')->login($login, $message, HM_User_Loginlog_LoginlogModel::STATUS_FAIL);

                $this->session = new Zend_Session_Namespace('default');
                $this->session->tmc_auth_error = true;
                $this->session->tmc_auth_login = $login;
            }
        }

        $this->_redirector->gotoSimple('index', 'index', 'default');
    }

    public function logoutAction()
    {
        $this->getService('User')->logout();

        $_SESSION = array();
        session_regenerate_id(true);

        $this->_redirector->gotoSimple('index', 'index', 'default');
    }


    public function restoreAction()
    {
        $url = $this->getService('User')->restore();
        $this->_redirector->gotoUrl($url);
//        $this->_redirector->gotoSimple('index', 'index', 'default');
    }

    public function switchAction()
    {
        $this->getService('User')->switchRole($this->_getParam('role', false));
        $this->session = new Zend_Session_Namespace('default');
        $this->session->switch_role = 1;

        // #37971
        $_SESSION['default']['grid']['subject-list-index']['grid']['filters'] = [];

        $this->_redirector->gotoUrl($_SERVER['HTTP_REFERER']);
    }

    public function rememberAction()
    {
        $form = new HM_Form_Remember();

        $request = $this->getRequest();
        if ($request->isPost() && $form->isValid($request->getParams())) {
            $users = $this->getService('User')->fetchAll($this->getService('User')->quoteInto('EMail = ?', $form->getValue('email')));

            if (!count($users)) {
                $this->_flashMessenger->addMessage(array(
                    'type' => HM_Notification_NotificationModel::TYPE_ERROR,
                    'message' => _('Пользователь не найден.')
                ));
                $this->_redirector->gotoSimple('index', 'index', 'remember');
            }
            if (count($users) > 1) {
                $this->_flashMessenger->addMessage(array(
                    'type' => HM_Notification_NotificationModel::TYPE_ERROR,
                    'message' => _('Восстановление пароля невозможно. Указанному E-mail соответствует несколько учетных записей.')
                ));
                $this->_redirector->gotoSimple('login', 'index', 'default');
            }

            $user = $this->getOne($users);
            $passwordOptions = $this->getService('Option')->getOptions(HM_Option_OptionModel::SCOPE_PASSWORDS);
            $lastDate = $this->getService('UserPassword')->getChangePasswordLastDate($user->MID);
            if((time() - strtotime($lastDate)) < ($passwordOptions['passwordMinPeriod'] * 3600*24)) {
                $this->_flashMessenger->addMessage(array(
                    'type' => HM_Notification_NotificationModel::TYPE_ERROR,
                    'message' => _('Восстановление пароля невозможно. Не прошел минимальный срок действия пароля.')
                ));
                $this->_redirector->gotoSimple('index', 'index', 'remember');
            }
            $user->password_recovery_hash = md5(time() . str_shuffle('qwertyuioplkjhgfdsazxcvbnm&%$#@'));
            $user->password_recovery_date = date('Y-m-d H:i:s', time() + (int) self::PASSWORD_RECOVERY_LIFETIME);
            $updatedUser = $this->getService('User')->update($user->getValues(), false);
            if (!$updatedUser) {
                $this->_flashMessenger->addMessage(array(
                    'type' => HM_Notification_NotificationModel::TYPE_ERROR,
                    'message' => _('Произошла ошибка на сервере. Попробуйте еще раз.')
                ));
            } else {
                // Отправляем сообщение со ссылкой для восстановления пароля
                $recoveryLink = $this->view->serverUrl('/default/index/recover-password/code/' . $user->password_recovery_hash);
                $messenger = $this->getService('Messenger');
                $messenger->setOptions(HM_Messenger::TEMPLATE_RECOVERY_LINK, array('recovery_link' => '<a href="' . $recoveryLink . '" >' . $recoveryLink . '</a>'));
                $messenger->send(HM_Messenger::SYSTEM_USER_ID, $user->MID);
                $this->_flashMessenger->addMessage(_('На электронную почту отправлена ссылка для восстановления пароля.'));
            }
            $this->_redirector->gotoSimple('login', 'index', 'default');
        }
        $this->view->form = $form;
    }

    public function recoverPasswordAction()
    {
        $form = new HM_Form_Force();

        $request = $this->getRequest();

        if (! $this->_hasParam('code') ||  ! preg_match('/^[a-z0-9]{32}$/i', $request->getParam('code'))) {
            $this->_redirector->gotoSimple('login', 'index', 'default');
        }

        $user = $this->getService('User')->getOne($this->getService('User')->fetchAll(array('password_recovery_hash = ?' => $request->getParam('code'))));

        if (!$user) {
            $this->_flashMessenger->addMessage(array(
                'type' => HM_Notification_NotificationModel::TYPE_ERROR,
                'message' => _('Недействительная ссылка для восстановления пароля.')
            ));
            $this->_redirector->gotoSimple('login', 'index', 'default');
        }

        $codeDateEnd = (int) strtotime($user->password_recovery_date);
        if (time() > $codeDateEnd) {
            $this->_flashMessenger->addMessage(array(
                'type' => HM_Notification_NotificationModel::TYPE_ERROR,
                'message' => _('Время действия ссылки истекло. Попробуйте восстановить пароль еще раз.')
            ));
            $this->_redirector->gotoSimple('index', 'index', 'remember');
        }

        if ($request->isPost() && $form->isValid($request->getParams())) {

            // пароль - генерим или используем заданный пользователем
            if ($request->getParam('generatepassword', 0) == 1) {
                $password = $this->getService('User')->getRandomString();
            } else {
                $password = $form->getValue('userpassword');
            }

            $user->Password = new Zend_Db_Expr($this->getService('User')->quoteInto('PASSWORD(?)', $password));
            $user->force_password = 0;
            $user->password_recovery_hash = '';
            $updatedUser = $this->getService('User')->update($user->getValues());

            if (!$updatedUser) {
                $this->_flashMessenger->addMessage(array(
                    'type' => HM_Notification_NotificationModel::TYPE_ERROR,
                    'message' => _('Произошла ошибка на сервере. Попробуйте еще раз.')
                ));
                $this->_redirector->gotoSimple('recover-password', 'index', 'default', ['code' => $request->getParam('code')]);
            } else {
                $messenger = $this->getService('Messenger');
                $messenger->setOptions(HM_Messenger::TEMPLATE_PASS, array('login' => $user->Login, 'password' => $password));
                $messenger->send(HM_Messenger::SYSTEM_USER_ID, $user->MID);
                $this->_flashMessenger->addMessage(_('Пароль успешно изменен.'));
                $this->_redirector->gotoSimple('login', 'index', 'default');
            }
        }
        $this->view->form = $form;
    }


    public function forcePasswordAction()
    {
        $form = new HM_Form_Force();

        $request = $this->getRequest();

        if ($request->isPost()) {

            if ($form->isValid($request->getParams())) {

                $user = $this->getService('User')->getOne($this->getService('User')->fetchAll(array('MID = ?' => $this->getService('User')->getCurrentUserId())));

                if ($user) {
                    // пароль - генерим или используем заданный пользователем
                    if ($request->getParam('generatepassword', 0) == 1) {
                        $password = $this->getService('User')->getRandomString();
                    } else {
                        $password = $form->getValue('userpassword');
                    }
                    $user->Password = new Zend_Db_Expr($this->getService('User')->quoteInto('PASSWORD(?)', $password));
                    $user->force_password = 0;

                    $this->getService('User')->update($user->getValues());

                    $messenger = $this->getService('Messenger');
                    $messenger->setOptions(HM_Messenger::TEMPLATE_PASS, array('login' => $user->Login, 'password' => $password));
                    $messenger->send(HM_Messenger::SYSTEM_USER_ID, $user->MID);

                    //$this->_flashMessenger->addMessage(_('Пароль успешно обновлен. Для продолжения работы вам необходимо авторизоваться заново.'));
                    $this->logoutAction();
                } else {
                    $this->_flashMessenger->addMessage(array(
                        'type' => HM_Notification_NotificationModel::TYPE_ERROR,
                        'message' => _('Пользователь не найден.')
                    ));
                }
            }
        }

        $this->view->form = $form;
    }


    public function errorAction() {
        $this->view->content = '';
    }

    public function ldapAction()
    {
        die('hack detected');

        $options = Zend_Registry::get('config')->ldap->options->toArray();
        $options['username'] = iconv(Zend_Registry::get('config')->charset, 'UTF-8', $options['username']);

        $ldap = new Zend_Ldap($options);
        $ldap->bind();

        $dn = $ldap->getCanonicalAccountName(
            'user1',
            Zend_Ldap::ACCTNAME_FORM_DN
        );

        $entry = $ldap->getEntry($dn);


        pr($entry);
        pr($dn);
        die();
    }

    public function dataGridAction()
    {
        // dataSheet
        $data = array();
        $data[1][1] = 1;
        $data[1][2] = 2;
        $data[1][3] = 3;
        $data[2][1] = 21;
        $data[2][2] = 22;
        $data[2][3] = 23;
        $data[3][1] = 31;
        $data[3][2] = 32;
        $data[3][3] = 33;

        $sheet = HM_DataSheet::factory(
            'table',
            array(
                'horizontalHeader' => array(
                    'name' => 'cols',
                    'title' => _('Название горизонтального заголовка'),
                    'checkboxes' => true,
                    'fields' => array(
                        1 => array('title' => _('Заголовок 1'), 'pattern' => '[2-5]'),
                        2 => array('title' => _('Заголовок 2'), 'render' => 'checkbox'),
                        3 => array('title' => _('Заголовок 3'), 'render' => 'select', 'values' => array(31 => 'триодин', 32 => 'тридва', 33 => 'тритри')),
                        4 => array('title' => _('Заголовок 4')),
                        5 => array('title' => _('Заголовок 5')),
                        6 => array('title' => _('Заголовок 6')),
                        7 => array('title' => _('Заголовок 7')),
                        8 => array('title' => _('Заголовок 8')),
                        9 => array('title' => _('Заголовок 9')),
                        10 => array('title' => _('Заголовок 10')),
                        11 => array('title' => _('Заголовок 11')),
                        12 => array('title' => _('Заголовок 12')),
                        13 => array('title' => _('Заголовок 13')),
                        14 => array('title' => _('Заголовок 14')),
                        15 => array('title' => _('Заголовок 15')),
                        16 => array('title' => _('Заголовок 16')),
                    )
                ),
                'verticalHeader' => array(
                    'name' => 'rows',
                    'title' => _('Название вертикального заголовка'),
                    'checkboxes' => true,
                    'fields' => array(
                        1 => array('title' => _('Вертикальный заголовок 1')),
                        2 => array('title' => _('Вертикальный заголовок 2')),
                        3 => array('title' => _('Вертикальный заголовок 3'))
                    )
                ),
                'data' => $data,
                'saveUrl' => $this->view->url(array('action' => 'data-grid', 'controller' => 'index', 'module' => 'default'))
            )
        );

        $actions = new HM_DataSheet_Actions(_('Действия со строками'));
        $actions->addAction(_('Тестовое действие'), $this->view->url(array('action' => 'data-grid', 'controller' => 'index', 'module' => 'default')));

        $sheet->setVerticalActions($actions);

        $actions = new HM_DataSheet_Actions(_('Действия со столбцами'));
        $actions->addAction(_('Тестовое действия со столбцами'), $this->view->url(array('action' => 'data-grid', 'controller' => 'index', 'module' => 'default')));

        $sheet->setHorizontalActions($actions);

        $this->view->sheet = $sheet->deploy();

    }

    public function languageAction()
    {
        $returnUrl = $_SERVER['HTTP_REFERER'];

        $lang = $this->_getParam('lang', 'rus');

        $langs = Zend_Registry::get('config')->languages->toArray();

        if (isset($langs[$lang])) {

            setcookie(HM_User_UserService::COOKIE_NAME_LANG, $lang, 0, '/');

            if ((int) $this->getService('User')->getCurrentUserId() > 0) {
                $this->getService('User')->update(array('lang' => $lang, 'MID' => $this->getService('User')->getCurrentUserId()));
                $user = $this->getService('User')->getCurrentUser();
                if ($user) {
                    $user->lang = $lang;
                }
            }
        }

        $this->_redirector->gotoUrl($returnUrl);
    }

    public function crontaskAction()
    {
        $param = 'sendRotationReport';
        if ($param == 'sendRotationReport') {
            try {
                $this->getService('CronTask')->deleteBy(array('crontask_id =?' => $param));
            } catch (Exception $e) {
                echo $e;
            }
        }
        $this->getService('CronTask')->init()->run($param);
        exit('Задания планировщика выполнены.');
    }

    public function cleanCacheAction()
    {
        $cache = Zend_Registry::get('cache');
        $cache->clean();
        exit('done');

    }

    public function linkOldMaterialsAction()
    {
        $currentRole = $this->getService('User')->getCurrentUserRole();
        if ($this->getService('Acl')->inheritsRole($currentRole, HM_Role_Abstract_RoleModel::ROLE_ADMIN)) {
            $select = $this->getService('Lesson')->getSelect();
            $select->from('schedule', ['SHEID']);
            $select->where('material_id IS NULL');
            $results = $select->query()->fetchAll();

            foreach ($results as $result) {
                $lesson = $this->getService('Lesson')->find($result['SHEID'])->current();
                try {
                    if ($lesson && ($materialId = $lesson->getModuleId())) {
                        $data = $lesson->getData();
                        $data['material_id'] = $materialId;
                        $this->getService('Lesson')->update($data);
                    }
                } catch (Exception $e) {}
            }

            $this->_flashMessenger->addMessage(array(
                'type' => HM_Notification_NotificationModel::TYPE_SUCCESS,
                'message' => _('Связи занятий с учебными модулями успешно обновлены.')
            ));
            $this->_redirector->gotoUrl('/', array('prependBase' => false));
        }
    }
}
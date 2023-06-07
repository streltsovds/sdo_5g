<?php
class HM_User_UserService extends HM_Service_Abstract
{
    const SECUTITY_TOKEN_NAME = 'client-security-token';

    const NEW_LOGIN_PREFIX = 'user_';

    const COOKIE_NAME_LANG = 'hmlang';

    const DEFAULT_LANG = 'rus';

    public function getCurrentUser()
    {
        try {
            /* один черт при вызове из анменеджда сервисных методов
            при любом вызове UserService->getCurrentUserЧто-Нибудь() все обернуто  в трайкетчи
            лучше уж тут тогда поставить а не плодить их везде где поНядобятся*/
            $currentUser = Library::getAuth('default')->getIdentity();

        } catch (Zend_Session_Exception $e) {
            // ???
        }
        return $currentUser;
    }

    /**
     * Создание простой ссылки для "быстрой" авторизации пользователя на сайте
     *
     * @param (int)    $user_id      - идентификатор пользователя
     * @param (string) $url          - ссылка на страницу, куда должен перейти пользователь после авторизации
     * @param (int)    $valid_before - метка времени timestamp, до которой пользователь может авторизоваться по ссылке
     */
    public function createSimpleAuthLink($user_id, $url, $valid_before = null)
    {
        $guid = strtolower(preg_replace('/[\{\}\-]/', '', com_create_guid()));

        if ($valid_before === null) {
            $valid_before = mktime() + 60*60*24; // действительно в течении 24 часов
        }

        $db = $this->getMapper()->getTable()->getAdapter();
        $result = $db->insert('simple_auth', array(
            'user_id'      => $user_id,
            'auth_key'     => $guid,
            'link'         => $url,
            'valid_before' => date('Y-m-d H:i:s', $valid_before)
        ));

        if ($result) {
            return 'http://'.$_SERVER['HTTP_HOST'].'/index/simple-authorization/key/'.$guid;
        }

        return false;
    }

    /**
     * Используется при авторизации через ссылку, полученную методом createSimpleAuthLink
     *
     * @param $key
     */
    public function authorizeSimple($key)
    {
        $select = $this->getSelect();
        $select->from('simple_auth', array(
            'user_id',
            'link',
            'valid_before'
        ));
        $select->where('auth_key = ?', $key);

        $result = $select->query()->fetch();

        if ($result) {
            $user_id      = $result['user_id'];
            $valid_before = DateTime::createFromFormat('Y-m-d H:i:s', $result['valid_before']);
            $valid_before = $valid_before->getTimestamp();

            if ($valid_before > mktime()) {

                $user = $this->getOne($this->find($user_id));

                if ($user && !$user->blocked) {

                    $this->_initRole($user);
                    $this->getService('Unmanaged')->initUnmanagedSession($user);
                    $this->_init($user);

                    header('Location: '. $result['link']);
                    die;
                }

            }
        }

        header('Location: /');
        die;

    }

    public function getCurrentUserRole($withUnion = false)
    {
        $user = $this->getCurrentUser();
        if (!$user) {
            return HM_Role_Abstract_RoleModel::ROLE_GUEST;
        } elseif ($withUnion && $this->getService('Acl')->inheritsRole($user->role, HM_Role_Abstract_RoleModel::ROLE_ENDUSER)) {
            return HM_Role_Abstract_RoleModel::ROLE_ENDUSER;
        }
        return $user->role;
    }

    public function getCurrentUserId()
    {
        $user = $this->getCurrentUser();
        return $user ? $user->MID : false;
    }

    public function getCurrentLang()
    {
        $languages = Zend_Registry::get('config')->languages->toArray();

        if ($this->getCurrentUserId() > 0) {
            $user = $this->getCurrentUser();
            $lang = $user->lang;

            if (isset($languages[$lang])) {
                return $languages[$lang];
            }
        }

        if (isset($_COOKIE[self::COOKIE_NAME_LANG])) {
            $lang = $_COOKIE[self::COOKIE_NAME_LANG];
            if (isset($languages[$lang])) {
                return $languages[$lang];
            }
        }

        $accepted = Zend_Locale::getBrowser();
        if (is_array($accepted) && count($accepted)) {
            foreach($accepted as $locale => $weight) {
                foreach($languages as $lang) {
                    if (strtolower($locale) == strtolower($lang['locale'])) {
                        return $lang;
                    }
                }
            }
        }

        return false;

    }

    public function getCurrentLangId()
    {
        $lang = $this->getCurrentLang();

        if ($lang) {
            $lang = $lang['id'];
        } else {
            $lang = self::DEFAULT_LANG;
        }

        return $lang;
    }


    public function assignRole($userId, $role)
    {
        $result = true;
        $roleNames = array();
        $roles = HM_Role_Abstract_RoleModel::getBasicRoles(false);
        $userId = intval($userId);

        if ( $userId <= 0 ) {
            return false;
        }

        $ar_roles = (is_array($role))? $role : (array) $role;

        foreach ( $ar_roles as $role) {

            if(!isset($roles[$role])){
                continue;
            }

            //Так как таблицы все разные, то придется делать свич и там создавать массив на добавление
            switch($role){
                case HM_Role_Abstract_RoleModel::ROLE_SUPERVISOR:
                    $find = $this->getService('Supervisor')->find($userId);
                    if (count($find) > 0) {
                        $result = false;
                        break;
                    }

                    $this->getService('Supervisor')->insert(array('user_id' => $userId));
                    break;
                case 'student':

                    $find = $this->getService('Student')->fetchAll(array('MID = ?' => $userId));

                    if(count($find) > 0){
                        $result = false;
                        break;
                    }

                    $this->getService('Student')->insert(
                        array('MID' => $userId,
                              'CID' => 0)
                    );

                break;
                case 'teacher':

                    $find = $this->getService('Teacher')->fetchAll(array('MID = ?' => $userId));

                    if(count($find) > 0){
                        $result = false;
                        break;
                    }

                    $this->getService('Teacher')->insert(
                        array('MID' => $userId,
                              'CID' => 0)
                    );

                break;
//                case HM_Role_Abstract_RoleModel::ROLE_MODERATOR:
//
//                    $find = $this->getService('Moderator')->fetchAll(array('user_id = ?' => $userId));
//
//                    if(count($find) > 0){
//                        $result = false;
//                        break;
//                    }
//
//                    $this->getService('Moderator')->insert(
//                        array('user_id' => $userId,
//                              'project_id' => 0)
//                    );
//
//                break;
                case 'developer':
                     $find = $this->getService('Developer')->fetchAll(array('mid = ?' => $userId));

                    if(count($find) > 0){
                        $result = false;
                        break;
                    }

                    $this->getService('Developer')->insert(
                        array('mid' => $userId)
                    );


                break;
                case 'manager':
                    $find = $this->getService('Manager')->fetchAll(array('mid = ?' => $userId));
                    if(count($find) > 0){
                        $result = false;
                        break;
                    }
                    $this->getService('Manager')->insert(
                        array('mid' => $userId)
                    );

                break;
                case HM_Role_Abstract_RoleModel::ROLE_ATMANAGER:
                case HM_Role_Abstract_RoleModel::ROLE_ATMANAGER_LOCAL: // см. #16691
                    $find = $this->getService('AtManager')->fetchAll(array('user_id = ?' => $userId));
                    if(count($find) > 0){
                        $result = false;
                        break;
                    }
                    $this->getService('AtManager')->insert(
                        array('user_id' => $userId)
                    );

                break;
                case HM_Role_Abstract_RoleModel::ROLE_HR:
                case HM_Role_Abstract_RoleModel::ROLE_HR_LOCAL: // см. #16691
                    $find = $this->getService('Recruiter')->fetchAll(array('user_id = ?' => $userId));
                    if(count($find) > 0){
                        $result = false;
                        break;
                    }
                    $this->getService('Recruiter')->insert(
                        array('user_id' => $userId)
                    );

                    break;
                case 'admin':
                    $find = $this->getService('Admin')->fetchAll(array('MID = ?' => $userId));

                    if(count($find) > 0){
                        $result = false;
                        break;
                    }

                    $this->getService('Admin')->insert(
                        array('MID' => $userId)
                    );

                break;
                case 'simple_admin':
                    $find = $this->getService('SimpleAdmin')->fetchAll(array('MID = ?' => $userId));

                    if(count($find) > 0){
                        $result = false;
                        break;
                    }

                    $this->getService('SimpleAdmin')->insert(
                        array('MID' => $userId)
                    );

                    break;
                case HM_Role_Abstract_RoleModel::ROLE_DEAN:
                case HM_Role_Abstract_RoleModel::ROLE_DEAN_LOCAL:
                    $find = $this->getService('Dean')->fetchAll(array('MID = ?' => $userId));

                    if(count($find) > 0){
                        $result = false;
                        break;
                    }

                    $this->getService('Dean')->insert(
                        array('MID' => $userId)
                    );

                break;
                case HM_Role_Abstract_RoleModel::ROLE_CURATOR:

                    $find = $this->getService('Curator')->fetchAll(array('MID = ?' => $userId));

                    if(count($find) > 0){
                        $result = false;
                        break;
                    }

                    $this->getService('Curator')->insert(array(
                        'MID' => $userId,
                        'project_id' => 0
                    ));

                    break;
                case HM_Role_Abstract_RoleModel::ROLE_LABOR_SAFETY:
                case HM_Role_Abstract_RoleModel::ROLE_LABOR_SAFETY_LOCAL:
                    $find = $this->getService('LaborSafety')->fetchAll(array('user_id = ?' => $userId));
                    if(count($find) > 0){
                        $result = false;
                        break;
                    }
                    $this->getService('LaborSafety')->insert(
                        array('user_id' => $userId)
                    );

                    break;
            }

            if (!$this->getService('Acl')->inheritsRole($role, HM_Role_Abstract_RoleModel::ROLE_ENDUSER)) {
                $roleNames[$role] = $roles[$role];
            }
        }

        if ( !$result ) {
            return false;
        }

        if (in_array($role, array(
            HM_Role_Abstract_RoleModel::ROLE_DEAN,
            HM_Role_Abstract_RoleModel::ROLE_LABOR_SAFETY,
            HM_Role_Abstract_RoleModel::ROLE_HR,
            HM_Role_Abstract_RoleModel::ROLE_ATMANAGER,
        ))) {
            $this->getService('Responsibility')->resetResponsibility($userId);
        }

        if (count($roleNames)) {
            $messenger = $this->getService('Messenger');

            $urls = array();
            $site = Zend_Registry::get('view')->serverUrl('/');
            foreach ($roleNames as $roleId => $roleName) {
                $urls[] = sprintf('<a href="%smanual/%s.pdf">%s</a>', $site, $roleId, $roleName);
            }
            $messenger->setOptions(
                HM_Messenger::TEMPLATE_ASSIGN_ROLE,
                    array('role' => implode(', ', $roleNames), 'url_manual' => implode(', ', $urls))
            );
            $messenger->send(HM_Messenger::SYSTEM_USER_ID, $userId);
        }

        return true;
    }

    /**
     *
     * Сохраняем изображение в папку
     * @param unknown_type $userId
     * @param unknown_type $postId
     * @return string|string|string
     */
    public function addPhoto($userId, $postId = 'photo')
    {
        $config = Zend_Registry::get('config');

        $upload = new Zend_File_Transfer();

        $upload->setAdapter('Http');
        $files = $upload->getFileInfo();
        //print_r($files);
        $photo = $files[$postId];

        if (!$upload->isUploaded()) {
            return false;
        }

        $image = getimagesize($photo['tmp_name']);
        $ext ='';
        if($image[2] && IMAGE_JPG){
            $ext ='jpg';
        }elseif($image[2] && IMAGE_PNG){
            $ext ='png';
        }elseif($image[2] && IMAGE_GIF){
            $ext ='gif';
        }else{
            return false;

        }
        $getpath = $this->getPath($config->path->upload->photo,$userId);

        if(!$upload->isValid()){
            return false;
        }

        $glob = glob($getpath . $userId .'.*');

        foreach($glob as $value){
            unlink($value);
        }

        $upload->receive();
        $img = PhpThumb_Factory::create($getpath . $userId . '.' .$ext);
        $img->resize(HM_User_UserModel::PHOTO_WIDTH, HM_User_UserModel::PHOTO_HEIGHT);
        $img->save($getpath . $userId . '.' .$ext);

        return true;
    }

    public function addPhotoFromStr($userId, $str)
    {
        $config = Zend_Registry::get('config');

        $getpath = $this->getPath($config->path->upload->photo,$userId);
        $glob = glob($getpath . $userId .'.*');

        foreach($glob as $value){
            unlink($value);
        }

        $f = fopen($getpath . $userId . '.jpg', 'w');
        fwrite($f, $str);
        fclose($f);

        return true;
    }


    /**
     * Потом вынести функцию в общий класс, который будет
     * выдавать каталог
     *
     *
     */
    public function getPath($filePath, $id)
    {
        $config = Zend_Registry::get('config');
// нельзя использовать realpath в случае когда upload есть ссылка, которую юзают одновременно 2 сервера
//         $filePath = realpath($filePath);

        if(!is_dir($filePath)){
            return false;
        }
        $maxFilesCount = (int) $config->path->upload->maxfilescount;
        $path = floor($id / $maxFilesCount);
        if(!is_dir($filePath . DIRECTORY_SEPARATOR . $path)){
            $old_umask = umask(0);
            mkdir($filePath . DIRECTORY_SEPARATOR . $path, 0777);
            chmod($filePath . DIRECTORY_SEPARATOR . $path, 0777);
            umask($old_umask);
        }
        return  $filePath . DIRECTORY_SEPARATOR . $path . DIRECTORY_SEPARATOR;
    }


    public function getImageSrc($userId){
        $config = Zend_Registry::get('config');
        $getpath = $this->getPath($config->path->upload->photo, $userId);
        $maxFilesCount = (int) $config->path->upload->maxfilescount;
        $glob = glob($getpath . $userId .'.*');
        foreach($glob as $value){
            return floor($userId / $maxFilesCount) . '/' . basename($value);
        }
        return false;

    }

    public function getRandomString($maxLength = null)
    {
        $passwordOptions = $this->getService('Option')->getOptions(HM_Option_OptionModel::SCOPE_PASSWORDS);
        if($maxLength == null){
            $maxLength = $passwordOptions['passwordMinLength'] > 0 ? $passwordOptions['passwordMinLength'] : HM_User_UserModel::PASSWORD_LENGTH;
        }
        $str ='';
        if($passwordOptions['passwordCheckDifficult'] != 1){
            $array = array_merge(range('a','z'), range('A','Z'), range('0','9'));
            $amount = count($array)-1;
            for($i = 0; $i < $maxLength; $i++){
                $str.=$array[mt_rand(0, $amount)];
            }
        }else{
            $alpha = range('a','z'); //array_merge(range('a','z'), range('а', 'я'), array('ё'));
            $alphaBig = range('A','Z'); //array_merge(range('A','Z'), range('А', 'Я'), array('Ё'));
            $numeric = range(0, 9);
            $symbol = array('$', '#', '!', '%');

            $result = array();

            for($i = 0; $i <= $maxLength; $i++){
                $arr = array();
                if($i % 4 == 0){
                    $arr = $symbol;
                }elseif($i % 4 == 1){
                    $arr = $numeric;
                }elseif($i % 4 == 2){
                    $arr = $alphaBig;
                }elseif($i % 4 == 3){
                    $arr = $alpha;
                }
                $result[] = $arr[array_rand($arr)];
            }

            shuffle($result);
            $str = implode('', $result);

        }
        return $str;
    }

    private function _initRole($user)
    {
        if (count($user->roles)) {
            $availableRoles = $user->roles->asArrayOfObjects('level');

            $newArr = array();
            foreach ($availableRoles as $availableRole) {
                $newArr[$availableRole->role] = $availableRole->role;
            }
            $user->role = $availableRole->role;
            $user->roles = $newArr;

        } else {
            $user->role = HM_Role_Abstract_RoleModel::ROLE_GUEST;
            $user->roles = array(HM_Role_Abstract_RoleModel::ROLE_GUEST);
        }

        if (in_array(HM_Role_Abstract_RoleModel::ROLE_ADMIN, $user->roles)) {
            $this->getService('Log')->log(
                $this->getCurrentUserId(),
                'Admin login',
                'Success',
                Zend_Log::NOTICE
            );
        }

        if ($user->role !== HM_Role_Abstract_RoleModel::ROLE_GUEST) {
            $this->getService('Guest')->setNotGuest();
        }

        return $user->role;
    }

    public function initUserIdentity($user)
    {
        $auth = Zend_Auth::getInstance();
        $auth->setStorage(new Zend_Auth_Storage_Session('default'));
        $auth->getStorage()->write($user);
    }

    private function _init($user)
    {
        $this->getService('Captcha')->delete($user->Login);
        $this->getService('Captcha')->purge();

        $user->invalid_login = 0;
        $user->countlogin++;
        $this->update($user->getValues(null, array('role', 'Password', 'email_backup')));

        $this->initUserIdentity($user);

        Zend_View_Helper_Navigation_HelperAbstract::setDefaultRole($user->role);

        if ($user->lang != $this->getCurrentLangId()) {
            $user = $this->update(array('lang' => $this->getCurrentLangId(), 'MID' => $user->MID));
        }

        setcookie('hmexit', 'true', time() - 3600, '/');
        setcookie(HM_User_UserService::COOKIE_NAME_LANG, $user->lang, 0, '/');
    }

    public function authorizeByKey($key)
    {
        $session = $this->getOne(
            $this->getService('Session')->fetchAll(
                $this->quoteInto(
                    'sesskey = ?', $key
                )
            )
        );

        if ($session && ($session->ip ==  $_SERVER["REMOTE_ADDR"])) {
            $user = $this->getOne(
                $this->findDependence('Role', $session->mid)
            );

            if (!$user->blocked) {
                $this->_initRole($user);
                $this->getService('Unmanaged')->initUnmanagedSession($user);
                $this->_init($user);

                $this->getService('Session')->setAuthorizerKey();

                return $user;
            }
        }

        setcookie('hmkey', '', time() - 3600, '/');

        return false;
    }

    public function authorizeByLogin($login, $domain)
    {
        $this->getService('Log')->log(
            $this->getCurrentUserId(),
            'Login attempt: ' . $domain . '\\' . $login,
            'Success',
            Zend_Log::NOTICE
        );

        $users = $this->fetchAllDependence('Role',
            $this->quoteInto(
                array('blocked != 1 AND Login = ?', ' AND Domain = ?'),
                array($login, $domain)
            )
        );

        if (count($users) == 1) {
            $user = $users->current();
            if (!$user->blocked) {
                $this->_initRole($user);
                $this->getService('Unmanaged')->initUnmanagedSession($user);
                $this->_init($user);

                return $user;
            }
        }

        return false;
    }

    public function authorizeByLdap($login, $password)
    {
        if (Zend_Registry::get('config')->ldap->authorization) {
            $adapter = new Zend_Auth_Adapter_Ldap(array('server' => Zend_Registry::get('config')->ldap->options->toArray()), $login, $password);
            if ($result = $adapter->authenticate()) {
                if ($result->getCode() == Zend_Auth_Result::SUCCESS) {
                    return true;
                }
            }
        }

        return false;
    }

    public function authorizeOnBehalf($userId)
    {
        $default = new Zend_Session_Namespace('default');

        $users = $this->findDependence('Role', $userId);
        if (count($users) == 1) {
            $user = $users->current();
            $this->_initRole($user);
            $this->getService('Unmanaged')->initUnmanagedSession($user);

            if ($user->role != HM_Role_Abstract_RoleModel::ROLE_GUEST) {

                $default->userRestore = $this->getCurrentUser();
                $default->userRoleRestore = $this->getCurrentUserRole();
                $default->urlRestore = $_SERVER['HTTP_REFERER'];

                $this->_init($user);

                return $user;
            }
        }

        return false;
    }

    public function restore()
    {
        $default = new Zend_Session_Namespace('default');
        if (isset($default->userRestore)) {
            $user = clone $default->userRestore;

            $user->role = $default->userRoleRestore;
            if ($user->role !== HM_Role_Abstract_RoleModel::ROLE_GUEST) {
                $this->getService('Guest')->setNotGuest();
            }
            $url = $default->urlRestore;

            $this->_init($user);
            unset($default->userRestore);
            unset($default->userRoleRestore);
            unset($default->urlRestore);

            return $url;
        }
    }

    public function _getTokenSignature($userId)
    {
        return md5($userId.'@#%$~#%!#$%!@#%!@#qazwsx');
    }

    public function _authorizeByToken($token)
    {
        list($userId, $signature) = explode('_', $token);
        if (!$token || !$userId || !$signature) {
            throw new HM_Exception_Auth(_('Некорректный токен'), 0);
        }
        if($signature != $this->_getTokenSignature($userId))
            throw new HM_Exception_Auth(_('Токен подделан'), 0);

        $collection = $this->fetchAllDependence('Role',
                $this->quoteInto(array('MID = ?'), array($userId))
        );

        $user = $this->getOne($collection);

        if(!$user || $user->blocked)
            throw new HM_Exception_Auth(_('Пользователь не найден или заблокирован.'),0);

        $this->_initRole($user);
        $this->getService('Unmanaged')->initUnmanagedSession($user, false);
        $this->_init($user);
/*
http://develop50/?client-security-token=1_a09c0ce01398193a6147b6f8dcf1964b
*/
        // Принудительно переключаем роль на студента, для доступа к курсам
        $this->switchRole('enduser');
        $this->switchRole('student');

        return $user;
    }

    public function authorizeByToken($token=false)
    {
        if($_SERVER['REQUEST_METHOD']=='OPTIONS') { //Иначе приложение не смогет передавать client-security-token
            header("Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With, ".HM_User_UserService::SECUTITY_TOKEN_NAME);
            die();
        }

        if($token) {
            return  $this->_authorizeByToken($token);
        }

        $request = Zend_Controller_Front::getInstance()->getRequest();
        $route = $request->getControllerName() . ':' . $request->getActionName();
        if(!($route=='auth:login' || $route=='auth:can-run'))  //Проверяем токен если не авторизация или валидация
        {
            $client_security_token = $this->getSecurityToken($request->getParams());
            if(!$client_security_token) {
                http_response_code(403);
                die();
            }
            return  $this->_authorizeByToken($client_security_token);
        }
    }

    public function getSecurityToken($params=false)
    {
            $HEADERS = getallheaders2();
            return isset($HEADERS[HM_User_UserService::SECUTITY_TOKEN_NAME]) ? $HEADERS[HM_User_UserService::SECUTITY_TOKEN_NAME] : (isset($_POST[HM_User_UserService::SECUTITY_TOKEN_NAME]) ? $_POST[HM_User_UserService::SECUTITY_TOKEN_NAME] : ($params && isset($params[HM_User_UserService::SECUTITY_TOKEN_NAME]) ? $params[HM_User_UserService::SECUTITY_TOKEN_NAME] : (isset($_GET[HM_User_UserService::SECUTITY_TOKEN_NAME]) ? $_GET[HM_User_UserService::SECUTITY_TOKEN_NAME] : false)));
    }

    /**
     * @throws HM_Exception_Auth
     * @param  string $login
     * @param  string $password
     * @return bool|HM_Model_Abstract
     */
    public function authorize($login, $password, $authorizedByLdap = false, $authorizedBySomethingElse = false, $systemInfo = false)
    {
        if ($authorizedBySomethingElse) {
            $collection = $this->fetchAllDependence('Role',
                $this->quoteInto(array('Login = ?'), array($login))
            );
        } else {
            $collection = $this->fetchAllDependence('Role',
                $this->quoteInto(array('Login = ?', ' AND Password = PASSWORD(?)'), array($login, $password))
            );
        }

        if (count($collection) > 1) {
            $collection = $this->fetchAllDependence('Role',
                $this->quoteInto(array('Login = ?', ' AND Password = PASSWORD(?) AND blocked=0 '), array($login, $password))
            );
            if (count($collection) > 1) {
                throw new HM_Exception_Auth(_('Больше одного пользователя с таким логином.'), 0);
            }
        }

        $user = $this->getOne($collection);

        $passwordOptions = $this->getService('Option')->getOptions(HM_Option_OptionModel::SCOPE_PASSWORDS);

        if ($user/* && (!$user->isImportedFromAD() || ($user->isImportedFromAD() && $authorizedByLdap))*/) {

            //$user = $this->getOne($collection);
            if ($user->blocked) {
                $message = _('Ваш аккаунт заблокирован.');
                if (strlen($user->block_message)) {
                    $message .= sprintf(_(' Администратор указал следующую причину: %s'), $user->block_message);
                }
                throw new HM_Exception_Auth($message, 0);
            }

            setcookie('hmcaptcha', '', time() - 3600, '/');

            if($passwordOptions['passwordMaxPeriod'] > 0){
                $lastPasswordChangeDate = $this->getService('UserPassword')->getChangePasswordLastDate($user->MID);

                if(((time() - strtotime($lastPasswordChangeDate)) > (3600 * 24 * $passwordOptions['passwordMaxPeriod']) || strtotime($lastPasswordChangeDate) == 0) && !$user->force_password) {
                    $user->force_password = 1;
                    // не перезаписывать текущую модель
                    $this->update($user->getValues(), false);
                }
            }

            $this->_initRole($user);
            $this->getService('Unmanaged')->initUnmanagedSession($user, $systemInfo);
            $this->_init($user);

            return $user;

        } else {
            $user = $this->getOne(
                $this->fetchAll(
                    $this->quoteInto('Login = ?', $login)
                )
            );


            if ($user) {
                if ($this->authorizeByLdap($login, $password)) {
                    $user->Password = new Zend_Db_Expr("PASSWORD(".$this->quoteInto('?', $password).")");
                    $user = $this->update($user->getValues());
                    return $this->authorize($login, $password, true, false, $systemInfo);
                }

                $user->invalid_login++;
                $this->update($user->getValues(), false);
            }

            $this->getService('Captcha')->attempt($login);

            $captcha = $this->getService('Captcha')->getOne($this->getService('Captcha')->find($login));
            if($captcha && $user){

                if($captcha->attempts >= $passwordOptions['passwordMaxFailedTry'] && $passwordOptions['passwordRestriction'] == HM_User_Password_PasswordModel::RESTRICTION_WITH && $passwordOptions['passwordFailedActions'] == HM_User_Password_PasswordModel::TYPE_BLOCK){
                    $user->blocked = 1;
                    $user = $this->update($user->getValues());
                }
            }
        }

        throw new HM_Exception_Auth(_('Вы неверно ввели имя пользователя или пароль.'),0);

    }

    public function logout()
    {
        $user = $this->getCurrentUser();

        $this->getService('UserLoginLog')->logout($user->Login, _('Пользователь успешно вышел из системы.'), HM_User_Loginlog_LoginlogModel::STATUS_OK);

        $auth = Zend_Auth::getInstance();
        $auth->clearIdentity();
        setcookie('hmkey', '', time() - 3600, '/');
        setcookie('hmexit', 'true', 0, '/');

        // помечаем пользователя как гостя
        $this->getService('Guest')->setSession(0);
    }

    public function isRoleExists($userId, $role)
    {
        $collection = $this->getService('Role')->fetchAll(array(
            'user_id = ?' => $userId,
            'role = ?' => $role,
        ));
        return (bool)count($collection);
    }

    /**
     * Возвращает массив ролей назначеных пользователю с ИД $userId
     * в данный массив не попадает роль user усли у пользователя имеется любая другая роль.
     * @param int $userId
     * @return array
     */
    public function getUserRoles($userId)
    {
        if ($userId) {
            
            // $collection = $this->getService('Role')->fetchAll(array(
            //     'user_id = ?' => $userId,
            // ), 'level')->getList('role');

            $currentUser = $this->getService('User')->getCurrentUser();
            $collection = $currentUser->roles;

            $roles = HM_Role_Abstract_RoleModel::getBasicRoles(true, true);

            $actualRoles = array_intersect_key($collection, $roles);

            return $actualRoles;   // return $collection->getList('role');
        }
        return array();
    }


    /**
     * Удаляет роль $role для пользователя $userId
     * @param int $userId
     * @param string $role
     * @return boolean
     */
    public function removalRole($userId, $role)
    {
        switch($role) {
            case HM_Role_Abstract_RoleModel::ROLE_ADMIN:
                $this->getService('Admin')->deleteBy($this->quoteInto('MID = ?', $userId));
                return true;
                break;
            case HM_Role_Abstract_RoleModel::ROLE_SIMPLE_ADMIN:
                $this->getService('SimpleAdmin')->deleteBy($this->quoteInto('MID = ?', $userId));
                return true;
                break;
            case HM_Role_Abstract_RoleModel::ROLE_MANAGER:
                $this->getService('Manager')->deleteBy($this->quoteInto('mid = ?', $userId));
                return true;
                break;
            case HM_Role_Abstract_RoleModel::ROLE_ATMANAGER:
                $this->getService('AtManager')->deleteBy($this->quoteInto('user_id = ?', $userId));
                return true;
                break;
            case HM_Role_Abstract_RoleModel::ROLE_DEVELOPER:
                $this->getService('Developer')->deleteBy($this->quoteInto('mid = ?', $userId));
                return true;
                break;
            case HM_Role_Abstract_RoleModel::ROLE_DEAN:
                $this->getService('Dean')->deleteBy($this->quoteInto('MID = ?', $userId));
                return true;
                break;
            case HM_Role_Abstract_RoleModel::ROLE_LABOR_SAFETY:
                $this->getService('LaborSafety')->deleteBy($this->quoteInto('user_id = ?', $userId));
                return true;
                break;
            case HM_Role_Abstract_RoleModel::ROLE_TEACHER:
                $this->getService('Teacher')->deleteBy($this->quoteInto('MID = ?', $userId));
                return true;
                break;
//            case HM_Role_Abstract_RoleModel::ROLE_MODERATOR:
//                $this->getService('Moderator')->deleteBy($this->quoteInto('user_id = ?', $userId));
//                return true;
//                break;
            case HM_Role_Abstract_RoleModel::ROLE_STUDENT:
                $this->getService('Student')->deleteBy($this->quoteInto('MID = ?', $userId));
                return true;
                break;
            case HM_Role_Abstract_RoleModel::ROLE_SUPERVISOR:
                $this->getService('Supervisor')->deleteBy($this->quoteInto('user_id = ?', $userId));
                return true;
                break;
            case HM_Role_Abstract_RoleModel::ROLE_CURATOR:
                $this->getService('Curator')->deleteBy($this->quoteInto('MID = ?', $userId));
                return true;
                break;
            case HM_Role_Abstract_RoleModel::ROLE_HR:
                $this->getService('Recruiter')->deleteBy($this->quoteInto('user_id = ?', $userId));
                return true;
                break;
        }
        return false;
    }

    public function switchRole($role)
    {
        $roles = HM_Role_Abstract_RoleModel::getBasicRoles(true, true);

        if ($role && (isset($roles[$role]) || isset($customRoles[$role])) && $this->isRoleExists($this->getCurrentUserId(), $role)) {

            if ($role == HM_Role_Abstract_RoleModel::ROLE_ADMIN) {
                $this->getService('Log')->log(
                    $this->getCurrentUserId(),
                    'Switch role to admin',
                    'Success',
                    Zend_Log::NOTICE
                );
            }

            $auth = Zend_Auth::getInstance();
            $auth->setStorage(new Zend_Auth_Storage_Session('default'));
            $user = $auth->getIdentity();
            $user->role = $role;
            $auth->getStorage()->write($user);

        } else {
            if($role == HM_Role_Abstract_RoleModel::ROLE_ADMIN){
                $this->getService('Log')->log(
                    $this->getCurrentUserId(),
                    'Switch role to admin',
                    'Fail',
                    Zend_Log::WARN
                );
            }
        }
    }

    public function delete($id)
    {
        $id = (int) $id;

        $this->getService('Developer')->deleteBy(array('mid = ?' => $id));
        $this->getService('Dean')->deleteBy(array('MID = ?' => $id));
        $this->getService('Student')->deleteBy(array('MID = ?' => $id));
        $this->getService('Admin')->deleteBy(array('MID = ?' => $id));
        $this->getService('Manager')->deleteBy(array('mid = ?' => $id));
        $this->getService('Teacher')->deleteBy(array('MID = ?' => $id));
        $this->getService('Supervisor')->deleteBy(array('user_id = ?' => $id));
        $this->getService('GroupAssign')->deleteBy(array('mid = ?' => $id));
        $this->getService('Claimant')->deleteBy(array('MID = ?' => $id));
        $this->getService('LessonAssign')->deleteBy(array('MID = ?' => $id));
        $this->getService('RoleCustomAssign')->deleteBy(array('mid = ?' => $id));

        // Очистка поля mid в оргструктуре
        $this->getService('Orgstructure')->updateWhere(
            array('MID' => NULL),
            array('MID = ? ' => $id)
        );

        $this->getService('Orgstructure')->deleteBy(array('mid = ?' => $id));
        $this->getService('StudyGroupUsers')->deleteBy(array('user_id = ?' => $id));
        $this->getService('ProgrammUser')->deleteBy(array('user_id = ?' => $id));

        $this->getService('UserAdditionalFields')->deleteBy(array('user_id = ?' => $id));

        //assessment
        $this->getService('Orgstructure')->updateWhere(array(
            'mid' => 0
        ), array(
            'mid = ?' => $id
        ));

        $collection = $this->getService('AtSessionUser')->fetchAll($this->getService('AtSessionUser')->quoteInto('user_id = ?', $id));
        $session_user_ids = $collection->getList('session_user_id', 'session_user_id');

        $this->getService('AtSessionUser')->deleteBy(array('user_id = ?' => $id));
        $this->getService('AtSessionRespondent')->deleteBy(array('user_id = ?' => $id));

        $collection = $this->getService('AtSessionEvent')->fetchAll($this->getService('AtSessionEvent')->quoteInto(array('user_id = ? OR', ' respondent_id = ?' ), array($id, $id)));
        $session_event_ids = $collection->getList('session_event_id', 'session_event_id');

        $this->getService('AtSessionEvent')->deleteBy($this->getService('AtSessionEvent')->quoteInto(array('user_id = ? OR', ' respondent_id = ?' ), array($id, $id)));
        $this->getService('AtKpiUser')->deleteBy(array('user_id = ?' => $id));
        $this->getService('AtSessionEventAttempt')->deleteBy(array('user_id = ?' => $id));
        $this->getService('AtRelation')->deleteBy(array('user_id = ?' => $id));
        $this->getService('AtProfile')->deleteBy(array('user_id = ?' => $id));

        if($session_user_ids){
            $this->getService('AtSessionUserCriterionValue')->deleteBy(array('session_user_id IN (?)' => $session_user_ids));
        }

        if($session_event_ids){
            $this->getService('AtSessionEventLesson')->deleteBy(array('session_event_id IN (?)' => $session_event_ids));
            $this->getService('AtEvaluationResults')->deleteBy(array('session_event_id IN (?)' => $session_event_ids));
            $this->getService('AtEvaluationIndicator')->deleteBy(array('session_event_id IN (?)' => $session_event_ids));
            $this->getService('AtEvaluationResults')->deleteBy(array('session_event_id IN (?)' => $session_event_ids));
            $this->getService('AtEvaluationMemoResult')->deleteBy(array('session_event_id IN (?)' => $session_event_ids));
        }

        parent::delete($id);
    }

    /**
     * @param  int $number
     * @param  string $prefix
     * @param  string $password
     * @param  string $role
     * @return HM_Collection
     */
    public function generate($number, $prefix, $password, $role)
    {
        $users = new HM_Collection(array(), 'HM_User_UserModel');
        $count = $i = 0;
        while($count < $number) {
            $login = $prefix.str_pad((string) $i, 3, "0", STR_PAD_LEFT);
            $collection = $this->fetchAll($this->quoteInto('Login = ?', $login));
            if (count($collection)) {
                $i++;
                continue;
            } else {
                $user = $this->insert(
                    array(
                        'Login' => $login,
                        'Password' => new Zend_Db_Expr(sprintf("PASSWORD('%s')", $password)),
                        'need_edit' => 1
                    )
                );
                if ($user) {
                    $this->assignRole($user->MID, $role);
                }
                $users[count($users)-1] = $user;
                $count++;
            }
        }
        return $users;
    }

    public function generateLogin()
    {
        $i = 0;

        $users = $this->fetchAll(
            array(
            	"Login LIKE ?" => self::NEW_LOGIN_PREFIX . "%",
                //"MAX(ABS(REPLACE(Login, ?, '')))" => self::NEW_LOGIN_PREFIX
            ),
            'Login DESC', 500);

            // Last $user save!!!
        foreach($users as $user){
            if(preg_match("/" . self::NEW_LOGIN_PREFIX . "([0-9]{4,})/i", $user->Login, $match)){
                break;
            }

        }

		if ($user) {
			$i = (int) substr($user->Login, strlen(self::NEW_LOGIN_PREFIX));
		}

        while(true) {
            $login = self::NEW_LOGIN_PREFIX.str_pad((string) $i, 4, "0", STR_PAD_LEFT);
            $collection = $this->fetchAll($this->quoteInto('Login = ?', $login));
            if (count($collection)) {
                $i++;
                continue;
            } else {
                return $login;
            }
        }
    }

    public function getMetadataArrayFromForm(Zend_Form $form)
    {
        return array(
// #11006 - поля вынесены из мета-кучи
//           'gender' => $form->getValue('gender'),
//           'year_of_birth' => $form->getValue('year_of_birth'),
           'tel' => $form->getValue('tel'),
           'team' => $form->getValue('team'),
           'additional_info' => $form->getValue('additional_info')
        );
    }

    public function isLoginExists($login, $domain)
    {
        return $this->getOne($this->fetchAll(
            $this->quoteInto(
                array('Login = ?', ' AND Domain = ?'),
                array($login, $domain)
            )
        ));
    }

    public function getUsersOnline(array $usersList = array())
    {
        $config = Zend_Registry::get('config');
        $lifetime = ($seconds = ini_get('session.gc_maxlifetime')) ? $seconds : (int)$config->user->onlinetimeout;
        $select = $this->getSelect();
        $select->from('sessions', array('mid'))
               ->where('stop >= ?',  date('Y-m-d H:i:s', time() - $lifetime));
        if (count($usersList)) {
            $select->where('mid IN ('. implode(',', $usersList) .')');
        }
        $query = $select->query();
        $usersOnline = array();
        $res = $query->fetchAll();
        foreach($res as $item) {
            $usersOnline[] = $item['mid'];
        }
        return $usersOnline;
    }


    public function duplicateInsert($data, $notifyUser = false)
    {
        $lastName = $data['LastName'] ? $data['LastName'] : $data['lastname'];
        $firstName = $data['FirstName'] ? $data['FirstName'] : $data['firstname'];
        $patronymic = $data['Patronymic'] ? $data['Patronymic'] : $data['patronymic'];
        $email = $data['EMail'] ? $data['EMail'] : $data['email'];
        $phone = $data['Phone'] ? $data['Phone'] : $data['phone'];

        // Ищем полное совпадение
        $user = $this->fetchRow(
//            $this->getOne(
//            $this->fetchAll(
                $this->quoteInto(
                    array(
                        ' ( LastName LIKE ? ',
                        ' AND FirstName LIKE ? ',
                        ' AND Patronymic LIKE ? ) ',
                        ' AND EMail LIKE ? ',
                        ' AND Phone LIKE ? ',
//                        ' AND BirthDate LIKE ? ',
                    ),
                    array(
                        $lastName,
                        $firstName,
                        $patronymic,
                        $email,
                        $phone
//                        $data['BirthDate'] ? $data['BirthDate'] : $data['birthdate'],
                    )
                )
//            )
        );

        if ($user) {
            $data['MID'] = $user->MID;
            unset($data['blocked']);
            $returnUser = $this->update($data);
        } else {
            // Берем первого попавшегося, но может быть много потенциальных кандидатов
            $user =
                $this->fetchRow(
//                $this->getOne(
//                $this->fetchAll (
                    '(' .
                        $this->quoteInto(
                            [
                              '     LastName LIKE ? ',
                              ' AND FirstName LIKE ?',
                              ' AND Patronymic LIKE ?'
                            ],
                            [ $lastName, $firstName, $patronymic ]
                        ) .
                    ')' .
                    $this->quoteInto(
                        [
                            ' OR  ( EMail LIKE ?  AND  EMail <> \'\' )',
                            ' OR  ( Phone LIKE ?  AND  Phone <> \'\' )',
                        ],
                        [ $email, $phone]
//                    )
                )
            );
            if ($user) {
                $data['duplicate_of'] = $user->MID;
                $returnUser = $this->insert($data, $notifyUser);
            }
            else {
                $returnUser = $this->insert($data, $notifyUser);
            }
        }
        return $returnUser;

    }


    public function insert($data, $unsetNull = true, $notifyUser = false)
    {
        if($data['MID'] != '') {
        $this->getService('UserPassword')->insert(
            array(
            	'user_id' => $data['MID'],
                'password' => md5($data['Password']),
                'change_date' => date('Y-m-d H:i:s')
            )
        );
        }
        $data['Registered'] = date('Y-m-d H:i:s');
        $user =  parent::insert($data);


        if ($notifyUser) {
//[ES!!!] //array('notify' => $notify)
        }

        return $user;

    }

    public function update($data, $updatePasswordHistory = true)
    {
        if($updatePasswordHistory && isset($data['Password']) && $data['Password']!= ""){
            $userId = $data['MID'];
            $this->getService('UserPassword')->insert(
                array(
                	'user_id' => $data['MID'],
                    'password' => md5($data['Password']),
                    'change_date' => date('Y-m-d H:i:s')
                )
            );
        }
        return parent::update($data);
    }

    /**
     * Notifies users that they are unblocked
     *
     * @param array $data contains 'id' of user(or users) for unblock, and 'placeholders' key
     * @param integer $template_id is template id
     * @param integer $channel_id  is channel_id
     * @see \library\HM\HM_Messenger.php
     */
    public function notifyUserUnblock($data, $template_id = HM_Messenger::TEMPLATE_UNBLOCK , $channel_id = HM_Messenger::SYSTEM_USER_ID) {
        $data = (array)$data + array (
            'id' => null,
            'placeholders' => array(),
        );
        $messenger = $this->getService('Messenger');
        foreach ((array)$data['id'] as $id) {
            $messenger->addMessageToChannel(
                $channel_id,
                $id,
                $template_id,
                $data['placeholders']
            );
        }
        $messenger->sendAllFromChannels();
    }

    public function checkResponsibility($select)
    {
        if($this->getService('Acl')->checkRoles(HM_Responsibility_ResponsibilityModel::getResponsibilityRoles())) {
            $select = $this->getService('Responsibility')->checkUsers($select);
        }
        return $select;
    }

    public function getUnitInfo($user_id)
    {
        $units = $this->getService('Orgstructure')->fetchAll(array(
            'MID = ?' => $user_id,
            'blocked = ?' => 0,
        ));
        $info = array();
        foreach($units as $unit){
            $info[] = $this->getService('Orgstructure')->getInfo($unit);
        }
        return $info;
    }

    public function pluralFormRolesCount($count)
    {
        return !$count ? _('Нет') : sprintf(_n('роль plural', '%s роль', $count), $count);
    }

    public function pluralFormRolesCountVue($count)
    {
        $count = intval($count);
        return '{{ _pl("роль plural", ' . $count . ') }}';
    }

    public function pluralFormUsersCount($count)
    {
        return !$count ? _('Нет') : sprintf(_n('пользователь plural', '%s пользователь', $count), $count);
    }

    public function getSubjects($userId = null)
    {
        if (null === $userId) {
            $userId = $this->getCurrentUserId();
        }

        if ($this->getService('Acl')->inheritsRole($this->getService('User')->getCurrentUserRole(), HM_Role_Abstract_RoleModel::ROLE_ENDUSER)) {
            $collections = $this->getService('Student')->getSubjects($userId);
        } elseif ($this->getService('Acl')->inheritsRole($this->getService('User')->getCurrentUserRole(), HM_Role_Abstract_RoleModel::ROLE_TEACHER)) {
            $collections = $this->getService('Teacher')->getSubjects($userId);
        } elseif ($this->getService('Acl')->inheritsRole($this->getService('User')->getCurrentUserRole(), HM_Role_Abstract_RoleModel::ROLE_LABOR_SAFETY)) {
            $collections = $this->getService('LaborSafety')->getSubjects($userId);
        } elseif ($this->getService('Acl')->inheritsRole($this->getService('User')->getCurrentUserRole(), HM_Role_Abstract_RoleModel::ROLE_DEAN)) {
            $collections = $this->getService('Dean')->getSubjects($userId);
        } else {
            $collections = new HM_Collection();
        }


        // не отображаем прошедшие курсы
//        foreach ($collections as $offset => $subject) {
//            if ($subject->period == HM_Subject_SubjectModel::PERIOD_DATES && $subject->end) {
//                $end  = new HM_Date($subject->end);
//                $curr = new HM_Date();
//                if ($end->getTimestamp() < $curr->getTimestamp()) {
//                    $collections->offsetUnset($offset);
//                }
//            }
//        }
        return $collections;
    }

    public function getProjects($userId = null)
    {
        if (null === $userId) {
            $userId = $this->getCurrentUserId();
        }

        if ($this->getService('Acl')->inheritsRole($this->getService('User')->getCurrentUserRole(), HM_Role_Abstract_RoleModel::ROLE_ENDUSER)) {
            $collections = $this->getService('Participant')->getProjects($userId);
//        } elseif ($this->getService('Acl')->inheritsRole($this->getService('User')->getCurrentUserRole(), HM_Role_Abstract_RoleModel::ROLE_MODERATOR)) {
//            $collections = $this->getService('Moderator')->getProjects($userId);
        } elseif ($this->getService('Acl')->inheritsRole($this->getService('User')->getCurrentUserRole(), HM_Role_Abstract_RoleModel::ROLE_CURATOR)) {
            $collections = $this->getService('Curator')->getProjects($userId);
        } else {
            $collections = new HM_Collection();
        }
        return $collections;
    }


    public function getGroups($userId = null)
    {
        if (null === $userId) {
            $userId = $this->getCurrentUserId();
        }

        $groups = new HM_Collection(array());

        if ($userId) {
            $collection = $this->getService('GroupAssign')->fetchAll($this->quoteInto('mid = ?', $userId));
            if (count($collection)) {
                $groups = $this->getService('Group')->fetchAll(
                    $this->quoteInto('gid IN (?)', $collection->getList('gid', 'gid'))
                );
            }
        }

        return $groups;
    }

    public function deleteDublicate($midDub)
    {
       // echo "дуб=".$midDub;
     //   echo "уникл=".$midUnic;
      //  exit;

        if (null !== $midDub)
        {
            //пытаемся сразу удалить дубликат
            $resultDelete = $this->deleteBy(array('MID = ?' => $midDub));
            if (null !== $resultDelete)
                return true;
            else
                return false;
        }
        else
            return false;
    }

    public static function getEmailConfirmationHash($userId)
    {
        return md5($userId . Zend_Registry::get('config')->privateKey);
    }

    public function checkEmailConfirmationHash($hash, $userId)
    {
        if (!empty($userId) && !empty($hash) && count($user = $this->fetchAll(array('MID = ?' => $userId, 'email_confirmed = ?' => 0)))) {
            return (md5($userId . Zend_Registry::get('config')->privateKey) === $hash) ? $user->current() : false;
        }
        return false;
    }

    public function getUsersByIds($ids = array())
    {
        if (count($ids)) {
            return $this->fetchAll($this->quoteInto('MID IN (?)', $ids));
        }
    }

    public function getById($id)
    {
        return $this->getOne($this->fetchAll($this->quoteInto('MID = ?', $id)));
    }

    /**
     *
     * @param string $where
     * @return array of user ids
     */

    public function getIds($where = null)
    {
        $select = $this->getSelect();
        $select->from('People', array('MID'));
        if($where <> null){
            $select->where($where);
        }
        $stmt = $select->query();
        $stmt->execute();
        $rows = $stmt->fetchAll();
        $ids = array();
        foreach ($rows as $value) {
            $ids[] = intval($value['MID']);
        }
        return $ids;
    }

    public function getAdditionalFields($userId) {
        $result = array();
        $data = $this->getService('UserAdditionalFields')->fetchAll('user_id='.$userId);
        foreach ($data as $row) {
            $result['userField_' . $row->field_id] = $row->value;
        }

        return $result;
    }

    public function updateAdditionalFields($userId, HM_Form $form) {
        $additionalService = $this->getService('UserAdditionalFields');
        $additionalService->deleteBy('user_id='.$userId);

        $fields = $this->getService('Option')->getOption('userFields');
        if ($fields) {
            $fields = json_decode($fields);
            foreach ($fields as $userField) {
                $value = $form->getValue('userField_' . $userField->field_id);
                if ($value) {
                    $additionalService->insert(array(
                        'user_id'  => $userId,
                        'field_id' => $userField->field_id,
                        'value'    => $value
                    ));
                }
            }
        }
    }

    public function isManager($mid = false, $asObject = false)
    {
        if (!$mid) $mid = $this->getCurrentUserId();

        if ($mid) {
            if (count($collection = $this->getService('Orgstructure')->fetchAll(array(
                'mid = ?' => $mid,
                'is_manager = ?' => HM_Orgstructure_Position_PositionModel::ROLE_MANAGER,
            )))) {
                return $asObject ? $collection->current() : $collection->current()->soid;
            }
        }
        return false;
    }

    public function isEndUser() {
        return $this->getService('Acl')
            ->inheritsRole(
                $this->getCurrentUserRole(),
                HM_Role_Abstract_RoleModel::ROLE_ENDUSER
            );
    }


    // DEPRECATED!
    // теперь (в СГК) резюме крепится к кандидату
    public function printResume($userId)
    {
        $user = call_user_func_array(array('HM_User_UserModel', 'factory'), array(array('MID' => $userId), 'HM_User_UserModel'));
        if ($path = $user->getResume()) {
            $filePath = Zend_Registry::get('config')->path->upload->resume . '/' . $path;
            $pathParts = pathinfo($filePath);
            $resourceReader = new HM_Resource_Reader($filePath, $pathParts['basename']);
            $resourceReader->readFile();
        }
        return '';
    }

    public function needDataAgreement()
    {
        if (!$this->getService('Option')->getOption('regRequireAgreement')) {
            return false;
        }

        $curUser = $this->getCurrentUser();
        if (!$curUser || !empty($curUser->data_agreement)) {
            return false;
        }

        $default = new Zend_Session_Namespace('default');
        if (isset($default->userRestore)) {
            return false;
        }

        return true;
    }

    /**
     *
     * @param type $fullName
     * фио в формате "Фамилия Имя Отчество" (регистронезависимое сравнение)
     * @param type $yearOfBirth
     * дата рождения в формате "дд.мм.гггг" (проверка только по году)
     * @return array возвращает массив данных пользователя в случае если кандидат найден и false, если не найден
     */
    public function getUserByNameAndBirthDate($fullName, $birthDate) {
        $result = false;

        $users = $this->fetchAll($this->quoteInto(
            array(
                "LOWER(CONCAT(CONCAT(CONCAT(CONCAT(LastName, ' ') , FirstName), ' '), Patronymic)) = LOWER(?)",
                " AND YEAR(BirthDate) = YEAR(?)"
            ),
            array(
                $fullName,
                $birthDate
            )
        ));
        if(count($users)){
            $user = $users->current();
        }
        return $user;
    }

	public function getRole($permission)
	{
		switch($permission) {
			case 0.5:
				return HM_Role_Abstract_RoleModel::ROLE_USER;
				break;
			case 0.75:
				return HM_Role_Abstract_RoleModel::ROLE_SUPERVISOR;
				break;
			case 0.8:
				return HM_Role_Abstract_RoleModel::ROLE_PARTICIPANT;
				break;
			case 1:
				return HM_Role_Abstract_RoleModel::ROLE_STUDENT;
				break;
			case 2:
				return HM_Role_Abstract_RoleModel::ROLE_TEACHER;
				break;
			case 2.5:
				return HM_Role_Abstract_RoleModel::ROLE_LABOR_SAFETY_LOCAL;
				break;
			case 3:
				return HM_Role_Abstract_RoleModel::ROLE_DEAN;
				break;
			case 3.15:
				return HM_Role_Abstract_RoleModel::ROLE_CURATOR;
				break;
			case 3.2:
				return HM_Role_Abstract_RoleModel::ROLE_LABOR_SAFETY;
				break;
			case 3.3:
				return HM_Role_Abstract_RoleModel::ROLE_DEVELOPER;
				break;
			case 3.6:
				return HM_Role_Abstract_RoleModel::ROLE_MANAGER;
				break;
			case 4:
				return HM_Role_Abstract_RoleModel::ROLE_ADMIN;
				break;
			case 4.1:
				return HM_Role_Abstract_RoleModel::ROLE_SIMPLE_ADMIN;
				break;
		}

		return HM_Role_Abstract_RoleModel::ROLE_GUEST;
	}

	public function getPermission($role)
	{
		switch($role) {
			case HM_Role_Abstract_RoleModel::ROLE_USER:
				return 0.5;
				break;
            case HM_Role_Abstract_RoleModel::ROLE_SUPERVISOR:
				return 0.75;
				break;
			case HM_Role_Abstract_RoleModel::ROLE_PARTICIPANT:
				return 0.8;
				break;
			case HM_Role_Abstract_RoleModel::ROLE_STUDENT:
				return 1;
				break;
			case HM_Role_Abstract_RoleModel::ROLE_TEACHER:
				return 2;
				break;
//            case HM_Role_Abstract_RoleModel::ROLE_MODERATOR:
//                return 2.5;
//                break;
			case HM_Role_Abstract_RoleModel::ROLE_DEAN_LOCAL:
				return 2.9;
				break;
			case HM_Role_Abstract_RoleModel::ROLE_DEAN:
				return 3;
				break;
			case HM_Role_Abstract_RoleModel::ROLE_CURATOR:
				return 3.15;
				break;
			case HM_Role_Abstract_RoleModel::ROLE_LABOR_SAFETY:
				return 3.2;
				break;
			case HM_Role_Abstract_RoleModel::ROLE_LABOR_SAFETY_LOCAL:
				return 2.5;
				break;
			case HM_Role_Abstract_RoleModel::ROLE_DEVELOPER:
				return 3.3;
				break;
			case HM_Role_Abstract_RoleModel::ROLE_MANAGER:
				return 3.4;
				break;
			case HM_Role_Abstract_RoleModel::ROLE_HR_LOCAL:
				return 3.5;
				break;
			case HM_Role_Abstract_RoleModel::ROLE_ATMANAGER_LOCAL:
				return 3.6;
				break;
			case HM_Role_Abstract_RoleModel::ROLE_HR:
				return 3.7;
				break;
			case HM_Role_Abstract_RoleModel::ROLE_ATMANAGER:
				return 3.8;
				break;
			case HM_Role_Abstract_RoleModel::ROLE_ADMIN:
				return 4;
				break;
			case HM_Role_Abstract_RoleModel::ROLE_SIMPLE_ADMIN:
				return 4.1;
				break;
		}

		if (false !== strstr($role, HM_Role_Custom_CustomModel::PREFIX)) {
			pr(
				$this->getService('RoleCustom')->getBasicRole(
					str_replace(HM_Role_Custom_CustomModel::PREFIX, '', $role)
				)
			);
			return $this->getPermission($this->getService('RoleCustom')->getBasicRole(
				str_replace(HM_Role_Custom_CustomModel::PREFIX, '', $role)
			));
		}

		return 0;
	}

	public function searchContacts($subjectId = null, HM_DataType_Contacts_SearchParams $searchParams = null)
    {
        $select = $this->getService('SubjectUser')
            ->getSelect()
            ->distinct()
            ->from(
                ['su' => 'subjects_users'],
                [
                    'p.MID',
                    'p.Phone',
                    'p.EMail',
                    'name' => new Zend_Db_Expr("CONCAT(CONCAT(CONCAT(CONCAT(p.LastName, ' ') , p.FirstName), ' '), p.Patronymic)"),
                    'org_position' => 'so.name',
                    'stop' => new Zend_Db_Expr('MAX(s.stop)'),
                    'su.status',
                    'order' => new Zend_Db_Expr(
                        'CASE WHEN su.status='.HM_Subject_User_UserModel::SUBJECT_USER_TEACHER.' THEN 0'.
                        ' WHEN su.status='.HM_Subject_User_UserModel::SUBJECT_USER_STUDENT.' THEN 1 '.
                        ' WHEN su.status='.HM_Subject_User_UserModel::SUBJECT_USER_GRADUATED.' THEN 2 END'
                    ),
                ]
            )
            ->joinInner(['p' => 'People'], 'p.MID=su.user_id', [])
            ->joinLeft(['so' => 'structure_of_organ'], 'p.MID=so.mid', [])
            ->joinLeft(['s' => 'sessions'], 'p.MID=s.mid', [])
            ->where('su.status in (?)', [
                [
                    HM_Subject_User_UserModel::SUBJECT_USER_STUDENT,
                    HM_Subject_User_UserModel::SUBJECT_USER_GRADUATED,
                    HM_Subject_User_UserModel::SUBJECT_USER_TEACHER,
                ]
            ])
            ->order(array('order', 'name'))
            ->group([
                'p.MID',
                'p.Phone',
                'p.EMail',
                'p.FirstName',
                'p.LastName',
                'p.Patronymic',
                'so.name',
                'su.status',
            ]);

        if($subjectId) {
            $select->where('su.subject_id = ?', $subjectId);
        }

        $query = $searchParams->query;

        if(!empty($query)) {
            $select->where($this->quoteInto(
                [
                    'p.FirstName like ?',
                    'or p.LastName like ?',
                    'or p.Patronymic like ?',
                    'or so.name like ?',
                ],
                [
                    "%{$query}%",
                    "%{$query}%",
                    "%{$query}%",
                    "%{$query}%",
                ]
            ));
        }

        $this->checkResponsibility($select);

        $usersFetchData = $select->query()->fetchAll();
        $userModels = new HM_Collection($usersFetchData, 'HM_User_UserModel');
        $result = [];

        foreach ($userModels as $userModel) {
            $result[] = [
                'id' => $userModel->MID,
                'photo' => $userModel->getPhoto(),
                'name' => $userModel->name,
                'phone' => $userModel->Phone,
                'email' => $userModel->EMail,
                'org_position' => $userModel->org_position,
                'online' => !is_null($userModel->stop) and (mktime() - (new HM_Date($userModel->stop))->getTimestamp() < 600),
                'status' => $userModel->status,
            ];
        }

        return $result;
    }

    public function formatContacts($contacts)
    {
        $result = [
            'teacher' => [],
            'student' => [],
            'graduated' => [],
        ];

        foreach ($contacts as $contact) {
            switch ((int) $contact['status']) {
                case HM_Subject_User_UserModel::SUBJECT_USER_STUDENT:
                    $result['student'][] = $contact;
                    break;
                case HM_Subject_User_UserModel::SUBJECT_USER_GRADUATED:
                    $result['graduated'][] = $contact;
                    break;
                case HM_Subject_User_UserModel::SUBJECT_USER_TEACHER:
                    $result['teacher'][] = $contact;
                    break;
            }
        }

        return $result;
    }

    public function getOnlineMates($page = 1, $itemsPerPage = 10) : Zend_Paginator
    {
        $config = Zend_Registry::get('config');
        $seconds = ini_get('session.gc_maxlifetime');
        $lifetime = $seconds ?: (int) $config->user->onlinetimeout;

        $currentUserId = $this->getCurrentUserId();
        $currentUserSubjects = $this->getSubjects($currentUserId);
        $currentUserSubjectsIds = count($currentUserSubjects) ? $currentUserSubjects->getList('subid') : [0];
        $currentUserSubjectsNames = $currentUserSubjects->getList('subid', 'name');

        $usersSelect = $this->getSelect()
            ->distinct()
            ->from(['p' => 'People'], [
                'p.MID',
                'p.LastName',
                'p.FirstName',
                'p.Patronymic',
                'isAdmin' => new Zend_Db_Expr("CASE WHEN rs.role='admin' THEN 1 ELSE 0 END"),
                'isDean' => new Zend_Db_Expr("CASE WHEN rs.role='dean' THEN 1 ELSE 0 END"),
                'hasSameSubjects' => new Zend_Db_Expr('CASE WHEN st.MID IS NOT NULL THEN 1 ELSE 0 END'),
                'subjectIds' => new Zend_Db_Expr('GROUP_CONCAT(sb.subid)')
            ])
            ->joinInner(['s' => 'sessions'], 's.mid=p.MID', [])
            ->joinLeft(['rs' => 'roles_source'], 'rs.user_id=p.MID', [])
            ->joinLeft(
                ['st' => 'Students'],
                $this->quoteInto('st.MID=p.MID and st.CID in (?)', $currentUserSubjectsIds),
                []
            )
            ->joinLeft(['sb' => 'subjects'], 'sb.subid=st.CID', [])
            ->group(['p.MID', 'rs.role', 'st.MID', 'p.LastName', 'p.FirstName', 'p.Patronymic',])
            ->order([
                new Zend_Db_Expr("isAdmin DESC"),
                new Zend_Db_Expr("isDean DESC"),
                new Zend_Db_Expr("hasSameSubjects DESC"),
                'p.FirstName',
                'p.LastName',
                'p.Patronymic',
                'p.MID'
            ])
            ->where('p.MID <> ?', $currentUserId)
            ->where('s.stop >= ?', date('Y-m-d H:i:s', time() - $lifetime));

        $userRows = $usersSelect->query()->fetchAll();
        $existingIds = [];

        foreach ($userRows as $rowKey => $currentItem) {
            if (in_array($currentItem['MID'], $existingIds)) unset($userRows[$rowKey]);
            $existingIds[] = $currentItem['MID'];
        }

        $paginator = Zend_Paginator::factory($userRows);
        $paginator->setCurrentPageNumber((int) $page);
        $paginator->setItemCountPerPage($page === 'all' ? $paginator->getTotalItemCount() : $itemsPerPage);
        $currentItemsArr = iterator_to_array($paginator->getCurrentItems());
        $usersIds = array_filter(array_column($currentItemsArr, 'MID')) + [0];
        $usersModels = $this->getService('User')->fetchAll(['MID in (?)' =>  $usersIds]);

        foreach ($paginator->getCurrentItems() as &$currentItem) {
            $subjectIds = array_filter(explode(',', $currentItem['subjectIds']));
            $currentItem['sameSubjects'] = [];

            foreach ($subjectIds as $subjectId) {
                $currentItem['sameSubjects'][$subjectId] = $currentUserSubjectsNames[$subjectId];
            }

            $currentItem['roleName'] = _('Пользователь');
            $roleNames = HM_Role_Abstract_RoleModel::getBasicRoles();
            if($currentItem['isAdmin']) {
                $currentItem['roleName'] = $roleNames[HM_Role_Abstract_RoleModel::ROLE_ADMIN];
            } elseif($currentItem['isDean']) {
                $currentItem['roleName'] = $roleNames[HM_Role_Abstract_RoleModel::ROLE_DEAN];
            }

            $userModel = $usersModels->exists('MID', $currentItem['MID']);
            $currentItem['user'] = $userModel->getData();
            $currentItem['avatar'] = $userModel->getPhoto();
            $currentItem['viewUrl'] = Zend_Registry::get('view')->url([
                'module' => 'user',
                'controller' => 'list',
                'action' => 'view',
                'user_id' => $currentItem['MID'],
            ]);
        }

        return $paginator;
    }

    public function die_error($error, $error_message=false, $error_code=400)
    {
trace_log(array('die_error', $error, $error_message, $error_code), 'total');
        http_response_code($error_code);
        $result = array('error_id'=>$error);
        if($error_message)
            $result['error_message'] = $error_message;
        die(json_encode($result));
    }

    public function sendPushMessage($userId, $message, $addInfo=false, $subject=false, $buttons=false)
    {
//firebase 
// server key AAAA8JS2ztk:APA91bGEe5g4KRSaxf0vSvBljuZOrTbxee5CR_8Xq2nDrFFiX3GRJQ03KBadCIyiAUudBnK7pLEpax9C3wd4BzWK0TqFDwSP2PzLgJUVmvVTmyWlOrJnpwMJsgibTV1A-FxjttnrfRh2Mj2tWj9ccbCRSq7wt0MG0g
// server key AIzaSyALcSebIqBUuIunc0_M6Sg--zv59SHID5g
// sender id 1033287159513

/*
    define('PUSH_HOST', 'https://onesignal.com/api/v1/notifications');
    define('PUSH_PROFILE_NAME', 'fc932ddd-4697-4c7a-8b09-ebc69383c83e');
    define('PUSH_BEARER', 'OWU2NjM5MTktMjdmNy00MjE1LTk3N2EtNjAxNDkzMjAyYWIz');
*/
        $push = Zend_Registry::get('config')->push->toArray();
        if(!$push['on'] || !$push['host'] || !$push['host'] ) return;

        $user = $this->getService('User')->find($userId)->current();
        if(!$user || !$user->push_token) return false;

    	$sendData = array(
        	'include_player_ids'=>  array($user->push_token),
	        'app_id'       =>  $push['profile_name'],
    	    'headings'  => array('en' => $subject ? strip_tags($subject) : 'eLearning 4G'),
    	    'contents'  => array('en' => strip_tags($message)),
	    );

        if($addInfo) {
           $sendData['data'] = $addInfo;
        }
        if($buttons) {
           $sendData['buttons'] = $buttons;
        }

    	$json = json_encode((object)$sendData);
	    $header = array("Authorization: Basic {$push['bearer']}", "Content-Type: application/json; charset=utf-8");
    	$options = array(
	        CURLOPT_POST => true,
    	    CURLOPT_URL => $push['host'],
        	CURLOPT_RETURNTRANSFER => true,
	        CURLOPT_HEADER => false,
    	    CURLOPT_SSL_VERIFYPEER => false,
        	CURLOPT_SSL_VERIFYHOST => false,
	        CURLOPT_HTTPHEADER => $header,
        	CURLOPT_POSTFIELDS => $json
    	);

        $MAX_TRY = 20;
        $i=0;

        do {
        	$ch = curl_init();
	        curl_setopt_array($ch, $options);
    	    $response = curl_exec($ch);
    	    curl_close($ch);
            $i++;
        } while(!($response || $i>$MAX_TRY));

		return strpos($response, 'CREATED')!==false;
    }

    public function getPhotoPath($MID)
    {
        $config = Zend_Registry::get('config');
        $filePath = $config->path->upload->photo;
        $filePath = realpath($filePath);

        if(!is_dir($filePath)){
            return false;
        }
        $maxFilesCount = (int) $config->path->upload->maxfilescount;
        $path = floor($MID / $maxFilesCount);
        if(!is_dir($filePath . DIRECTORY_SEPARATOR . $path)){
            $old_umask = umask(0);
            mkdir($filePath . DIRECTORY_SEPARATOR . $path, 0777);
            chmod($filePath . DIRECTORY_SEPARATOR . $path, 0777);
            umask($old_umask);
        }
        return  $filePath . DIRECTORY_SEPARATOR . $path . DIRECTORY_SEPARATOR;
    }

    public function getPhoto($MID)
    {
        $config = Zend_Registry::get('config');
        $path = $this->getPhotoPath($MID);
        $maxFilesCount = (int) $config->path->upload->maxfilescount;
        $glob = glob($path . $MID.'.*');
        foreach ($glob as $value) {
            $fn = '/' . $config->src->upload->photo . floor($MID / $maxFilesCount) . '/' . basename($value);
            return $fn . '?_=' . @filemtime(PUBLIC_PATH . $fn);
        }
        return (substr($config->src->default->photo, 1)!='/' ? '/': '') .$config->src->default->photo;
    }

    /**
     * @method Сохранение/обновление юзера в сессии
     * @param HM_User_UserModel $user - модель пользователя
     */
    public function updateUserIdentity($user)
    {
        if ($currentUser  = $this->getCurrentUser()) {
            $user->role = $currentUser->role;
            $user->roles = $currentUser->roles;
            $this->initUserIdentity($user);
        }
    }
}


function getallheaders2()
{
    $headers = array();
    foreach ($_SERVER as $name => $value)
    {
        if (substr($name, 0, 5) == 'HTTP_') {
               $headers[str_replace(' ', '-', /*ucwords*/(strtolower(str_replace('_', ' ', substr($name, 5)))))] = $value;
        }
    }
    return $headers;
}

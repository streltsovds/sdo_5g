<?php

define('APP_VERSION', 15);

class AuthController extends HM_Controller_Action_Mobile {

    private $MOBILE_APPS = array('com.hypermethod.fcplayer' => array('version'=> APP_VERSION));

    public function canRunAction()//Если приложение не обслуживается - не запрещаем, если номер версии ниже - запрещаем
    {   $appVersion = $this->_getParam('appVersion', 0);
        $packageName = $this->_getParam('packageName', 0);
        if(intval($appVersion) < $this->MOBILE_APPS[$packageName]['version'])
            $this->die_error('old-version', 'Для дальнейшей работы с приложением Вам необходимо установить для него обновление', 403);
    }

    public function savePushTokenAction()
    {
        $token = $this->_getParam('token', 0);
        if(!$token) return;
        $userId = $this->getService('User')->getCurrentUserId();
        $user = $this->getService('User')->find($userId)->current();
        if(!$user) return;

        $data = $user->getValues();
        $data['push_token'] = $token;
        $user = $this->getService('User')->update($data);
    }

    public function loginAction()
    {
        $data = $this->getInput();
/*
        if ($user = $this->getService('User')->getCurrentUser()) {//Бывает, вдруг заработала сессия!!!
                $this->view->user = $this->_getUserData($user);
                $this->view->error = "";
//                $this->die_error('already_authorized', 'Пользователь уже авторизован');
        } 
закомментил - мешало перелогиниванию
        else */

        {
            try {
                $user = $this->getService('User')->authorize($data->login, $data->password);

                if($data->application=='OJT') {
                    if(!($this->getService('User')->haveRole($user->MID, 'supervisor')||$this->getService('User')->haveRole($user->MID, array(HM_Role_Abstract_RoleModel::ROLE_DEAN, HM_Role_Abstract_RoleModel::ROLE_CURATOR))))
                        $this->die_error('err_not_super', 'У Вашей учетной записи недостаточно полномочий', 403);
                }

                $this->view->user = $this->_getUserData($user);
                $this->view->menu = array(
                  array('id'=>'home', 'title'=>'Главная2'),
                  array('id'=>'mycourses'),
                  array('id'=>'catalog'),
                  array('id'=>'news'),
                  array('id'=>'polls'),
                  array('id'=>'messages'),
                  array('id'=>'support'),
//                  array('id'=>'idea')
                );

                $this->view->error = "";
            } catch (HM_Exception_Auth $e) {
                $this->die_error($e->getCode(), $e->getMessage(), 403);
            }
        }
    }

    public function logoutAction()
    {
        if (!$this->getService('User')->getCurrentUser()) {
                $this->die_error('not_authorized', 'Авторизация не совершена');
        } else {
            $this->getService('User')->logout();
            $this->view->error = "";
        }

    }


    public function _getUserData($user)
    {
        $userData = array(
            'security_token' => $user->MID."_".$this->getService('User')->_getTokenSignature($user->MID),
            'login' => $user->login,
            'userid' => $user->MID,
            'firstname' => $user->FirstName,
            'lastname' => $user->LastName,
            'patronymic' => $user->Patronymic,
            'email' => $user->EMail,
            'phone' => $user->Phone,
            'avatar' => $user->getPhoto(),
            'logo'=> 'images/logo.png',
            'logo_width'=> '30',
        );

        return $userData;
    }

}
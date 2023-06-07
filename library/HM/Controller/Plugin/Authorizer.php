<?php
class HM_Controller_Plugin_Authorizer extends HM_Controller_Plugin_Abstract
{
    const AUTHORIZER_COOKIE_NAME = 'hmkey';

    public function preDispatch(Zend_Controller_Request_Abstract $request)
    {
        $url = $_SERVER['REQUEST_URI'];

        if (false !== strstr($url, 'index.php')) {
            $url = $this->getView()->serverUrl('/');
        }

        /** @var HM_User_UserService $userService */
        $userService = $this->getService('User');

        if (!$userService->getCurrentUser() && isset($_COOKIE[self::AUTHORIZER_COOKIE_NAME])) {
            $key = trim($_COOKIE[self::AUTHORIZER_COOKIE_NAME]);

            if (strlen($key)) {
                if ($userService->authorizeByKey($key)) {
                    header('Location: '.$url);
                    exit();

                }                
            }            

        }
    }
}

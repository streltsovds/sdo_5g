<?php
class HM_Controller_Plugin_RoleSwitcher extends HM_Controller_Plugin_Abstract
{
    public function preDispatch(Zend_Controller_Request_Abstract $request)
    {
        /** @var HM_User_UserService $userService */
        $userService = $this->getService('User');

        $userId    = $userService->getCurrentUserId();
        $userRole  = $userService->getCurrentUserRole(true); // нужен обязательно roleUnion;
        $userRoles = $userService->getUserRoles($userId);
        
        if ( $userId && !in_array($userRole, $userRoles) ) {
            
            if ( count($userRoles) ) {
                $userService->switchRole(array_shift($userRoles));
            } else {
                $userService->logout();
            }

            header('Location: '.$this->getView()->serverUrl('/'));
            exit();
        }
    }
}

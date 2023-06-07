<?php
class HM_Controller_Plugin_Session extends HM_Controller_Plugin_Abstract
{
    public function preDispatch(Zend_Controller_Request_Abstract $request)
    {
        /** @var HM_Option_OptionService $optionService */
        $optionService = $this->getService('Option');
        /** @var HM_User_UserService $userService */
        $userService = $this->getService('User');

        $disableMultipleAuthentication = $optionService->getOption('disable_multiple_authentication');

        if ($userId = $userService->getCurrentUserId() && $disableMultipleAuthentication) {

            $s = new Zend_Session_Namespace('s');

            /** @var HM_Session_SessionService $sessionService */
            $sessionService = $this->getService('Session');

            $session = $sessionService->find($s->sessid);
            if (count($session)) {
                $session = $session->current();
                if ($session->logout) {
                    $userService->logout();
                    header('Location: '.$this->getView()->serverUrl('/'));
                    exit();
                }
            }
        }
    }

    public function postDispatch(Zend_Controller_Request_Abstract $request)
    {
        /** @var HM_User_UserService $userService */
        $userService = $this->getService('User');
        /** @var HM_Session_SessionService $sessionService */
        $sessionService = $this->getService('Session');

        $s = new Zend_Session_Namespace('s');

        if ($userId = $userService->getCurrentUserId()) {
            $sessionService->updateWhere(array(
                'stop' => date('Y-m-d H:i:s')        
            ), array(
                'mid = ?' => $userId,       
                'sessid = ?' => $s->sessid,       
            ));
        }
    }
}


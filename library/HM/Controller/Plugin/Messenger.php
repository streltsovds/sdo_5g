<?php
class HM_Controller_Plugin_Messenger extends HM_Controller_Plugin_Abstract
{
    /**
     * пока этот плагин не используется так как при редиректе (gotoUrl gotoSimple) postDispatch не вызывается
     * @todo переделать на Zend_Queue.
     * @param Zend_Controller_Request_Abstract $request
     */
    public function postDispatch(Zend_Controller_Request_Abstract $request)
    {
        /** @var HM_Messenger $messenger */
        $messenger = $this->getService('Messenger');
        $messenger->sendAllFromChannels();
    }
}

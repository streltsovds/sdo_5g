<?php
/**
 * Created by PhpStorm.
 * User: k1p
 * Date: 12/3/18
 * Time: 3:54 PM
 */

class HM_Crontask_Task_SendHeldEmails extends HM_Crontask_Task_TaskModel  implements HM_Crontask_Task_Interface
{
    public function getTaskId() {
        return 'sendHeldEmails';
    }

    public function run()
    {
        $holdMailService = $this->getServiceLayer('HoldMail');
        $sendList = $holdMailService->getSendList();

        foreach ($sendList as $sendItem)
        {
            $messageData = json_decode($sendItem['serialized_message'], true);
            $message = new HM_Messenger();
            $message->__fromArray($messageData);

            $this->getServiceLayer('MessengerMail')->update($message);

            $holdMailService->delete($sendItem['hold_mail_id']);
        }
    }

    /*
    *
    * @return HM_Service_Abstract
    */
    protected function  getServiceLayer($name)
    {
        return Zend_Registry::get('serviceContainer')->getService($name);
    }
}
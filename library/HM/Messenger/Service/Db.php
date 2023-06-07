<?php

class HM_Messenger_Service_Db extends HM_Messenger_Service_Abstract
{
    public function update(SplSubject $message)
    {
        list($roomSubject, $roomSubjectId) = $message->getRoom();

        if (!$message->isNoPush()) {
            $this->getService('User')->sendPushMessage($message->getReceiverId(), str_ireplace(['<p>', '<br>', '<br/>'], "\n", $message->getMessage()));
        }

        return $this->getService('Message')->insert(
            [
                'subject' => (string)$roomSubject,
                'subject_id' => $roomSubjectId,
                'theme' => $message->getSubject(),
                'from' => $message->getSenderId(),
                'to' => $message->getReceiverId(),
                'message' => $message->getMessage()
            ]
        );
    }
}

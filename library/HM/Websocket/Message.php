<?php

/**
 *
 * @property HM_Websocket_Library_Connection[] $_clients
 */


class HM_Websocket_Message extends HM_Websocket_Library_ApplicationController
{
    protected function connectedAction($userId, $namespace)
    {

    }

    protected function disconnectedAction($userId, $namespace)
    {

    }

    public function sendAction($data, $userId, $namespace)
    {
        $result = $this->saveMessage($data, $userId, $namespace);
        $this->_sendToAll($namespace, 'sent', $result);
        return $result;
    }

    public function editAction($data, $userId, $namespace)
    {
        $result = $this->saveMessage($data, $userId, $namespace);

        $this->_sendToAll($namespace, 'edited', $result);
        return $result;
    }

    public function deleteAction($data, $userId, $namespace)
    {
        /** @var HM_ChatMessage_ChatMessageService $chatMessageService */
        $chatMessageService = Zend_Registry::get('serviceContainer')->getService('ChatMessage');
        /** @var HM_MessageItem_MessageItemService $messageItemService */
        $messageItemService = Zend_Registry::get('serviceContainer')->getService('MessageItem');

        if($data['messageId']) {
            $chatMessageService->deleteBy(array('message_id = ?' => $data['messageId']));
            $messageItemService->deleteBy(array('message_id = ?' => $data['messageId']));
        }

        $result = array('message_id' => $data['messageId']);

        $this->_sendToAll($namespace, 'deleted', $result);
        return $result;
    }


    private function saveMessage($data, $userId, $namespace)
    {
        /** @var HM_ChatMessage_ChatMessageService $chatMessageService */
        $chatMessageService = Zend_Registry::get('serviceContainer')->getService('ChatMessage');
        /** @var HM_MessageItem_MessageItemService $messageItemService */
        $messageItemService = Zend_Registry::get('serviceContainer')->getService('MessageItem');

        $insertData = array(
            'message' => $data['message'],
            'namespace' => $namespace,
            'user_id' => $userId,
            'created_at' => date('Y-m-d H:i:s'),
        );

        if($data['messageId']) {
            $insertData['message_id'] = $data['messageId'];
            $message = $chatMessageService->update($insertData);
        } else {
            $message = $chatMessageService->insert($insertData);
        }

        $messageItems = array();
        $result = false;
        $messageItemsWithTypes = array();

        if ($message) {
            $messageItemService->deleteBy(array('message_id = ?' => $message->message_id));
            $result = $message->getData();
            if (!empty($data['items']) &&
                is_array($data['items'])
            ) {
                foreach ($data['items'] as $messageItem) {
                    $messageItem = $messageItemService->insert(array(
                        'message_id' => $message->message_id,
                        'matched_text' => $messageItem['text'],
                        'type' => $messageItem['type'],
                        'item_id' => $messageItem['itemId'],
                    ));

                    $messageItemsWithTypes[$messageItem->type][] = $messageItem;
                    $messageItems[] = $messageItem->getData();
                }

                $this->processMessageItems($messageItemsWithTypes, $userId);
                $result['items'] = $messageItems;
            }

            $user = $this->getService('User')->fetchRow(array('MID = ?' => $userId));
             if($user) {
                 $result['user'] = array(
                     'id' => $user->MID,
                     'name' => $user->getName(),
                     'avatarUrl' => $user->getPhoto(),
                 );
             }
        }

        return $result;
    }

    private function processMessageItems($items, $fromUserId)
    {

    }

    /**
     * @param string $serviceName
     *
     * @return HM_Service_Abstract
     */
    protected function getService($serviceName)
    {
        return Zend_Registry::get('serviceContainer')->getService($serviceName);
    }
}

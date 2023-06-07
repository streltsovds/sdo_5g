<?php

/**
 *
 * @property HM_Websocket_Library_Connection[] $_clients
 */


abstract class HM_Websocket_Library_ApplicationController extends HM_Websocket_Library_Application
{
    private $_connectionTime = null;

    protected $_clients = array();
    protected $_namespaces = array();

    abstract protected function connectedAction($client, $namespace);
    abstract protected function disconnectedAction($client, $namespace);

    protected function init()
    {
        $this->_connectionTime = microtime(true);
    }

    private function dbReconnection()
    {
        /*\Yii::$app->db->close();
        \Yii::$app->db->open();*/
        $this->_connectionTime = microtime(true);
    }

    private function checkConnection($client)
    {
        $config = Zend_Registry::get('config');
        if (isset($config->websocket->db_reconnection_timeout)) {
            $reconnectTimeout = $config->websocket->db_reconnection_timeout;
            $now = microtime(true);
            if ($now >=  $this->_connectionTime + $reconnectTimeout || $reconnectTimeout == false ) {
                $this->dbReconnection();
                $client->log('Reconnection');
            }
        }
    }

    public function onConnect($client)
    {

    }

    public function onDisconnect($client)
    {
        /** @var HM_Websocket_Library_Connection $client */
        $id = $client->getClientId();
        $userId = $client->getUserId();
        if ($userId) {
            $namespace = $client->getNamespace();

            //$user = $webinarState->getUser($userId);
            unset($this->_clients[$namespace][$id]);

            // Посылаем уведомление
            $moreConnects = false;
            if (isset($this->_clients[$namespace]) && is_array($this->_clients[$namespace])) {
                /** @var HM_Websocket_Library_Connection $curClient */
                foreach($this->_clients[$namespace] as $curClient) {
                    if ($curClient->getUserId() == $userId) {
                        $moreConnects = true;
                        break;
                    }
                }
            }
            if (!$moreConnects) {
                $this->checkConnection($client);
                $data = $this->disconnectedAction($userId, $namespace);
                $this->_sendToAll($namespace, 'disconnected', $data, $userId);
            }
        }
    }


    public function onData($data, $client)
    {
        /** @var HM_Websocket_Library_Connection $client */
        $decodedData = $this->_decodeData($data);
        $userId = $client->getUserId();

        if($decodedData === false) {
            // @todo: invalid request trigger error...
        }

        if (isset($decodedData['action'])) {
            $action = $decodedData['action'];
            $this->checkConnection($client);
            $client->log('Action: '.$action);
            switch ($action) {
                case 'connected':
                    if (isset($decodedData['data']['namespace']) && isset($decodedData['data']['sessionId'])) {
                        $namespace = $decodedData['data']['namespace'];
                        $sessionId = $decodedData['data']['sessionId'];

                        /** @var HM_Session_SessionService $sessionService */
                        $sessionService = Zend_Registry::get('serviceContainer')->getService('Session');
                        $session = $sessionService->fetchAll(array('sesskey = ?' => $sessionId), 'sessid desc')->current();
                        if($session) {
                            $userId = $session->mid;
                        } else {
                            $userId = 0;
                            $client->log("Can't find user session with key: ".$sessionId);
                        }

                        $client->setUserId($userId);

                        $id = $client->getClientId();
                        $client->setNamespace($namespace);
                        if ($namespace && $userId) {
                            // Посылаем уведомление
                            $moreConnects = false;
                            if (isset($this->_clients[$namespace]) && is_array($this->_clients[$namespace])) {

                                foreach($this->_clients[$namespace] as $curClient) {
                                    if ($curClient->getUserId() == $userId) {
                                        $moreConnects = true;
                                        break;
                                    }
                                }
                            }

                            $this->_clients[$namespace][$id] = $client;

                            if (!$moreConnects) {
                                $data = $this->connectedAction($userId, $namespace);
                                $this->_sendToAll($namespace, 'connected', $data, $userId);
                            }
                        }
                    }
                    break;
                default:
                    $actionFunction = $action.'Action';
                    $namespace = $client->getNamespace();
                    if (method_exists($this, $actionFunction)) {
                        $resultData = $this->{$actionFunction}($decodedData['data'], $userId, $namespace);

                        if ($resultData) {
                            $this->_sendToAll($namespace, $action, $resultData, $userId);
                        }
                    }

            }
        } else {
            // @todo: invalid request
        }
    }

    public function onBinaryData($data, $client)
    {
        // @todo: chat not allow binary data...
    }


    protected function _sendToUser($namespace, $user_id, $action, $data = array(), $senderId = null)
    {
        $encodedData = $this->_encodeData($action, $data, $senderId);
        if (isset($this->_clients[$namespace]) && is_array($this->_clients[$namespace])) {
            /** @var HM_Websocket_Library_Connection $sendto */
            foreach($this->_clients[$namespace] as $sendto) {
                if ($sendto->getUserId() == $user_id) {
                    $sendto->send($encodedData);
                }
            }
        }
    }

    protected function _sendToAll($namespace, $action, $data = array(), $senderId = null)
    {
        $encodedData = $this->_encodeData($action, $data, $senderId);
        if (isset($this->_clients[$namespace]) && is_array($this->_clients[$namespace])) {
            /** @var HM_Websocket_Library_Connection $sendto */
            foreach($this->_clients[$namespace] as $sendto) {
                if ($sendto->getUserId()) {
                    $sendto->send($encodedData);
                }
            }
        }
    }
}
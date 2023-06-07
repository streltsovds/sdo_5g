<?php

/**
 * WebSocket Server Application
 * 
 * @author Nico Kaiser <nico@kaiser.me>
 */
abstract class HM_Websocket_Library_Application
{
    protected static $instances = array();
    
    /**
     * Singleton 
     */
    protected function __construct() {
        static::init();
    }

    final private function __clone() { }
    
    final public static function getInstance()
    {
        $calledClassName = get_called_class();
        if (!isset(self::$instances[$calledClassName])) {
            self::$instances[$calledClassName] = new $calledClassName();
        }

        return self::$instances[$calledClassName];
    }

    abstract public function onConnect($connection);

	abstract public function onDisconnect($connection);

	abstract public function onData($data, $client);

    protected function init() {}
	// Common methods:
	
	protected function _decodeData($data)
	{
		$decodedData = json_decode($data, true);
		if($decodedData === null) {
			return false;
		}
		
		if(isset($decodedData['action'], $decodedData['data']) === false) {
			return false;
		}
		
		return $decodedData;
	}
	
	protected function _encodeData($action, $data, $userId)
	{
		if(empty($action)) {
			return false;
		}
		
		$payload = array(
			'action' => $action,
			'data' => $data,
            'userId' => $userId,
			'server' => 'true',
		);
		
		return json_encode($payload);
	}
}
<?php
abstract class HM_Controller_Action_Mobile extends Zend_Controller_Action
{
    protected $_inputData = array();
    protected function getInput()
    {
        if(!$this->_inputData) {
            $input = file_get_contents('php://input');
            $this->_inputData= json_decode($input);//, true);
        }

        return $this->_inputData;
    }

    protected function _getParam($key, $default = false)
    {
        $input = (array) $this->getInput();
        return isset($input[$key]) ? $input[$key] : parent::_getParam($key, $default);
    }

    protected  function die_error($error, $error_message=false, $error_code=400)
    {
trace_log(array('die_error', $error, $error_message, $error_code), 'total');
        $this->getService('User')->die_error($error, $error_message, $error_code);
    }

    public function init() {
        $this
            ->_helper
            ->ContextSwitch()
            ->setAutoJsonSerialization(true)
            ->addActionContext($this->getRequest()->getActionName(), 'json')
            ->initContext('json');

        $locale = Zend_Registry::get('config')->resources->locale->default;
/*
        //Получаем и запоминаем режим без меню
        $default = new Zend_Session_Namespace('default');
        $default->isMobile = 1;
        
        Это тут не надо, тут и так никаких меню нет!!! Тут апи!
        Флаг Mobile только для обозначения, что интерфейс генерим для мобильного
*/
        $translate = new Zend_Translate(
            array(
                'adapter' => 'HM_Translate_Adapter_Gettext',
                'content' => APPLICATION_PATH . '/../data/locales/' . $locale . '/LC_MESSAGES/',
                'locale'  => $locale . '_unmanaged'
            )
        );

        $translate->addTranslation(
            array(
                'adapter' => 'HM_Translate_Adapter_Gettext',
                'content' => APPLICATION_PATH . '/../data/locales/' . $locale . '/LC_MESSAGES/',
                'locale'  => $locale,
            )
        );

        $translate->getAdapter()->generateJsTranslate($locale);
        Zend_Registry::set('translate', $translate);
        Zend_Registry::set('Zend_Translate', $translate);

        if (!function_exists('_')){
            function _($str){
                return Zend_Registry::get('translate')->_($str);
            }
        }
        if (!function_exists('_n')){
            function _n($msgid, $str, $num){
                return Zend_Registry::get('translate')->plural($msgid, $str, $num);
            }
        }

trace_log($this->getInput(), 'total');

        $this->getService('User')->authorizeByToken();

        parent::init();
    }

    public function getService($name)
    {
        return Zend_Registry::get('serviceContainer')->getService($name);
    }

}

function trace_log($message, $fname0="common")
{
    if($_SERVER['REQUEST_METHOD']=='OPTIONS') return;

    $H = getallheaders2();
	$client_security_token = isset($H[HM_User_UserService::SECUTITY_TOKEN_NAME]) ? $H[HM_User_UserService::SECUTITY_TOKEN_NAME] : 
        (isset($_GET[HM_User_UserService::SECUTITY_TOKEN_NAME]) ? $_GET[HM_User_UserService::SECUTITY_TOKEN_NAME] : 'unknown');
    $client_security_token = explode('_', $client_security_token);
	$_fname = $fname;

    if(is_object($message)||is_array($message))
        $message_txt = print_r($message, true);

	$curLog = "{$fname0}.log";
    $fname = "../data/log/{$curLog}";

	$IP = isset($_SERVER["REMOTE_ADDR"])?$_SERVER["REMOTE_ADDR"]:'?';

	if(isset($_SERVER["HTTP_X_REAL_IP"]))//for nginx forwarding
		$IP = $_SERVER["HTTP_X_REAL_IP"];
	else
	if(isset($_SERVER["HTTP_X_FORWARDED_FOR"]))
		$IP = $_SERVER["HTTP_X_FORWARDED_FOR"];

	$fd = fopen($fname, "a+");
	fwrite($fd, @date("Y-m-d H:i:s ")." : {$IP} : [".$client_security_token[0]."]\t".(($_SERVER['REQUEST_URI'])).(strlen($message_txt)>100?"\r\n":"\t").$message_txt."\r\n");
	fclose($fd);

	$curLog = "{$fname0}.bin";
    $fname = "../data/log/{$curLog}";
	$fd = fopen($fname, "a+");
	fwrite($fd, @date("Y-m-d").";".@date('H:i:s').";".$client_security_token[0].";{$IP};".($_SERVER['REQUEST_URI']).";".serialize($message)."\r\n");
	fclose($fd);
}


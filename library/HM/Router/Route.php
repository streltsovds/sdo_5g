<?php
/**
 * Created by PhpStorm.
 * User: k1p
 * Date: 4/11/19
 * Time: 10:49 AM
 */

/**
 * Class HM_Router_Route
 * Support assemble with module, controller, action params, if have one of them in params.
 * Always reset result url in this case.
 */
class HM_Router_Route extends Zend_Controller_Router_Route
{
    /**#@+
     * Array keys to use for module, controller, and action. Should be taken out of request.
     * @var string
     */
    protected $_moduleKey     = 'module';
    protected $_controllerKey = 'controller';
    protected $_actionKey     = 'action';

        /**
     * Instantiates route based on passed Zend_Config structure
     *
     * @param Zend_Config $config Configuration object
     */
    public static function getInstance(Zend_Config $config)
    {
        $reqs = ($config->reqs instanceof Zend_Config) ? $config->reqs->toArray() : array();
        $defs = ($config->defaults instanceof Zend_Config) ? $config->defaults->toArray() : array();
        return new self($config->route, $defs, $reqs);
    }

    public function assemble($data = array(), $reset = false, $encode = false, $partial = false)
    {
        if (!empty($data[$this->_moduleKey]) or
            !empty($data[$this->_controllerKey]) or
            !empty($data[$this->_actionKey])
        ) {
            $result = $this->assembleByModule($data, $reset, $encode, $partial);
        } else {
            $result = parent::assemble($data, $reset, $encode, $partial);
        }

        return $result;
    }

    public function assembleByModule($data = array(), $encode = false)
    {
        $params = array();

        foreach ($data as $key => $value) {
            if ($value !== null) {
                $params[$key] = $value;
            } elseif (isset($params[$key])) {
                unset($params[$key]);
            }
        }

        $params += $this->_defaults;
        $url = '';

        if (array_key_exists($this->_moduleKey, $data)) {
            $module = $params[$this->_moduleKey];
        } elseif(isset($this->_defaults[$this->_moduleKey])) {
            $module = $this->_defaults[$this->_moduleKey];
        }
        unset($params[$this->_moduleKey]);

        $controller = $params[$this->_controllerKey];
        unset($params[$this->_controllerKey]);

        $action = $params[$this->_actionKey];
        unset($params[$this->_actionKey]);

        foreach ($params as $key => $value) {
            $key = ($encode) ? urlencode($key) : $key;
            if (is_array($value)) {
                foreach ($value as $arrayValue) {
                    $arrayValue = ($encode) ? urlencode($arrayValue) : $arrayValue;
                    $url .= '/' . $key;
                    $url .= '/' . $arrayValue;
                }
            } else {
                if ($encode) $value = urlencode($value);
                $url .= '/' . $key;
                $url .= '/' . $value;
            }
        }

        if (!empty($url) || $action !== $this->_defaults[$this->_actionKey]) {
            if ($encode) $action = urlencode($action);
            $url = '/' . $action . $url;
        }

        if (!empty($url) || $controller !== $this->_defaults[$this->_controllerKey]) {
            if ($encode) $controller = urlencode($controller);
            $url = '/' . $controller . $url;
        }

        if (isset($module)) {
            if ($encode) $module = urlencode($module);
            $url = '/' . $module . $url;
        }

        return ltrim($url, $this->_urlDelimiter);
    }
}
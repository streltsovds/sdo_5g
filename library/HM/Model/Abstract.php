<?php

abstract class HM_Model_Abstract implements JsonSerializable
{
    protected $_data = array();

    protected $_process = null;

    /** @var HM_Collection_Abstract $_collection */
    protected $_collection = null;

    protected $_primaryName = '';

    public function __construct($data)
    {
        $this->_data = $data;
    }

    public function __set($key, $value)
    {
        $this->_data[$key] = $value;
    }

    public function __get($key)
    {
        if (isset($this->_data[$key])) {
            return $this->_data[$key];
        }

        return null;
    }

    public function __isset($key)
    {
        return isset($this->_data[$key]);
    }

    public function __unset($key)
    {
        unset($this->_data[$key]);
    }

    public function setData($data)
    {
        if (is_array($data)) {
            $this->_data = $data;
        }
    }

    public function getData()
    {
        return $this->_data;
    }

    /*
     * Стоит использовать этот метод для передачи данных в Vue
     * Чтобы не затесались коллекции зависимостей
     */
    public function getPlainData()
    {
        $data = [];

        foreach ($this->getData() as $key => $value) {

            if (is_a($value, 'HM_Collection')) continue;

            if (is_string($value)) {
                $value = htmlspecialchars($value, ENT_QUOTES|ENT_HTML401);
            }
            $data[$key] = $value;
        }

        return $data;
    }

    public function getValues($keys = null, $excludes = null)
    {
        $values = array();
        if (is_array($this->_data) && count($this->_data)) {
            foreach($this->_data as $key => $value) {
                if ((!is_object($value) && !is_array($value)) || $value instanceof Zend_Db_Expr) {
                    if (is_array($keys) && !in_array($key, $keys)) continue;
                    if (is_array($excludes) && in_array($key, $excludes)) continue;
                    $values[$key] = $value;
                }
            }
        }
        return $values;
    }

    public function getValue($key)
    {
        if (isset($this->_data[$key])) {
            return $this->_data[$key];
        }
        return null;
    }

    public function setValue($key, $value)
    {
        $this->_data[$key] = $value;
    }

    public function add($key, $value)
    {
        $this->_data[$key][] = $value;
    }

    public function remove($key, $value)
    {
        if (is_array($this->_data[$key]) && count($this->_data[$key])) {
            $data = array();
            foreach($this->_data[$key] as $v) {
                if ($v === $value) continue;
                $data[] = $v;
            }
            $this->_data[$key] = $data;
        }
    }

    static public function factory($data, $default = 'HM_Model_Abstract')
    {
        return new $default($data);
    }

    static public function date($datetime)
    {
    	if (!strlen($datetime)) return '';
        return date('d.m.Y', strtotime($datetime));
    }

    static public function dateTime($datetime)
    {
        if (!strlen($datetime)) return '';
    	return '<span class="nowrap">' . date('d.m.Y H:i:s', strtotime($datetime)) . '</span>';
    }

    static public function dateTimeWithoutSeconds($datetime)
    {
        if (!strlen($datetime)) return '';
    	return '<span class="nowrap">' . date('d.m.Y H:i', strtotime($datetime)) . '</span>';
    }

    static public function time($datetime)
    {
        if (!strlen($datetime)) return '';
        return date('H:i:s', strtotime($datetime));
    }

    static public function timeWithoutSeconds($datetime)
    {
        if (!strlen($datetime)) return '';
        return date('H:i', strtotime($datetime));
    }

    public function checkInterval($value, $interval)
    {
        if (!empty($interval)) {
            if ($interval[strlen($interval)-1] == '-') $interval = substr($interval,0,-1);
            if ($interval[0] == '-') $interval = '0'.$interval;

            if (strstr($interval,'-') !== false) {
                $interval = explode('-',$interval);
                if (count($interval) == 2) {
                    return (($value >= $interval[0]) && ($value <= $interval[1]));
                }
            } else {
                return ($value >= $interval);
            }

        }
        return false;
    }
    public function getPrimaryName()
    {
        return $this->_primaryName;
    }

    public function getServiceName()
    {
        return false;
    }

    /**
     * @return mixed
     * @throws Zend_Exception
     */
    public function getService()
    {
        $name = $this->getServiceName();
        if ($name) {
            return Zend_Registry::get('serviceContainer')->getService($name);
        }
        return false;
    }


     public function getProcess()
     {
         if($this->_process == null){
            $name = substr(get_class($this), 0, -5) . 'Process';
             $this->_process = new $name($this);
         }

        return $this->_process;
     }

    /**
     * @return bool|mixed|string|null
     * @throws Zend_Exception
     */
    public function getPrimaryKey()
    {
        if($this->_primaryName != ''){
            return $this->{$this->_primaryName};
        }
        if ($service = $this->getService()) {
            if ($primaryKey =  $service->getMapper()->getAdapter()->getPrimaryKey()) {
                if (count($primaryKey) == 1) {
                    $this->_primaryName = $primaryKey[1];
                    return $this->_primaryName;
                }
                //@todo а если ключег составной?
            }
        }

        return false;
    }

    /**
     * @param $state
     * @return string
     */
    public function getProcessStateClass($state)
    {
        return '';
    }

    /**
     * @return HM_Collection_Abstract
     */
    public function getCollection()
    {
        return $this->_collection;
    }

    /**
     * @param HM_Collection_Abstract $collection
     * @return HM_Model_Abstract
     */
    public function setCollection(HM_Collection_Abstract $collection)
    {
        $this->_collection = $collection;
        return $this;
    }

    public function getCachedValue($cacheType, $key)
    {
        if ($collection = $this->getCollection()) {
            return $collection->getCachedValue($cacheType, $key);
        }
        return false;
    }

    public function jsonSerialize() {
        return $this->_data;
    }

    public function __toArray()
    {
        return $this->_data;
    }

    /**
     * @param $method
     * @param $arguments
     * @return mixed
     * @throws Exception
     */
    public function __call($method, $arguments)
    {
        $service = $this->getService();
        if (method_exists($service, $method)) {
            return call_user_func_array( array($service, $method), $arguments);
        }
        throw new Exception('Wrong method call ' . $method);
    }

    public function getUnifiedData()
    {
        return $this->_data;
    }
}

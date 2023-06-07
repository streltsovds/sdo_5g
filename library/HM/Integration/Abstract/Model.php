<?php

class HM_Integration_Abstract_Model
{
    protected $_id;
    protected $_externalId;

    protected $_attributes = array();
    protected $_source = array(); // массив параметров до конвертации
    protected $_externals = array();

    /**
     * @return mixed
     */
    public function getId()
    {
        return $this->_id;
    }

    /**
     * @param mixed $id
     */
    public function setId($id)
    {
        $this->_id = $id;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getExternalId()
    {
        return $this->_externalId;
    }

    /**
     * @param mixed $externalId
     */
    public function setExternalId($externalId)
    {
        $this->_externalId = $externalId;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getAttributes()
    {
        return $this->_attributes;
    }

    /**
     * @param mixed $attributes
     */
    public function setAttributes($attributes)
    {
        $this->_attributes = $attributes;
        return $this;
    }

    public function setAttribute($key, $value)
    {
        $this->_attributes[$key] = $value;
        return $this;
    }

    public function unsetAttribute($key)
    {
        if (isset($this->_attributes[$key])) {
            unset($this->_attributes[$key]);
        }
        return $this;
    }

    /**
     * @return array
     */
    public function getSource()
    {
        return $this->_source;
    }

    /**
     * @param array $source
     */
    public function setSource($source)
    {
        $this->_source = $source;
        return $this;
    }


    public function setExternalAttribute($key, $value)
    {
        $this->_externals[$key] = $value;
        return true;
    }


    public function getExternalAttribute($key)
    {
        if (isset($this->_externals[$key])) {
            return $this->_externals[$key];
        }
        return false;
    }

    public function __get($key)
    {
        if (isset($this->_attributes[$key])) {
            return $this->_attributes[$key];
        }
        return null;
    }

    /**
     * @return array
     * @throws Exception
     */
    static public function getLdapNames()
    {
        $result = array();
        $config = Zend_Registry::get('config');

        foreach ($config as $key => $value) {
            if (is_a($value,'Zend_Config') && isset($value->integration->sources->key))
                $result[] = $value->integration->sources->key;
        }

        return $result;
    }

    static public function getLdapNamesForSelect()
    {
        return array_merge(array('-все-'), self::getLdapNames());
    }
}
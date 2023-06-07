<?php

abstract class HM_Integration_Abstract_Service extends HM_Service_Abstract
{
    protected $_source;
    protected $_client;

    static public function factory($task)
    {
        $class = sprintf('HM_Integration_Task_%s_Service', ucfirst($task));
        if (class_exists($class)) {
            return new $class;
        }
        return null;
    }

    /**
     * @return mixed
     */
    public function getSource()
    {
        return $this->_source;
    }

    /**
     * @param mixed $source
     */
    public function setSource($source)
    {
        $this->_source = $source;
        return $this;
    }

    public function initClient($transport)
    {
        $method = 'initClient' . ucfirst($transport);
        return $this->$method();
    }

    public function initClientFile()
    {
        $this->_client = new HM_Integration_File_Client($this->_source);
        return $this;
    }

    public function initClientSoap()
    {
        $this->_client = new HM_Integration_Soap_Client($this->_source, array(
            'soap_version' => SOAP_1_1,
            'encoding' => 'UTF-8',
            'login' => $this->_source['login'],
            'password' => $this->_source['password'],
        ));
        return $this;
    }

    /**
     * @return mixed
     */
    public function getClient()
    {
        return $this->_client;
    }

    public function getPlanOfExchange()
    {
        return $this->_source['planId'];
    }

    public function fetchAll()
    {
        $items = $this->_fetch();
        return $items;
    }

    public function fetchChanged()
    {
        try {
            $items = $this->_fetch(true);
        } catch (Exception $e) {
            throw new HM_Integration_Exception($e->getMessage());
        }
        return $items;
    }

}
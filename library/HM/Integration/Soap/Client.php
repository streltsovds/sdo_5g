<?php

class HM_Integration_Soap_Client extends Zend_Soap_Client implements HM_Integration_Interface_Client
{
    protected $_source;
    protected $_dataIds = array();
    protected $requireInputParam = false;

    public function __construct($source, $options)
    {
        $this->_source = $source;
        return parent::__construct($source['wsdl'], $options);
    }

    public function setRequireInputParam($requireInputParam)
    {
        $this->requireInputParam = $requireInputParam;
        return $this;
    }

    public function call($method, $primaryKey = 'id', $keySalt = false)
    {
        $isChangeRequest = (bool)strpos($method, 'ChangesOf');

        $request = HM_Integration_Soap_Request::get($method, $this->requireInputParam);
        $this->$method(new SoapVar($request, XSD_ANYXML));
        $response = $this->getLastResponse();

//        $response = file_get_contents(APPLICATION_PATH . '/../data/temp/soap-org.xml');

        $responseName = sprintf('%sResponse', $method);
        $tableName = sprintf('%s_table', str_replace('ChangesOf', '', $method));
        $rowName = sprintf('%s_string', str_replace('ChangesOf', '', $method));

        $items = array();
        $xml = simplexml_load_string($response);
        $container = $xml
            ->children('soap', true)->Body
            ->children('m', true)->$responseName
            ->children('m', true)->return;

        if ($isChangeRequest) {

            $this->addDataId(
                (string)$container->children('m', true)->idPlanOfExchange,
                (string)$container->children('m', true)->idData
            );
            $container = $container->children('m', true)->$tableName;
        }

        foreach ($container->children('m', true)->$rowName as $item) {
            $item = (array)$item;
            array_walk($item, array(self, '_inputFilter'), sprintf('%s-', $this->_source['key']));
            $key = $keySalt ? implode('-', array($item[$primaryKey], $item[$keySalt])) : $item[$primaryKey];
            $items[$key] = $item;
        }

        return $items;
    }

    public function callExport($item)
    {
        $message = HM_Integration_Soap_RequestExport::get($item);
        $this->Download(new SoapVar($message, XSD_ANYXML));
        $response = $this->getLastResponse();

//        $response = file_get_contents(APPLICATION_PATH . '/../data/temp/soap-download.xml');

        $xml = simplexml_load_string($response);
        $container = $xml
            ->children('soap', true)->Body
            ->children('m', true)->DownloadResponse
            ->children('m', true)->return;

        return (array)$container;
    }

    // надо обеспечить уникальность id среди всех источников
    protected function _inputFilter(&$value, $key, $prefix)
    {
        if (strlen($value)) {
            return strpos(strtolower($key), 'id') === 0 ? $value = $prefix . $value : $value;
        }
        return $value;
    }

    /**
     * @return mixed
     */
    public function getDataIds()
    {
        return $this->_dataIds;
    }

    /**
     * @param mixed $dataId
     */
    public function addDataId($planId, $dataId)
    {
        if (!isset($this->_dataIds[$planId])) {
            $this->_dataIds[$planId] = $dataId;
        }
        return $this;
    }

    public function answer($status)
    {
        foreach ($this->getDataIds() as $planId => $dataId) {
            $answer = new HM_Integration_Soap_Answer();
            $answer->idData = $dataId;
            $answer->idPlanOfExchange = $planId;
            $answer->Status = $status;

            $this->UniversalInOutPointAnswer($answer);
//        $this->_client->getLastResponse(); //?
        }
        return true;
    }



}
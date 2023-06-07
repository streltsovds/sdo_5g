<?php

// @todo-int
class HM_Integration_Soap_Answer
{
    public $idData = '';
    public $idRequestor = '';
    public $idPlanOfExchange = '';
    public $Status = 0;

    public function __construct()
    {
        $config = Zend_Registry::get('config');
        $this->idRequestor = $config->integration->requestorId;
    }
}
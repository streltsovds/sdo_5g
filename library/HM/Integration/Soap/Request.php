<?php

// @todo-int
class HM_Integration_Soap_Request
{
    protected $Field = 'Requestor';
    protected $Low = '';
    protected $High = '';

    // не используется
    public function __construct()
    {
        $config = Zend_Registry::get('config');
        $this->Low = $this->High = $config->integration->requestorId;
    }

    static public function get($method, $requireInputParameter)
    {
        $withoutParam =  <<<XML
<ns1:{$method}>
</ns1:{$method}>
XML;

        $withParam =  <<<XML
<ns1:{$method}>
 <ns1:parameters_table>
    <ns1:parameters_string>
       <ns1:field></ns1:field>
       <ns1:low></ns1:low>
       <ns1:high></ns1:high>
    </ns1:parameters_string>
 </ns1:parameters_table>
</ns1:{$method}>
XML;
        return $requireInputParameter ? $withParam : $withoutParam;
    }
}
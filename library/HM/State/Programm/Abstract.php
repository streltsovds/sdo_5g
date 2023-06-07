<?php
abstract class HM_State_Programm_Abstract extends HM_State_Abstract
{
    protected $_programmEventId;
    
    public function __construct($programmEventId)
    {
        $this->_programmEventId = $programmEventId;
    }    
    
    public function getProgrammEventId()
    {
        return $this->_programmEventId;
    }
}

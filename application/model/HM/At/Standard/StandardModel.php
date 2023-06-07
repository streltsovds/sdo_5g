<?php
class HM_At_Standard_StandardModel extends HM_Model_Abstract
{
    public function getName()
    {
        return $this->name;    
    }
    
    public function getIcon()
    {
        return Zend_Registry::get('config')->url->base . 'images/session-icons/standard.png';
    }

    public function getShortName()
    {
        return $this->name;
    }

}

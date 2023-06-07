<?php
class HM_Form_Element_Image extends Zend_Form_Element
{
    const ITEMS_NEW = 'new';
    
    public $helper = 'imageMap';

    public function init()
    {
        $this->setIsArray(true);
            /*->setFilters(array(array('multiSet', array(
                $this->getName(),
                $this->getAttrib('dependences')
            ))));*/

        parent::init();
    }
    
    public function isValid($value, $context = null)
    {
        $this->setValue($value);
        
        return true;
    }
    
    public function getValue()
    {
        $this->_filterValue($this->_value, $this->_value);
        return $this->_value;
    }
}

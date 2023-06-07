<?php
class HM_Form_Element_Vue_SingleChoice extends HM_Form_Element_Vue_MultiSet
{
    const ITEMS_NEW = 'new';

    public $helper = 'vueSingleChoice';
    protected $_isArray = true;

    protected function _getErrorMessages()
    {
        return $this->getErrorMessages();
    }

    public function getValue()
    {
        $this->_filterValue($this->_value, $this->_value);
        return $this->_value;
    }
}

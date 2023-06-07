<?php
class HM_Form_Element_Vue_Image extends HM_Form_Element_Vue_Element
{
    public $helper = 'vueImage';

    public function getValue()
    {
        return $this->_value;
    }
}
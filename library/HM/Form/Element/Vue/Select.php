<?php

class HM_Form_Element_Vue_Select extends Zend_Form_Element_Multi
{
    public $helper = "vueSelect";

    /**
     * Set element value
     *
     * @param  mixed $value
     * @return Zend_Form_Element
     */
    public function setValue($value)
    {
        parent::setValue(is_array($value) ? $value : (string)$value);
        return $this;
    }
}
<?php

class HM_Form_Element_UiTreeSelect extends ZendX_JQuery_Form_Element_UiWidget
{
    public $helper = "UiTreeSelect";

    public function init()
    {
        if (isset($this->multiple) && $this->multiple) {
            $this->setIsArray(true);
        }
    }
}
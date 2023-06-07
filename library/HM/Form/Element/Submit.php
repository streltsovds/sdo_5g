<?php
/**
 * Created by PhpStorm.
 * User: dvukhzhilov
 * Date: 15-Oct-18
 * Time: 9:30 AM
 */

class HM_Form_Element_Submit extends Zend_Form_Element_Submit {
    public function render(Zend_View_Interface $view = null)
    {
        return "<button name='{$this->getName()}' id='{$this->getId()}' type='submit' class='v-btn theme--light primary'><div class='v-btn__content'>{$this->getLabel()}</div></button>";
    }
}
<?php
/**
 * Created by PhpStorm.
 * User: dvukhzhilov
 * Date: 15-Oct-18
 * Time: 9:30 AM
 */

class HM_Form_Element_Vue_SubmitLink extends Zend_Form_Element_Xhtml {

    public function render(Zend_View_Interface $view = null)
    {
        $id = $this->getId();
        $name = $this->getName();
        $label = $this->getLabel();
        $url = $this->getAttrib('url');

        return <<<HTML
<hm-submit-link
    id='$id'
    name='$name'
    label='$label'
    url='$url'
>
</hm-submit-link>
HTML;

    }
}
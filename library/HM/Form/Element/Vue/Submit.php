<?php
/**
 * Created by PhpStorm.
 * User: dvukhzhilov
 * Date: 15-Oct-18
 * Time: 9:30 AM
 */

class HM_Form_Element_Vue_Submit extends HM_Form_Element_Vue_Element {

    public function render(Zend_View_Interface $view = null)
    {
        $id = $this->getId();
        $name = $this->getName();
        $label = $this->getLabel();

        $formId = $this->getFormId() ? $this->getFormId() : null;
        $redirectUrls = $this->getRedirectUrls();
        $isAjax = (bool) $this->getAttrib('isAjax') ? 'true' : 'false';

        return <<<HTML
<hm-submit
    id='$id'
    name='$name'
    label='$label'
    :is-ajax=$isAjax
    form-id='$formId'
    :redirect-urls='$redirectUrls'
>
</hm-submit>
HTML;

    }

    private function getFormId() {
        $decorator = $this->getDecorator('VueViewHelper');
        return $decorator ? $decorator->getOption('formName') : "";
    }


    private function getRedirectUrls()
    {
        $redirectUrls = $this->getAttrib('redirectUrls');
        return is_array($redirectUrls) ? json_encode($redirectUrls) : "[]";
    }
}
<?php

class HM_View_Helper_VueDatePicker extends Zend_View_Helper_HtmlElement
{
    public function vueDatePicker($id, $value = null, array $attribs = array(), array $errors = array())
    {
        if ($errors) {
            $attribs['errorsData'] = $errors;
        }

        $attribsJson = json_encode($attribs, JSON_UNESCAPED_UNICODE);
//        $errorsJson = json_encode($errors, JSON_UNESCAPED_UNICODE);
//        :errors='$errorsJson'

        return <<<HTML
<hm-date-picker
    name='$id'
    value='$value'
    v-bind='$attribsJson'
>
</hm-date-picker>
HTML;

    }
}
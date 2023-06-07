<?php

class HM_View_Helper_VueCheckbox extends Zend_View_Helper_HtmlElement
{
    public function vueCheckbox($id, $value = null, array $attribs = array(), array $errors = array())
    {

        $attribs = ZendX_JQuery::encodeJson($attribs);

        $value = boolval($value) ? true : null;
        $value = ZendX_JQuery::encodeJson($value);
        $errors = ZendX_JQuery::encodeJson($errors);

        return <<<HTML
<hm-checkbox
    name='$id'
    :attribs='$attribs'
    :checked='$value'
    :errors='$errors'
>
</hm-checkbox>
HTML;

    }
}
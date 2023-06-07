<?php

class HM_View_Helper_VueMultiCheckbox extends Zend_View_Helper_HtmlElement
{
    public function vueMultiCheckbox($id, $value = [], array $attribs = array(), array $errors = array())
    {

        $attribs = ZendX_JQuery::encodeJson($attribs);
        $value = ZendX_JQuery::encodeJson(array_unique($value));
        $errors = ZendX_JQuery::encodeJson($errors);

        return <<<HTML
<hm-multi-checkbox
    name='$id'
    :attribs='$attribs'
    :value='$value'
    :errors='$errors'
>
</hm-multi-checkbox>
HTML;

    }
}
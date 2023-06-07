<?php

class HM_View_Helper_VueMultiSelect extends Zend_View_Helper_HtmlElement
{
    public function vueMultiSelect($id, $value = null, array $attribs = array(), array $errors = array())
    {
        $attribs = ZendX_JQuery::encodeJson($attribs);
        $errors = ZendX_JQuery::encodeJson($errors);
        $value = $value ? ZendX_JQuery::encodeJson($value) : "[]";

        return <<<HTML
<hm-multi-select
    name='$id'
    :attribs='$attribs'
    :errors='$errors'
    :value='$value'
>
</hm-multi-select>
HTML;

    }
}
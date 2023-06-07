<?php

class HM_View_Helper_VueMultiText extends Zend_View_Helper_HtmlElement
{
    public function vueMultiText($id, $value = array(), array $attribs = array(), array $errors = array())
    {

        $attribs = ZendX_JQuery::encodeJson($attribs);
        $errors = ZendX_JQuery::encodeJson($errors);
        $value = ZendX_JQuery::encodeJson(array_values($value));

        return <<<HTML
<hm-multi-text
    name='$id'
    :attribs='$attribs'
    :value='$value'
    :errors='$errors'
>
</hm-multi-text>
HTML;
    }
}
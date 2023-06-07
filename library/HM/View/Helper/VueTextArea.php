<?php

class HM_View_Helper_VueTextArea extends Zend_View_Helper_HtmlElement
{
    public function vueTextArea($id, $value = null, array $attribs = [], array $errors = [])
    {

        $attribsJson = HM_Json::encodeErrorThrow($attribs);
        $errorsJson = HM_Json::encodeErrorThrow($errors);
        $valueJson = HM_Json::encodeErrorThrow($value);

        return <<<HTML
<hm-textarea
    name='$id'
    :attribs='$attribsJson'
    :errors='$errorsJson'
    :value='$valueJson'
>
</hm-textarea>
HTML;
    }
}

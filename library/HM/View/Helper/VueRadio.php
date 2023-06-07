<?php

class HM_View_Helper_VueRadio extends Zend_View_Helper_HtmlElement
{
    public function vueRadio($id, $value = null, array $attribs = array(), array $errors = array(), array $options = array())
    {

        $attribs = ZendX_JQuery::encodeJson($attribs);
        $options = ZendX_JQuery::encodeJson($options);
        $errors = ZendX_JQuery::encodeJson($errors);
        $value = ZendX_JQuery::encodeJson($value);

        return <<<HTML
<hm-radio
    name='$id'
    :attribs='$attribs'
    :options='$options'
    :value='$value'
    :errors='$errors'
>
</hm-radio>
HTML;

    }
}
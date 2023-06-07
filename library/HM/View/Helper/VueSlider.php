<?php

class HM_View_Helper_VueSlider extends Zend_View_Helper_HtmlElement
{
    public function vueSlider($id, $value = null, array $attribs = array(), array $errors = array())
    {

        $attribs = ZendX_JQuery::encodeJson($attribs);
        $errors = ZendX_JQuery::encodeJson($errors);
        $value = ZendX_JQuery::encodeJson($value);

        return <<<HTML
<hm-slider
    name='$id'
    :attribs='$attribs'
    :value='$value'
    :errors='$errors'
>
</hm-slider>
HTML;

    }
}
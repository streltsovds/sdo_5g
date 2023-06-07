<?php

class HM_View_Helper_VueTimeSlider extends Zend_View_Helper_HtmlElement
{
    public function vueTimeSlider($id, $value = null, array $attribs = array(), array $errors = array())
    {

        $attribs = ZendX_JQuery::encodeJson($attribs);
        $errors = ZendX_JQuery::encodeJson($errors);
        $value = ZendX_JQuery::encodeJson($value);

        return <<<HTML
<hm-time-slider
    name='$id'
    :attribs='$attribs'
    :value='$value'
    :errors='$errors'
>
</hm-time-slider>
HTML;

    }
}
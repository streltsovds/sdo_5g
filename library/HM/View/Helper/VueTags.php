<?php

class HM_View_Helper_VueTags extends Zend_View_Helper_HtmlElement
{
    public function vueTags($id, $value = null, array $attribs = array(), array $errors = array())
    {

        $attribs = ZendX_JQuery::encodeJson($attribs);
        $errors = ZendX_JQuery::encodeJson($errors);
        $value = $value ? ZendX_JQuery::encodeJson($value) : "[]";

        return <<<HTML
<hm-tags
    name='$id'
    :attribs='$attribs'
    :value='$value'
    :errors='$errors'
>
</hm-tags>
HTML;

    }
}
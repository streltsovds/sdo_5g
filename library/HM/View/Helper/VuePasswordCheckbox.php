<?php

class HM_View_Helper_VuePasswordCheckbox extends Zend_View_Helper_HtmlElement
{
    public function vuePasswordCheckbox($id, $value = null, array $attribs = array(), array $errors = array())
    {

        if (array_key_exists('generatepassword', $attribs) && array_key_exists('generatepassword', $_POST)) {
            $attribs['generatepassword']['value'] = !!$_POST['generatepassword'];
        }

        if (array_key_exists('userpasswordrepeat', $attribs)) {
            $attribs['userpasswordrepeat']['value'] = null;
        }

        $attribs = ZendX_JQuery::encodeJson($attribs);
        $errors = ZendX_JQuery::encodeJson($errors);
        $value = ZendX_JQuery::encodeJson($value);

        return <<<HTML
<hm-password-checkbox
    name='$id'
    :attribs='$attribs'
    :value='$value'
    :errors='$errors'
>
</hm-password-checkbox>
HTML;

    }
}
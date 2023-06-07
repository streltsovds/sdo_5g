<?php

class HM_View_Helper_VueText extends Zend_View_Helper_HtmlElement
{
    public function vueText($id, $value = null, array $attribs = array(), array $errors = null)
    {
        $attribsJson = HM_Json::encodeErrorThrow($attribs);
        $errorsJson = HM_Json::encodeErrorThrow((array) $errors);
//        $valueJson = ZendX_JQuery::encodeJson($value);
        $valueHtmlEncoded = htmlspecialchars($value, ENT_COMPAT | ENT_HTML5);
//        $convmap = array(0x0100, 0xFFFF, 0, 0xFFFF);
//        $valueHtmlEncoded = mb_encode_numericentity($value, $convmap, 'UTF-8');

        // ВНИМАНИЕ!!! Очень важно использовать для value именно двойные кавычки,
        // иначе поломается отображение в этом елементе текста, содержащего и одинарные и двойные кавычки,
        // а также может нарушить валидность json-a и юзер увидит белую страницу.
        return <<<HTML
<hm-text
    name='$id'
    :attribs='$attribsJson'
    value="$valueHtmlEncoded"
    :errors='$errorsJson'
>
</hm-text>
HTML;

    }
}

<?php

class HM_View_Helper_VueSelect extends Zend_View_Helper_HtmlElement
{
    public function vueSelect($id, $value = null, array $attribs = array(), array $errors = array())
    {
        $options = [];
        $options['items'] = [];
        if (isset($attribs['options'])) {
            $options['items'] = $attribs['options'];
            if(is_array($value)) {
                $options['selected'] = array();
                foreach($value as $val) {
                    if(!array_key_exists($val, $options['items'])) continue;
                    $options['selected'][] = (string)$val;
                }
            } else {
                $options['selected'] = (array_key_exists($value, $options['items'])) ? (string)$value : null;
            }
            unset($attribs['options']);
        }

        $attribs = ZendX_JQuery::encodeJson($attribs);
        $errors = ZendX_JQuery::encodeJson($errors);
        $options["keyOrder"] = array_keys($options["items"]);

        $options = json_encode($options, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_HEX_APOS);

        return <<<HTML
<hm-select
    name='$id'
    :attribs='$attribs'
    :options='$options'
    :errors='$errors'
>
</hm-select>
HTML;

    }
}
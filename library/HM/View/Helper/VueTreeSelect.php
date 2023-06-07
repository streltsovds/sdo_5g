<?php

class HM_View_Helper_VueTreeSelect extends Zend_View_Helper_HtmlElement
{
    public function vueTreeSelect($id, $value = null, array $attribs = array(), array $errors = array())
    {
        $options = null;

        if (isset($attribs['Params']) && isset($attribs['Params']['options'])) {
            $options = $attribs['Params']['options'];
        }

        $attribs = ZendX_JQuery::encodeJson($attribs);
        $options = ZendX_JQuery::encodeJson($options);
        $errors = ZendX_JQuery::encodeJson($errors);


        return <<<HTML
<hm-tree-select
            name='$id'
            :attribs='$attribs'
            :options-prop='$options'
            :errors='$errors'
            >
          </hm-tree-select>
HTML;
    }
}
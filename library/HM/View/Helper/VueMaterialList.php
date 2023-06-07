<?php

class HM_View_Helper_VueMaterialList extends Zend_View_Helper_HtmlElement
{
    public function vueMaterialList($id, $value = null, array $attribs = array(), array $errors = array())
    {
        $result = "<hm-choose-material id=' " .$id. " ' :data-choose=' ". HM_Json::encodeErrorSkip(array_filter((array) $attribs['multiOptions'])) ." '/>";

        return $result;
    }
}
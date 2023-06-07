<?php

class HM_View_Helper_VueRubricator extends Zend_View_Helper_HtmlElement
{
    public function vueRubricator($items, $url, $gridId = '', $gridUrl = null)
    {
        $items = ZendX_JQuery::encodeJson($items);
        $url = ZendX_JQuery::encodeJson($url);
        $gridId = ZendX_JQuery::encodeJson($gridId);
        $gridUrl = ZendX_JQuery::encodeJson($gridUrl);

        return <<<HTML
<hm-rubricator
    :data='$items'
    :url='$url'
    :grid-id='$gridId'
    :grid-url='$gridUrl'
>
</hm-rubricator>
HTML;

    }
}
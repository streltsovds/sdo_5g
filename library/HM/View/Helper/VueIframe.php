<?php

class HM_View_Helper_VueIframe extends Zend_View_Helper_HtmlElement
{
    /**
     * использование в
     * @see HM_Form_ResourceTypeHtml
     */
    public function vueIframe($id, $value = null, array $attribs = array(), array $errors = array())
    {
        $url = $attribs['url'];

//        $attribs = ZendX_JQuery::encodeJson($attribs);
//        $errors = ZendX_JQuery::encodeJson($errors);
//        $value = ZendX_JQuery::encodeJson($value);


        // @todo: реализовать передачу данных туда-обратно

        $iframeProps = [
            'src' => $url,
            'width' => '100%',
            'height' => 600,
            'frameborder' => 0,
        ];

        if (isset($attribs['htmlAttribs'])) {
            $iframeProps = array_merge($iframeProps, $attribs['htmlAttribs']);
        }

//        if (isset($attribs['hideSaveButton'])) {
//            data - slider - editor - no - save - button
//            }

        $iframePropsJson = HM_Json::encodeErrorThrow($iframeProps);

        return <<<HTML
<iframe v-bind='$iframePropsJson'></iframe>
HTML;

    }
}
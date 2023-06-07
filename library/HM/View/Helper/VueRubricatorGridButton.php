<?php

class HM_View_Helper_VueRubricatorGridButton extends Zend_View_Helper_HtmlElement
{
    /**
     * @param string $buttonLabel - надпись на кнопке, когда никакое значение не выбрано
     * @param array|null $value - выделенный объект
     * @param array $rubricatorProps
     * @param bool $autoOpen - открывать автоматически при загрузке, если значение не выбрано
     * @return string
     * @throws Exception
     */
    public function vueRubricatorGridButton($buttonLabel = '', $value = null, $rubricatorProps = [], $autoOpen = true)
    {
        $rubricatorProps = array_merge(
            [
                'gridId' => '',
                'gridUrl' => null,
                'itemsData' => [],
                'url' => '',
                'isAdmin' => false,
            ],
            $rubricatorProps
        );

        $autoOpenJson = HM_Json::encodeErrorThrow($autoOpen);
        $rubricatorPropsJson = HM_Json::encodeErrorThrow($rubricatorProps);
        $valueJson = HM_Json::encodeErrorThrow($value);


        return <<<HTML
<hm-rubricator-grid-button
    :auto-open='$autoOpenJson'
    label='$buttonLabel'
    :menu-props='{maxWidth: "calc(100vw - " + (appComputedContentMarginsWidth + 100) + "px)"}'
    :rubricator-props='$rubricatorPropsJson'
    target-style="margin-right: 32px"
    :value='$valueJson'
>
</hm-rubricator-grid-button>
HTML;

    }
}

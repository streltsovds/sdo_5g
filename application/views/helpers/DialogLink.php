<?php

class HM_View_Helper_DialogLink extends HM_View_Helper_Abstract
{

    /**
     * @param string $linkText
     * @param array $options
     *  - @see `<hm-modal-activator>` (frontend/app/src/components/hm-modal/activator.vue) props
     * @return string
     * @throws Exception
     */
    public function dialogLink($linkText, $options = [])
    {
        $options['title'] = isset($options['title']) ?  $options['title'] : $linkText;

        $optionsJson = HM_Json::encodeErrorThrow($options);

        return
            "<hm-modal-activator v-bind='${optionsJson}'>
                <a href='javascript:void(0);'>{{ _('${linkText}') }}</a>
            </hm-modal-activator>";
    }
}
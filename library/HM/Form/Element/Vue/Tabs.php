<?php

class HM_Form_Element_Vue_Tabs extends HM_Form_Element_Vue_Element {

    public function render(Zend_View_Interface $view = null)
    {
        $tabsJson = $this->getAttrib("tabs");
        $defaultTabName = $this->getAttrib("default");
        $defaultIndex = null;
        /** @var HM_Form $form */
        $form = $this->getAttrib("form");

        $tabsViews = [];
        foreach ($tabsJson as $tabName => $props) {
            $label = $props['title'];
            $tab = $props['groups'];

            if($defaultTabName === $tabName) {
                $defaultIndex = $label;
            }

            $tabViews = [];
            $isValidTab = true;
            /** @var Zend_Form_DisplayGroup $displayGroup */
            foreach ($tab as $tabDisplayGroup) {
                $displayGroup = $form->getDisplayGroup($tabDisplayGroup);
                if (!$displayGroup) continue;

                $displayGroup->setAttrib('is_tab', true);

                $tabView = $displayGroup->render();

//                $tabView = str_replace("'", "&apos;",  $tabView);

                $tabViews[] = $tabView;

                $elements = $displayGroup->getElements();

                if ($form->getRequest()->isPost()) {
                    $isValidTab = $this->isValidTab($elements, $form);
                }

                $this->removeElementsFromForm($elements, $form);
                $form->removeDisplayGroup($tabDisplayGroup);
            }
            $tabsViews[$label] =  [
                "content" => implode("", $tabViews),
                "description" => $props['description'],
                "isValid" => $isValidTab,
            ];
        }

        $tabsJson = HM_Json::encodeErrorThrow($tabsViews);

        return <<<HTML
        <hm-tabs
          :tabs='$tabsJson'
          default-tab="$defaultIndex"
        >
        </hm-tabs>
HTML;

    }

    /**
     * @param $elements
     * @param HM_Form $form
     */
    private function removeElementsFromForm($elements, $form)
    {
        /** @var Zend_Form_Element $element */
        foreach ($elements as $element) {
            $form->removeElement($element->getName());
        }
    }

    /**
     * @param $elements
     * @param HM_Form $form
     */
    private function isValidTab($elements, $form)
    {
        $request = $form->getRequest();
        $params = $request->getParams();

        /** @var Zend_Form_Element $element */
        foreach ($elements as $element) {
            $value = array_key_exists($element->getName(), $params) ? $params[$element->getName()] : null;
            if (!$element->isValid($value)) return false;
        }

        return true;
    }
}

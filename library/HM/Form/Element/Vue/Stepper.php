<?php

class HM_Form_Element_Vue_Stepper extends HM_Form_Element_Vue_Element {

    public function render(Zend_View_Interface $view = null)
    {
        $steps = $this->getAttrib("steps");
        /** @var HM_Form $form */
        $form = $this->getAttrib("form");

        $stepsViews = [];
        $numStep = 1;
        foreach ($steps as $label => $step) {
            $stepViews = [];
            $isValidStep = true;
            /** @var Zend_Form_DisplayGroup $displayGroup */
            foreach ($step as $stepDisplayGroup) {
                $displayGroup = $form->getDisplayGroup($stepDisplayGroup);
                if (!$displayGroup) continue;
                $displayGroup->removeDecorator("Fieldset");
                $stepViews[] = $displayGroup->render(); //str_replace("'", "&apos;", $displayGroup->render());
                $elements = $displayGroup->getElements();

                if ($form->getRequest()->isPost()) {
                    $isValidStep = $this->isValidTab($elements, $form);
                }

                $this->removeElementsFromForm($elements, $form);

                $form->removeDisplayGroup($stepDisplayGroup);
            }
            $stepsViews[$numStep] =  [
                "label" => $label,
                "content" => implode("", $stepViews),
                "isValid" => $isValidStep
            ];

            $numStep++;
        }

//        $steps = json_encode($stepsViews, JSON_FORCE_OBJECT);
        $steps = HM_Json::encodeErrorSkip($stepsViews);
        return <<<HTML
        <hm-stepper
          :steps='$steps'>
        </hm-stepper>
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
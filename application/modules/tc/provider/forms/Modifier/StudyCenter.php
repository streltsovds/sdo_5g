<?php
class HM_Form_Modifier_StudyCenter extends HM_Form_Modifier
{
    /**
     * @return array
     */
    protected function _getActions()
    {
        $form    = $this->getForm();

        $details = $form->getDisplayGroup('details');
        $form->removeDisplayGroup($details->getName());
        $elements = $details->getElements();
        foreach ($elements as $field => $element) {
            $form->removeElement($field);
        }

        return array();
    }


}
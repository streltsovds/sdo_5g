<?php
class HM_Form_Modifier_Provider extends HM_Form_Modifier
{
    /**
     * @return array
     */
    protected function _getActions()
    {
        $hideFields = array('department_id', 'licence', 'registration', 'pass_by');
        $form    = $this->getForm();
        foreach ($hideFields as $field) {
            $form->removeElement($field);
        }

        return array();
    }
}
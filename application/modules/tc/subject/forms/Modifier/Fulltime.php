<?php
abstract class HM_Form_Modifier_Fulltime extends HM_Form_Modifier
{
    protected $hideFields    = array();
    protected $skipFields    = array('subid', 'cancel', 'submit', 'cancelUrl');
    protected $displayGroups = array();
    protected $modifyFields  = array();
    /**
     * @return array
     */
    protected function _getActions()
    {
        $form = $this->getForm();

        $allGroups = $form->getDisplayGroups();
        foreach ($allGroups as $group) {
            if (!in_array($group->getName(), $this->displayGroups)) {
                $form->removeDisplayGroup($group->getName());
                $elements = $group->getElements();
                $this->hideFields = array_merge($this->hideFields, array_keys($elements));
            }
        }
        $this->hideFields = array_unique($this->hideFields);

        foreach ($this->hideFields as $field) {
            if (!in_array($field, $this->skipFields)) {
                $form->removeElement($field);
            }
        }

        $result = array();
        foreach ($this->modifyFields as $fieldData) {
            $elem = $form->getElement($fieldData[0]);
            if ($elem) {
                switch ($fieldData[1]) {
                    case 'disable':
                        $result[] = array(
                            'name'         => $fieldData[0],
                            'type'         => 'setOptions',
                            'paramValue'   =>  array('disabled' => true)
                        );
                        $result[] = array(
                            'name'         => $fieldData[0],
                            'type'         => 'setOptions',
                            'paramValue'   =>  array('required' => false)
                        );
                        break;

                    case 'required':
                        $result[] = array(
                            'name'         => $fieldData[0],
                            'type'         => 'setOptions',
                            'paramValue'   =>  array('required' => true)
                        );
                        break;

                    case 'type':
                        $result[] = array(
                            'name'         => $fieldData[0],
                            'type'         => 'changeType',
                            'element_type' =>  $fieldData[2]
                        );
                        break;

                }
            }

        }

        return $result;
    }


}
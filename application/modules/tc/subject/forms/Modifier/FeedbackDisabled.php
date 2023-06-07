<?php
class HM_Form_Modifier_FeedbackDisabled extends HM_Form_Modifier
{
    protected $_disableFields  = array('mark_goal', 'mark_goal2', 'longtime',  'mark_usefull', 'mark_motivation',
        'mark_course', 'mark_teacher', 'mark_papers', 'mark_organization', 'recomend', 'mark_final');
    protected $_readOnlyFields = array('text_goal', 'text_usefull', 'text_not_usefull', 'text');

    /**
     * @return array
     */
    protected function _getActions()
    {
        $result = array(
            array(
                'name'         => 'submit',
                'type'         => 'changeType',
                'element_type' => 'hidden'
            )
        );

        foreach ($this->_disableFields as $field) {
            $result[] =  array(
                'name'         => $field,
                'type'         => 'setOptions',
                'paramValue'   =>  array('disabled' => true)
            );
        }
        foreach ($this->_readOnlyFields as $field) {
            $result[] =  array(
                'name'         => $field,
                'type'         => 'setOptions',
                'paramValue'   =>  array('readonly' => true)
            );
        }

        return $result;
   }


}
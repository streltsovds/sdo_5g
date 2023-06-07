<?php

class HM_Form_Modifier_Hideuser extends HM_Form_Modifier
{
    /**
     * @return array
     */
    protected function _getActions()
    {

        return array(
            array(
                'name'         => 'user_id',
                'type'         => 'changeType',
                'element_type' => 'hidden'
            )
        );
    }


}
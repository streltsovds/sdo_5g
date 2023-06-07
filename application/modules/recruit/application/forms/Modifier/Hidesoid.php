<?php

class HM_Form_Modifier_Hidesoid extends HM_Form_Modifier
{
    /**
     * @return array
     */
    protected function _getActions()
    {

        return array(
            array(
                'name'         => 'soid',
                'type'         => 'changeType',
                'element_type' => 'hidden'
            )
        );
    }


}
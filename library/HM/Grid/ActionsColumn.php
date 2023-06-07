<?php

class HM_Grid_ActionsColumn extends Bvb_Grid_Extra_Column
{
    public function __construct(HM_Grid_ActionsList $actionsList)
    {
        $this->_field = array(
            'position' => 'right',
            'name'     => 'actions',
            'title'    => _('Действия'),
            'callback' => array(
                'function' => array($actionsList, 'toString'),
                'appendRowToParams' => true
            )
        );
    }

}
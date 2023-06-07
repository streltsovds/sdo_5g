<?php

/**
 *
 */
class HM_Assign_DataGrid_MassAction_Calendar extends HM_DataGrid_MassAction
{
    static public function create(HM_DataGrid $dataGrid, $name, array $options = array())
    {
        $self = parent::create($dataGrid, $name, $options);
        $self->setUrl(
            array(
                'module' => 'assign',
                'controller' => 'teacher',
                'action' => 'calendar'
            )
        );

        return $self;
    }
}
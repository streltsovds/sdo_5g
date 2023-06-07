<?php

/**
 *
 */
class HM_User_DataGrid_MassAction_SetConfirmed extends HM_DataGrid_MassAction
{
    static public function create(HM_DataGrid $dataGrid, $name, array $options = array())
    {
        $self = parent::create($dataGrid, $name, $options);
        $self->setName($name);
        $self->setUrl(['action' => 'set-confirmed']);

        return $self;
    }
}
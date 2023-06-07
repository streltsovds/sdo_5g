<?php

/**
 *
 */
class HM_User_DataGrid_MassAction_SetPassword extends HM_DataGrid_MassAction
{
    static public function create(HM_DataGrid $dataGrid, $name, array $options = array())
    {
        $self = parent::create($dataGrid, $name, $options);
        $self->setName($name);
        $self->setUrl(['action' => 'set-password']);
        $self->setSub([
            'function' => self::SUB_MASS_ACTION_INPUT,
            'params'   => [
                'url'  => $dataGrid->getView()->url($self->getUrl()),
                'name' => 'pass'
            ]
        ]);

        return $self;
    }
}
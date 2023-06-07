<?php

/**
 *
 */
class HM_DataGrid_MassAction_SendMessage extends HM_DataGrid_MassAction
{
    static public function create(HM_DataGrid $dataGrid, $name, array $options = array())
    {
        $self = parent::create($dataGrid, $name, $options);
        $self->setUrl(
            array(
                'module' => 'message',
                'controller' => 'send',
                'action' => 'index'
            )
        );

        return $self;
    }
}
<?php

/**
 *
 */
class HM_DataGrid_Action_SendMessage extends HM_DataGrid_Action
{
    static public function create(HM_DataGrid $dataGrid, $name, array $options = array())
    {
        $self = parent::create($dataGrid, $name);

        $self->setName($name);

        $self->setUrl(
            array(
                'module' => 'message',
                'controller' => 'send',
                'action' => 'index'
            )
        );

        $self->setParams(array('MID'));

        return $self;
    }
}
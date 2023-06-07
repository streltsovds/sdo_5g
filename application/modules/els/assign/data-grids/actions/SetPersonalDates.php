<?php

/**
 *
 */
class HM_Assign_DataGrid_Action_SetPersonalDates extends HM_DataGrid_Action
{
    static public function create(HM_DataGrid $dataGrid, $name, array $options = array())
    {
        $self = parent::create($dataGrid, $name);
        $self->setName($name);
        $self->setUrl(array(
            'module' => 'lesson',
            'controller' => 'list',
            'action' => 'my'
        ));
        if ($options['params']) $self->setParams($options['params']);

        return $self;
    }
}
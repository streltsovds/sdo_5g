<?php

/**
 *
 */
class HM_User_DataGrid_Action_DuplicateMerge extends HM_DataGrid_Action
{
    static public function create(HM_DataGrid $dataGrid, $name, array $options = [])
    {
        $self = parent::create($dataGrid, $name);
        $self->setName($name);
        $self->setUrl([
            'module' => 'user',
            'controller' => 'list',
            'action' => 'duplicate-merge',
            'from' => 'user-list'
        ]);
        if ($options['params']) $self->setParams($options['params']);

        return $self;
    }
}
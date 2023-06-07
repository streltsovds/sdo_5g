<?php

/**
 *
 */
class HM_Subject_DataGrid_Action_EditContent extends HM_DataGrid_Action
{
    static public function create(HM_DataGrid $dataGrid, $name, array $options = array())
    {
        $self = parent::create($dataGrid, $name);
        $self->setName($name);
        $self->setUrl(array(
            'module' => 'subject',
            'controller' => 'material',
            'action' => 'edit-content'
        ));

        if ($options['params']) $self->setParams($options['params']);

        return $self;
    }
}
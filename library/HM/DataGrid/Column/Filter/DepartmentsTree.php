<?php

/**
 *
 */
class HM_DataGrid_Column_Filter_DepartmentsTree extends HM_DataGrid_Column_Filter
{
    static public function create($dataGrid, array $params = [])
    {
        $self = new self;
        $self->setValue(array('render' => 'department'));
        return $self->getValue();
    }
}
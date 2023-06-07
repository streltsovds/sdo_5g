<?php

/**
 *
 */
class HM_DataGrid_Column_Filter_Department extends HM_DataGrid_Column_Filter
{
    static public function create($dataGrid, array $params = [])
    {
        $self = new self($dataGrid);
        $self->setValue(array('render' => 'department'));
        return $self->getValue();
    }
}
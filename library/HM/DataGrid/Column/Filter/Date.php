<?php

/**
 *
 */
class HM_DataGrid_Column_Filter_Date extends HM_DataGrid_Column_Filter
{
    static public function create($dataGrid, array $params = [])
    {
        $self = new self($dataGrid);
        $self->setValue(array('render' => 'date'));
        return $self->getValue();
    }
}
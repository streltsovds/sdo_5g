<?php

/**
 *
 */
class HM_DataGrid_Column_Filter_DateSmart extends HM_DataGrid_Column_Filter
{
    static public function create($dataGrid, array $params = [])
    {
        $self = new self($dataGrid);
        $self->setValue(['render' => 'DateSmart']);
        return $self->getValue();
    }
}
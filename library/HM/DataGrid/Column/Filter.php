<?php

/**
 *
 */
class HM_DataGrid_Column_Filter
{
    protected $value;

    static public function create($dataGrid, array $params = [])
    {
        $self = new self($dataGrid);
        $self->setValue(null);
        return $self->getValue();
    }

    /**
     * @param null $value
     */
    protected function setValue($value)
    {
        $this->value = $value;
    }

    public function getValue()
    {
        return $this->value;
    }

    public function callback($dataGrid)
    {

    }
}
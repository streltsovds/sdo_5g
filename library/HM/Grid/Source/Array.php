<?php

class HM_Grid_Source_Array extends Bvb_Grid_Source_Array
{
    protected $_sourceName = 'array';

    public function __construct (array $array, $titles = null)
    {
        if ( count($array) > 0 ) {
            $this->_fields = array_keys($array[0]);
        } elseif ($titles !== null) {
            $this->_fields = $titles;
        }

        $this->_rawResult = $array;

    }

    public function getFields()
    {
        return $this->_fields;
    }
}
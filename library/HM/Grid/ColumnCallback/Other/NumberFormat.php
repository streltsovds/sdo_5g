<?php

class HM_Grid_ColumnCallback_Other_NumberFormat extends HM_Grid_ColumnCallback_Els_ClassifiersList
{
    public function __invoke($field, $dec = 0, $defaultValue = '')
    {
        return $field ? number_format((int)$field, $dec, '.', ' ') : $defaultValue;
    }
}
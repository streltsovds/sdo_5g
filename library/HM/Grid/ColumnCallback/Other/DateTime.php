<?php

class HM_Grid_ColumnCallback_Other_DateTime extends HM_Grid_ColumnCallback_Abstract
{
    public function __invoke($value)
    {
        if (!$value) {
            return '';
        }

        $date = new HM_Date($value);

        return $date->toString();

    }

}
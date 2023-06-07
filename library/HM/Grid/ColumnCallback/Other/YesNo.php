<?php

class HM_Grid_ColumnCallback_Other_YesNo extends HM_Grid_ColumnCallback_Abstract
{
    public function __invoke($value)
    {
        if ($value) {
            return _('Да');
        }

        return _('Нет');
    }

}
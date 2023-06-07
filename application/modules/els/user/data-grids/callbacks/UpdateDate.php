<?php

/**
 *
 */
class HM_User_DataGrid_Callback_UpdateDate extends HM_DataGrid_Column_Callback_Abstract
{
    public function callback(...$args)
    {
        list($dataGrid, $date) = func_get_args();

        if (empty($date)) return null;
        $upDate = new HM_Date($date);

        return $upDate->get('dd.MM.Y');
    }
}
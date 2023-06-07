<?php

/**
 *
 */
class HM_Assign_DataGrid_Callback_UpdateDateBegin extends HM_DataGrid_Column_Callback_Abstract
{
    public function callback(...$args)
    {
        list($dataGrid, $date) = func_get_args();
        if (!$date) return '';
        $date = new Zend_Date($date, 'YYYY-MM-DD HH:mm:ss');
        $date = iconv('UTF-8', Zend_Registry::get('config')->charset, $date->toString(HM_Locale_Format::getDateFormat()));

        return $date;
    }
}
<?php

/**
 * Текстовый вывод поля "Место хранения"
 */
class HM_Subject_DataGrid_Callback_Materials_Location extends HM_DataGrid_Column_Callback_Abstract
{
    public function callback(...$args)
    {
        list($dataGrid, $location) = func_get_args();
        $location = (int)!(bool)$location;

        $locations = HM_Resource_ResourceModel::getLocaleStatuses();
        return $locations[$location];
    }
}
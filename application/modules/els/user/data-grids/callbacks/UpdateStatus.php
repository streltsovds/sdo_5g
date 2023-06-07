<?php

/**
 *
 */
class HM_User_DataGrid_Callback_UpdateStatus extends HM_DataGrid_Column_Callback_Abstract
{
    public function callback(...$args)
    {
        list($dataGrid, $field) = func_get_args();

        $active = ($field == 0);

        $colorName = $active ? 'themeColors.success' : 'themeColors.error';
        $caption = _($active ? 'Активный' : 'Заблокирован');

        return '<icon-diode :color="' . $colorName . '" style="margin-right: 6px"></icon-diode>' . $caption;
    }
}
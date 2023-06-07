<?php

/**
 *
 */
class HM_DataGrid_Column_Callback_UpdateFio extends HM_DataGrid_Column_Callback_Abstract
{
    public function callback(...$args)
    {
        list($dataGrid, $userId, $userName) = func_get_args();
        $fio = trim($userName);
        if (!strlen($fio)) {
            $fio = sprintf(_('Пользователь #%d'), $userId);
        }
        return $fio;
    }
}
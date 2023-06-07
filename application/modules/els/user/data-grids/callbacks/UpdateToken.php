<?php

/**
 *
 */
class HM_User_DataGrid_Callback_UpdateToken extends HM_DataGrid_Column_Callback_Abstract
{
    public function callback(...$args)
    {
        list($dataGrid, $token) = func_get_args();

        return $token ? _('Установлено') : _('Не установлено');
    }
}
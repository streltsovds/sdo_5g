<?php

/**
 *
 */
class HM_DataGrid_Column_Callback_UpdateGroupColumn extends HM_DataGrid_Column_Callback_Abstract
{
    public function callback(...$args)
    {
        list($dataGrid, $field, $id) = func_get_args();
        return ($field == $id) ? _('Да') : _('Нет');
    }
}
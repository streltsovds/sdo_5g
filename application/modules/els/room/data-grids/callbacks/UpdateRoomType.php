<?php

/**
 *
 */
class HM_Room_DataGrid_Callback_UpdateRoomType extends HM_DataGrid_Column_Callback_Abstract
{
    public function callback(...$args)
    {
        list($dataGrid, $type) = func_get_args();

        $types = HM_Room_RoomModel::getTypes();
        return '<span style="white-space: nowrap;">'.$types[$type].'</span>';
    }
}
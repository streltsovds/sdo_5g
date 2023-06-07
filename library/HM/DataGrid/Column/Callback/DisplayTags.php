<?php

/**
 *
 */
class HM_DataGrid_Column_Callback_DisplayTags extends HM_DataGrid_Column_Callback_Abstract
{
    public function callback(...$args)
    {
        list($dataGrid, $itemId, $itemType) = func_get_args();
        if ( $tags = $dataGrid->getServiceContainer()->getService('Tag')->getStrTagsByIds($itemId, $itemType, true) ) {
            return $tags;
        }
        return '';
    }
}
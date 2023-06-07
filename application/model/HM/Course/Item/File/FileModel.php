<?php

class HM_Course_Item_File_FileModel extends HM_Course_Item_ItemModel
{

    public function getExecuteUrl()
    {
        return Zend_Registry::get('view')->url(
            array(
                'module' => 'file',
                'controller' => 'get',
                'action' => 'file',
                'file_id' => $this->module
            )
        );
    }
}
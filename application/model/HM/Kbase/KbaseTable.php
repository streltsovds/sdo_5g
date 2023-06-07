<?php
class HM_Kbase_KbaseTable extends HM_Db_Table
{
    protected $_name = "materials";
    protected $_primary = array('type', 'title', 'id', 'cdate');
    
    
    public function getDefaultOrder()
    {
        return 'kbase_items.cdate DESC';
    }
}
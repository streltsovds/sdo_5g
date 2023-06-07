<?php
class HM_Eclass_EclassTable extends HM_Db_Table
{
    protected $_name = "eclass";
    protected $_primary = 'id';
    protected $_sequence = '';

    public function getDefaultOrder()
    {
        return array('eclass.id');
    }
}
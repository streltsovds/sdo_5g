<?php
class HM_At_Relation_RelationTable extends HM_Db_Table
{
    protected $_name = "at_relations";
    protected $_primary = "relation_id";
    protected $_sequence = "????"; // @todo

    protected $_referenceMap = array(
//         'UserRelation' => array(
//             'columns'       => 'kpi_id',
//             'refTableClass' => 'HM_At_Relation_User_UserTable',
//             'refColumns'    => 'kpi_id',
//             'propertyName'  => 'user_kpi'
//         ), 
    );
}
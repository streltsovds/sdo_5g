<?php

class HM_Material_MaterialTable extends HM_Db_Table
{
    protected $_name = "materials";
    protected $_primary = array("id", "type");

    protected $_referenceMap = array(
        'Subject' => array(
            'columns' => 'subject_id',
            'refTableClass' => 'HM_Subject_SubjectTable',
            'refColumns' => 'subid',
            'propertyName' => 'subjects'
        ),
    );


    public function getDefaultOrder()
    {
        return array('subjects_resources.resource_id ASC');
    }
}
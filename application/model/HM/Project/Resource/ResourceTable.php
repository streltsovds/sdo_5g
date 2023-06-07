<?php

class HM_Project_Resource_ResourceTable extends HM_Db_Table
{
    protected $_name = "projects_resources";
    protected $_primary = array("project_id", "resource_id");

/*
     protected $_dependentTables = array(
        "HM_Role_ParticipantTable",
         "HM_Role_TeacherTable"
    );
*/    
    protected $_referenceMap = array(
        'Project' => array(
            'columns' => 'project_id',
            'refTableClass' => 'HM_Project_ProjectTable',
            'refColumns' => 'projid',
            'propertyName' => 'projects'
        ),
        'Resource' => array(
            'columns' => 'resource_id',
            'refTableClass' => 'HM_Resource_ResourceTable',
            'refColumns' => 'resource_id',
            'propertyName' => 'resources'
        ),
    );// имя свойства текущей модели куда будут записываться модели зависимости

    

    public function getDefaultOrder()
    {
        return array('projects_resources.resource_id ASC');
    }
}
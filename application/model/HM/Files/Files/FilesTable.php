<?php

class HM_Files_FilesTable extends HM_Db_Table
{
    protected $_name = "files";
    protected $_primary = 'file_id';
    protected $_sequence = 'S_100_1_FILES';
    
    
    protected $_dependentTables = array("HM_Webinar_Files_FilesTable","HM_Files_Videoblock_VideoblockTable");
    
    protected $_referenceMap = array(
        'Webinar' => array(
            'columns'       => 'file_id',
            'refTableClass' => 'HM_Webinar_Files_FilesTable',
            'refColumns'    => 'file_id',
            'onDelete'      => self::CASCADE,
            'propertyName'  => 'webinars' // имя свойства текущей модели куда будут записываться модели зависимости
        ),
        'Videoblock' => array(
            'columns'       => 'file_id',
            'refTableClass' => 'HM_Files_Videoblock_VideoblockTable',
            'refColumns'    => 'file_id',
            'propertyName'  => 'videos' // имя свойства текущей модели куда будут записываться модели зависимости
        ),
    );

    public function getDefaultOrder()
    {
        return array('files.file_id');
    }
}
<?php

class HM_Videoblock_VideoblockTable extends HM_Db_Table
{
    protected $_name = "videoblock";
    protected $_primary = 'videoblock_id';
    protected $_sequence = 'S_100_1_videoblock';

    protected $_referenceMap = array(
        'Files' => array(
            'columns'       => 'file_id',
            'refTableClass' => 'HM_Files_FilesTable',
            'refColumns'    => 'file_id',
            'propertyName'  => 'files' // имя свойства текущей модели куда будут записываться модели зависимости
        ),
    );

    public function getDefaultOrder()
    {
        return array('videoblock.name');
    }
}
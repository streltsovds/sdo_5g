<?php

class HM_Tc_Provider_Files_FilesTable extends HM_Db_Table
{
    protected $_name = "tc_provider_files";
    protected $_primary = array('file_id','provider_id');

    protected $_referenceMap = array(
        'Provider' => array(
            'columns'       => 'provider_id',
            'refTableClass' => 'HM_Tc_Provider_ProviderTable',
            'refColumns'    => 'provider_id',
            //'onDelete'      => self::CASCADE,
            'propertyName'  => 'provider' // имя свойства текущей модели куда будут записываться модели зависимости
        ),
        'Files' => array(
            'columns'       => 'file_id',
            'refTableClass' => 'HM_Files_FilesTable',
            'refColumns'    => 'file_id',
            //'onDelete'      => self::CASCADE,
            'propertyName'  => 'files' // имя свойства текущей модели куда будут записываться модели зависимости
        )

    );// имя свойства текущей модели куда будут записываться модели зависимости

    public function getDefaultOrder()
    {
        return array('list_files.file_id ASC');
    }
}
<?php
class HM_Tc_Document_DocumentModel extends HM_Model_Abstract
{

    const TYPE_FILE = 1;
    const TYPE_BLANK = 2;

    protected $_primaryName = 'document_id';


    public function getServiceName()
    {
        return 'TcDocument';
    }

    public static function uploadPath($subject_id)
    {
          return APPLICATION_PATH . "/../data/upload/subject_documents/".$subject_id.DIRECTORY_SEPARATOR;
    }
    
    public static function getTypes(){
        $types = array(
            self::TYPE_FILE  => 'Скан',
            self::TYPE_BLANK   => 'Бланк',
        );
        return $types;
    }



}
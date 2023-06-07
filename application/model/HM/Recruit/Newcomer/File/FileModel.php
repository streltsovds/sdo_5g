<?php
class HM_Recruit_Newcomer_File_FileModel extends HM_Model_Abstract
{
    const STATE_TYPE_OPEN = 1;
    const STATE_TYPE_PLAN = 2;
    const STATE_TYPE_PUBLISH = 3;
    const STATE_TYPE_RESULT = 4;
    const STATE_TYPE_COMPLETE = 5;

    public static $_processes = null;
    
    public static function getStateTypes()
    {
        if (!static::$_processes) {
            static::$_processes = Zend_Registry::get('serviceContainer')->getService('Process')->getStaticProcess(HM_Process_ProcessModel::PROCESS_PROGRAMM_ADAPTING);
        }
        return array (
            self::STATE_TYPE_PLAN   => _('План'),
        );
    }
    
    public static function StateType($type)
    {
        $types = self::getStateTypes();
        return $types[$type];
    }
}
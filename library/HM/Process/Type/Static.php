<?php
/**
 * Абстрактный класс процесса статического процесса 
 * (который настраивается в processes.xml)
 */
abstract class HM_Process_Type_Static extends HM_Process_Abstract
{
    public function initProcessAbstract()
    {
        $type = $this->getType();
        $this->_processAbstract = Zend_Registry::get('serviceContainer')->getService('Process')->getStaticProcess($type);
    }    
    
    // считаем, что статичные процессы всегда строго последовательны 
    public function isStrict()
    {
        return true;
    }
}

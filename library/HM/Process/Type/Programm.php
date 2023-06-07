<?php
/**
 * Абстрактный класс процесса динамического процесса 
 * (который настраивается через программу)
 */
abstract class HM_Process_Type_Programm extends HM_Process_Abstract
{
    public function initProcessAbstract()
    {
        if ($this->_model->process_id) {
            if ($process = Zend_Registry::get('serviceContainer')->getService('Process')->getOne(
                Zend_Registry::get('serviceContainer')->getService('Process')->findDependence('Programm', $this->_model->process_id)
            )) {
                $this->_processAbstract = $process;
            }
        }   
    }    
}

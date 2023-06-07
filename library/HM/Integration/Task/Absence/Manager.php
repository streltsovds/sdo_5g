<?php

class HM_Integration_Task_Absence_Manager extends HM_Integration_Abstract_Manager implements HM_Integration_Interface_Manager
{
    protected $_targetServiceName = 'Absence';

    static public function getTaskId()
    {
        return HM_Integration_Manager::TASK_ABSENCE;
    }

    public function import($integrationSource)
    {
        $masterManager = Zend_Registry::get('integrationManager');

        try {

            if ($masterManager->isAllSources() && $masterManager->isFirstRun(HM_Integration_Manager::TASK_ABSENCE)) {
                $this->getTargetService()->deleteBy(array('user_external_id LIKE ?' => $integrationSource['key'] . '-%'));
            }
            $items = $this->getService()->fetchAll();

            foreach($items as $item) {

                try {

                    $model = $this->getAdapter()
                        ->init(new HM_Integration_Abstract_Model())
                        ->convert($item)
                        ->getModel();

                    $this->_insert($model);

                } catch (Exception $e) {
                    $this->log($e->getMessage());
                }
            }

        } catch (Exception $e) {
            $this->breakAll($e->getMessage());
        }

        $this->answer(HM_Integration_Abstract_Manager::TASK_STATUS_SUCCESS);

        return $this;
    }

    public function needUpdate($model, $id)
    {
        $updates = array();
        $profile = Zend_Registry::get('serviceContainer')->getService('Absence')->find($id)->current();
        foreach ($model->getAttributes() as $key => $value) {
            if ($profile->$key != $value) $updates[$key] = $value;
        }
        return $updates;
    }

    public function match(HM_Integration_Abstract_Model $model)
    {
        $masterManager = Zend_Registry::get('integrationManager');
        if (!$masterManager->cacheExists('absenceIdExternal2absenceId')) {
            $masterManager->initCache('absenceIdExternal2absenceId');
        }

        if ($value = $masterManager->getCachedValue(implode('-', array($model->user_external_id, $model->absence_begin, $model->absence_end)), 'absenceIdExternal2absenceId')) {
            return $value;
        }

        return false;
    }

}
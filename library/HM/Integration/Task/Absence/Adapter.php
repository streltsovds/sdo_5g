<?php

class HM_Integration_Task_Absence_Adapter extends HM_Integration_Abstract_Adapter implements HM_Integration_Interface_Adapter
{
    protected $_mapping = array(
        'ID' => 'user_external_id',
        'StartDate' => 'absence_begin',
        'EndDate' => 'absence_end',
        'Type' => 'type',
    );

    protected $_defaults = array(
        'type' => HM_Absence_AbsenceModel::TYPE_VACATION
    );

    protected $_externals = array(
    );

    static public function isAbsence($watchTypeStr)
    {
        return !in_array($watchTypeStr, array(
            'Вахта',
            'Время еженедельного отдыха на вахте',
        ));
    }

    public function convert(array $item)
    {
        $masterManager = Zend_Registry::get('integrationManager');
        if (!$masterManager->cacheExists('midExternal2mid')) {
            $masterManager->initCache('midExternal2mid');
        }

        if ($cachedValue = $masterManager->getCachedValue($item['ID'], 'midExternal2mid')) {
            $this->_model->setAttribute('user_id', $cachedValue);
        }

        return parent::convert($item);
    }

    protected function _convertStartDate($date)
    {
        return parent::_convertDate($date);
    }

    protected function _convertEndDate($date)
    {
        return parent::_convertDate($date);
    }

    protected function _convertType($type)
    {
        return $type ? $type : HM_Absence_AbsenceModel::TYPE_VACATION;
    }

}
<?php
class HM_Cycle_CycleModel extends HM_Model_Abstract
{
    const NEWCOMER_DURATION = 3; // months
    const RESERVE_DURATION = 11; // months

    const CYCLE_TYPE_ASSESMENT = 'at';
    const CYCLE_TYPE_PLANNING  = 'tc';
    const CYCLE_TYPE_ADAPTING  = 'recruit'; // подбор/адаптация
    const CYCLE_TYPE_RESERVE   = 'reserve'; // кадровый резерв


    public static function getCycleTypes($onlyEditable = false)
    {
        $service = Zend_Registry::get('serviceContainer')->getService('Extension');
        $return = [];

        if (!$service->getRemover("HM_Extension_Remover_AtRemover")) {
            $return[self::CYCLE_TYPE_ASSESMENT] = _('Оценка персонала');
        }
        if (!$service->getRemover("HM_Extension_Remover_RecruitRemover")) {
            $return[self::CYCLE_TYPE_ADAPTING] = _('Подбор/адаптация');
        }
        if (!$service->getRemover("HM_Extension_Remover_AdaptingRemover")) {
            $return[self::CYCLE_TYPE_RESERVE] = _('Кадровый резерв');
        }

        if (!$onlyEditable) {
            $return[self::CYCLE_TYPE_PLANNING] = _('Планирование обучения');
        }

        return $return;
    }

    public static function getCycleType($type)
    {
        $types = self::getCycleTypes();
        return isset($types[$type]) ? $types[$type] : '';
    }
}
<?php
/**
 * Детализирует методику оценки 360 - определяет логику, специфичную для оценки руководителем
 *
 */
class HM_At_Evaluation_Method_Kpi_ParentreserveModel extends HM_At_Evaluation_Method_KpiModel implements HM_At_Evaluation_Method_Interface
{
    public function getRespondents($targetPosition, $user = null)
    {
        // основных респондентов нет, только custom
        return array();
    }

    public function getRespondentsCustom($position)
    {
        $return = array();
        if ($this->reserve_id) {
            if (count($collection = Zend_Registry::get('serviceContainer')->getService('HrReserve')->findDependence('ReservePosition', array('reserve_id = ?' => $this->reserve_id)))) {
                $reserve = $collection->current();
                if (count($reserve->reservePosition)) {
                    $reservePosition = $reserve->reservePosition->current();
                    $serialized = $reservePosition->custom_respondents;

                    $respondentIds = unserialize($serialized);
                    if (is_array($respondentIds) && count($respondentIds)) {
                        foreach ($respondentIds as $respondentId) {
                            $position = Zend_Registry::get('serviceContainer')->getService('Orgstructure')->find($respondentId)->current();
                            $return[] = $position->soid ? $position : Zend_Registry::get('serviceContainer')->getService('Orgstructure')->getDummyPosition($respondentId);
                        }
                    }
                }
            }
        }
        return $return;
    }

    public function getDefaults($user)
    {
        if (!is_a($user, 'HM_User_UserModel')) return false;
        return array(
            'name' => sprintf($msg = _('Оценка выполнения задач участником кадрового резерва %s'), $user->getName()),
        );
    }

    public function isAllowCustomRespondent()
    {
        return true; // те же руководители, что и в 360
    }

    
    public function isAvailableForProgramm($programmType)
    {
        switch ($programmType) {
            case HM_Programm_ProgrammModel::TYPE_RESERVE:
                return true;
            break;
        }
        return false;
    }    
}
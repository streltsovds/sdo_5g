<?php
/**
 * Детализирует методику оценки KPI - определяет логику, специфичную для самооценки
 *
 */
class HM_At_Evaluation_Method_Kpi_SelfModel extends HM_At_Evaluation_Method_KpiModel implements HM_At_Evaluation_Method_Interface
{
    public function getRespondents($position, $user = null)
    {
        return array($position);
    }
    
    public function isAvailableForProgramm($programmType)
    {
        switch ($programmType) {
            case HM_Programm_ProgrammModel::TYPE_ASSESSMENT:
            case HM_Programm_ProgrammModel::TYPE_RESERVE:
                return true;
            break;
        }
        return false;
    }      
    
    public function isOtherRespondentsEventsVisible()
    {
        return true;
    }       
}
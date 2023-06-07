<?php
/**
 * Детализирует методику оценки 360 - определяет логику, специфичную для оценки руководителем
 *
 */
class HM_At_Evaluation_Method_Competence_ParentfunctionalModel extends HM_At_Evaluation_Method_CompetenceModel implements HM_At_Evaluation_Method_Interface
{
    public function getRespondents($targetPosition, $user = null)
    {
        // только ручная настройка
        return array();
    }

    public function isAllowCustomRespondent()
    {
        return true;
    }

    public function isAvailableForProgramm($programmType)
    {
        switch ($programmType) {
            case HM_Programm_ProgrammModel::TYPE_ASSESSMENT:
                return true;
            break;
        }
        return false;
    }    
}
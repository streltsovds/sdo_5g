<?php
/**
 * Детализирует методику оценки 360 - определяет логику, специфичную для оценки руководителем
 *
 */
class HM_At_Evaluation_Method_Competence_ClientModel extends HM_At_Evaluation_Method_CompetenceModel implements HM_At_Evaluation_Method_Interface
{
    public function getRespondents($targetPosition, $user = null)
    {
        // только ручная настройка
        return array();
    }

    public function getDefaults($user)
    {
        if (!is_a($user, 'HM_User_UserModel')) return false;
        return array(
            'name' => sprintf($msg = _('Оценка компетенций %s'), $user->getName()),
        );
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
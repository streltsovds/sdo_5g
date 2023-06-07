<?php
/**
 * Детализирует методику оценки по компетенциям - определяет логику, специфичную для оценки рекрутером
 *
 */
class HM_At_Evaluation_Method_Competence_RecruiterModel extends HM_At_Evaluation_Method_CompetenceModel implements HM_At_Evaluation_Method_Interface
{
    public function getRespondents($position, $user = null)
    {
        $positions = array();
            if ($this->vacancy_id) {
            if (count($collection = Zend_Registry::get('serviceContainer')->getService('RecruitVacancyRecruiterAssign')->fetchAllHybrid('Recruiter', 'Position', 'Recruiter', array('vacancy_id = ?' => $this->vacancy_id)))) {
                foreach ($collection as $recruiterAssign) {
                    if (count($recruiterAssign->position)) {
                        $position = $recruiterAssign->position->current(); // если он ещё и совместитель - не учитываем это
                        $positions[] = $position;
                    } elseif (count($recruiterAssign->recruiters)) {
                        $recruiter = $recruiterAssign->recruiters->current();
                        $positions[] = Zend_Registry::get('serviceContainer')->getService('Orgstructure')->getDummyPosition($recruiter->user_id);
                    }
                }
            }
        } 
        
        // в адаптации нет оценки компетенций, RecruitNewcomerRecruiterAssign можно не проверять
        
        return $positions;
    }

    public function getDefaults($user)
    {
        if (!is_a($user, 'HM_user_UserModel')) return false;
        return array(
            'name' => sprintf($msg = _('Оценка компетенций кандидата %s'), $user->getName()),
        );
    }

    // достаточно, чтобы один из рекрутёров заполнил свою анкету
    // и процесс двинется дальше
    public function isFullCompletionRequired()
    {
        return false;
    }
    
    public function isAvailableForProgramm($programmType)
    {
        switch ($programmType) {
            case HM_Programm_ProgrammModel::TYPE_RECRUIT:
                return true;
            break;
        }
        return false;
    }        
}
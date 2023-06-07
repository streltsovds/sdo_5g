<?php
/**
 * Методика оценки "оценка KPI" (или оценка достижений)
 *
 */
abstract class HM_At_Evaluation_Method_KpiModel extends HM_At_Evaluation_EvaluationModel
{
    static public function getMethodId()
    {
        return HM_At_Evaluation_EvaluationModel::TYPE_KPI;
    }
        
    static public function getRelationTypeTitle($relationTypeId, $short = false)
    {
        $types = self::getRelationTypes();
        return isset($types[$relationTypeId]) ? $types[$relationTypeId] : false;
    }    
    
    static public function getMethodName()
    {
        return _('Оценка KPI');
    }
    
    static public function getRelationTypes()
    {
        return array(
            self::RELATION_TYPE_SELF => _('Выполнение задач - самооценка'),
            self::RELATION_TYPE_PARENT => _('Выполнение задач - оценка руководителем'),
            self::RELATION_TYPE_PARENT_RESERVE => _('Выполнение задач - оценка куратором'),
        );
    }
    
    // DEPRECATED??
    // должно соответствовать HM_At_Evaluation_Method_CompetenceModel::getRelationTypes
    static public function getTitle()
    {
        return _('Куратор оценивает выполнение задач');
    }
    
    public function getDefaults($user)
    {
        return array(
            'name' => sprintf(_('Оценка выполнения задач %s'), $user->getName())
        );
    }

    // есть ли у пользователя цели на этот период
    public function isValid()
    {
        list($userId, $cycleId) = func_get_args();
        $userKpis = Zend_Registry::get('serviceContainer')->getService('AtKpiUser')->fetchAll(array(
            'user_id = ?' => $userId,        
            'cycle_id = ?' => $cycleId,        
        ));
        return count($userKpis);
    }
    
    public function isAvailableForProgramm($programmType)
    {
        switch ($programmType) {
            case HM_Programm_ProgrammModel::TYPE_ASSESSMENT:
            case HM_Programm_ProgrammModel::TYPE_ADAPTING:
            case HM_Programm_ProgrammModel::TYPE_RESERVE:
                return true;
            break;
        }
        return false;
    }    
}